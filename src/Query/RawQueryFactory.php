<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use InvalidArgumentException;
use Utilitte\Doctrine\QueryMetadataExtractor;

final class RawQueryFactory
{

	private QueryMetadataExtractor $queryMetadataExtractor;

	public function __construct(QueryMetadataExtractor $queryMetadataExtractor)
	{
		$this->queryMetadataExtractor = $queryMetadataExtractor;
	}

	public function create(string $sql, array $aliases): string
	{
		return preg_replace_callback(
			$this->buildRegex(array_keys($aliases)),
			fn (array $matches) => $this->replace($matches, $aliases),
			$sql
		);
	}

	private function replace(array $matches, array $aliases): string
	{
		if (!isset($aliases[$matches[1]])) {
			throw new InvalidArgumentException(sprintf('Alias %s not set', $matches[1]));
		}

		if (isset($matches[2])) {
			// column
			return $this->queryMetadataExtractor->getColumn($aliases[$matches[1]], $matches[2]);
		} else {
			// table
			return $this->queryMetadataExtractor->getTableName($aliases[$matches[1]]);
		}
	}

	private function buildRegex(array $aliases): string
	{
		$group = implode('|', array_map(fn (string $alias) => preg_quote($alias, '#'), $aliases));

		return '#%(' . $group . ')(?:\.(\w+))?#';
	}

}
