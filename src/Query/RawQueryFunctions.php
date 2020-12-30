<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use InvalidArgumentException;
use Utilitte\Doctrine\QueryMetadataExtractor;

final class RawQueryFunctions
{

	private QueryMetadataExtractor $queryMetadataExtractor;

	public function __construct(QueryMetadataExtractor $queryMetadataExtractor)
	{
		$this->queryMetadataExtractor = $queryMetadataExtractor;
	}

	public function call(array $aliases, string $name, string ...$args)
	{
		if ($name === 'call' || !method_exists($this, $name)) {
			throw new InvalidArgumentException(sprintf('Raw query function %s not exists', $name));
		}

		return $this->$name($aliases, ...$args);
	}

	public function join(array $aliases, string $field, ?string $alias = null, ?string $sourceAlias = null): string
	{
		[$table, $column] = explode('.' , $field);
		$alias = $alias ? ' ' . $alias : '';

		$metadata = $this->queryMetadataExtractor->getClassMetadata($aliases[$table]);
		$tableName = $this->queryMetadataExtractor->getTableName($metadata->getAssociationMapping($column)['targetEntity']);

		return sprintf(
			'%s%s ON %s%s = %s',
			$tableName,
			$alias,
			$sourceAlias ? $sourceAlias . '.' : '',
			$metadata->getSingleAssociationJoinColumnName($column),
			$metadata->getSingleAssociationReferencedJoinColumnName($column)
		);
	}

	public function joinTable(array $aliases, string $field): string
	{
		[$table, $column] = explode('.' , $field);

		$metadata = $this->queryMetadataExtractor->getClassMetadata($aliases[$table]);

		return $this->queryMetadataExtractor->getTableName($metadata->getAssociationMapping($column)['targetEntity']);
	}

	public function joinSource(array $aliases, string $field): string
	{
		[$table, $column] = explode('.' , $field);

		$metadata = $this->queryMetadataExtractor->getClassMetadata($aliases[$table]);

		return $metadata->getSingleAssociationJoinColumnName($column);
	}

	public function joinTarget(array $aliases, string $field): string
	{
		[$table, $column] = explode('.' , $field);

		$metadata = $this->queryMetadataExtractor->getClassMetadata($aliases[$table]);

		return $metadata->getSingleAssociationReferencedJoinColumnName($column);
	}

}
