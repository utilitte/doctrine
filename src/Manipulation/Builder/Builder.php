<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\ParameterTypeInferer;
use Utilitte\Doctrine\Literal\Literal;

abstract class Builder
{

	public function __construct(
		protected EntityManagerInterface $em,
	)
	{
	}

	protected function escape(mixed $value): string
	{
		if ($value instanceof Literal) {
			return $value->toString($this->em);
		}

		return $this->em->getConnection()->quote($value, ParameterTypeInferer::inferType($value));
	}

	protected function quoteColumn(string $column): string
	{
		return str_starts_with($column, '`') ? $column : sprintf('`%s`', $column);
	}

	/**
	 * @param array<string, mixed> $where
	 */
	protected function buildWhereSql(ClassMetadataInfo $metadata, array $where): string
	{
		if (!$where) {
			return '';
		}

		return sprintf(' WHERE %s', implode(' AND ', $this->processAssignment($metadata, $where)));
	}

	/**
	 * @param array<string|int, mixed> $assignments
	 * @return string[]
	 */
	protected function processAssignment(ClassMetadataInfo $metadata, array $assignments, bool $brackets = true): array
	{
		if (!$assignments) {
			return [];
		}

		$build = [];
		foreach ($assignments as $field => $value) {
			if (is_int($field) && $value instanceof Literal) {
				$build[] = $this->surroundWithBrackets($this->escape($value), $brackets);
			} else {
				$build[] = $this->surroundWithBrackets(sprintf(
					'%s = %s',
					$this->quoteColumn($this->getColumnName($metadata, $field)),
					$this->escape($value)
				), $brackets);
			}
		}

		return $build;
	}

	private function surroundWithBrackets(string $query, bool $use): string
	{
		if (!$use) {
			return $query;
		}

		return sprintf('(%s)', $query);
	}

	protected function getColumnName(ClassMetadataInfo $metadata, string $field): string
	{
		if ($metadata->hasAssociation($field)) {
			return $metadata->getSingleAssociationJoinColumnName($field);
		}

		return $metadata->getColumnName($field);
	}

	/**
	 * @param string[] $fields
	 * @return string[]
	 */
	protected function getColumns(ClassMetadataInfo $metadata, array $fields): array
	{
		return array_map(fn (string $field) => $this->quoteColumn($this->getColumnName($metadata, $field)), $fields);
	}

	/**
	 * @param array<string, mixed> $fields
	 * @return array<string, string> quotedColumn => value
	 */
	protected function processFields(ClassMetadataInfo $metadata, array $fields): array
	{
		$return = [];
		foreach ($fields as $field => $value) {
			$return[$this->quoteColumn($this->getColumnName($metadata, $field))] = $this->escape($value);
		}

		return $return;
	}

}
