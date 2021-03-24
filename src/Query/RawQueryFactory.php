<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use InvalidArgumentException;
use Nette\Utils\Strings;
use Utilitte\Doctrine\QueryMetadataExtractor;

final class RawQueryFactory
{

	private QueryMetadataExtractor $queryMetadataExtractor;
	private RawQueryFunctions $functions;

	public function __construct(QueryMetadataExtractor $queryMetadataExtractor)
	{
		$this->queryMetadataExtractor = $queryMetadataExtractor;
		$this->functions = new RawQueryFunctions($queryMetadataExtractor);
	}

	public function prepare(string $sql): RawQueryStatement
	{
		return new RawQueryStatement($this->queryMetadataExtractor, $sql);
	}

	/**
	 * @param string[] $aliases
	 */
	public function create(string $sql, array $aliases): string
	{
		return Strings::replace(
			$sql,
			$this->buildRegex(array_keys($aliases)),
			fn (array $matches) => $this->replace($matches, $aliases)
		);
	}

	/**
	 * @param mixed[] $matches
	 * @param string[] $aliases
	 */
	private function replace(array $matches, array $aliases): string
	{
		if ($matches[2] === 'fn') {
			return $this->replaceFunctions($matches, $aliases);
		}
		if (!isset($aliases[$matches[2]])) {
			throw new InvalidArgumentException(sprintf('Alias %s not set', $matches[1]));
		}

		$prefix = '';
		if ($matches[1] === '%') {
			$prefix = $this->queryMetadataExtractor->getTableName($aliases[$matches[2]]) . '.';
		}

		return $prefix . (isset($matches[3])
			? $this->queryMetadataExtractor->getColumn($aliases[$matches[2]], $matches[3])
			:
			$this->queryMetadataExtractor->getTableName($aliases[$matches[2]]));
	}

	/**
	 * @param mixed[] $matches
	 * @param string[] $aliases
	 */
	private function replaceFunctions(array $matches, array $aliases): string
	{
		if (count($matches) !== 5) {
			throw new InvalidArgumentException('Raw query function must have method');
		}

		$name = $matches[3];
		$args = array_map('trim', explode(',', $matches[4]));

		return $this->functions->call($aliases, $name, ...$args);
	}

	/**
	 * @param string[] $aliases
	 */
	private function buildRegex(array $aliases): string
	{
		$aliases[] = 'fn';
		$group = implode('|', array_map(fn (string $alias) => preg_quote($alias, '#'), $aliases));

		$args = '(?:\((.*?)\))?';

		return '#%(%?)(' . $group . ')(?:\.(\w+)' . $args . ')?#';
	}

}
