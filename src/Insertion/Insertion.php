<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Insertion;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

final class Insertion
{

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	public function insertOneIgnore(string $entity, array $values, array $columns = []): bool
	{
		return $this->insertArray($entity, [$values], $columns, true) === 1;
	}

	public function insertArray(string $entity, array $values, array $columns = [], bool $ignore = false): ?int
	{
		$insert = $this->sqlValues($values);
		$metadata = $this->em->getClassMetadata($entity);
		$table = $metadata->getTableName();

		if (!$insert) {
			return null;
		}

		if ($columns) {
			$columns = implode(
				', ',
				array_map(
					function (string $column) use ($metadata): string
					{
						if (isset($metadata->fieldMappings[$column])) {
							return $metadata->getColumnName($column);
						}

						return $metadata->getSingleAssociationJoinColumnName($column);
					},
					$columns
				)
			);

			$columns = ' (' . $columns . ')';
		} else {
			$columns = '';
		}

		$sql = sprintf('INSERT%s INTO %s%s %s', $ignore ? ' IGNORE' : '', $table, $columns, $insert);

		return $this->em->getConnection()->executeStatement($sql);
	}

	private function sqlValues(array $values): ?string
	{
		$sql = '';

		foreach ($values as $items) {
			$sql .= sprintf(
				'(%s), ',
				implode(',', array_map(fn (mixed $value) => $this->escape($value), $items))
			);
		}

		return $sql ? 'VALUES ' . substr($sql, 0, -2) : null;
	}

	private function escape(mixed $value): mixed
	{
		$type = ParameterType::STRING;

		if (is_int($value)) {
			$type = ParameterType::INTEGER;
		} else if (is_bool($type)) {
			$type = ParameterType::BOOLEAN;
		}

		return $this->em->getConnection()->quote($value, $type);
	}

}
