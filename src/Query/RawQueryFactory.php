<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use InvalidArgumentException;
use Nette\Utils\Strings;
use Utilitte\Doctrine\QueryMetadataExtractor;

final class RawQueryFactory
{

	private QueryMetadataExtractor $queryMetadataExtractor;

	public function __construct(QueryMetadataExtractor $queryMetadataExtractor)
	{
		$this->queryMetadataExtractor = $queryMetadataExtractor;
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
		if (!isset($aliases[$matches[1]])) {
			throw new InvalidArgumentException(sprintf('Alias %s not set', $matches[1]));
		}

		return isset($matches[2])
			? $this->queryMetadataExtractor->getColumn($aliases[$matches[1]], $matches[2])
			:
			$this->queryMetadataExtractor->getTableName($aliases[$matches[1]]);
	}

	/**
	 * @param string[] $aliases
	 */
	private function buildRegex(array $aliases): string
	{
		$group = implode('|', array_map(fn (string $alias) => preg_quote($alias, '#'), $aliases));

		return '#%(' . $group . ')(?:\.(\w+))?#';
	}

}
