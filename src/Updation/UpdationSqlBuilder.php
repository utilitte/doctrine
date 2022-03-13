<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Updation;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Utilitte\Doctrine\Literal\Literal;

final class UpdationSqlBuilder
{

	/**
	 * @param array<string, mixed> $fields
	 * @param array<string, mixed> $where
	 */
	public function __construct(
		private EntityManagerInterface $em,
		private string $entity,
		private array $fields,
		private array $where,
	)
	{
	}

	public function getSql(): string
	{
		$metadata = $this->em->getClassMetadata($this->entity);
		$fields = [];

		foreach ($this->fields as $field => $value) {
			$fields[] = sprintf('`%s` = %s', $metadata->getColumnName($field), $this->quote($value));
		}

		$sql = sprintf('UPDATE %s SET %s', $metadata->getTableName(), implode(', ', $fields));

		if ($this->where) {
			$where = [];
			foreach ($this->where as $field => $value) {
				if (is_int($field) && $value instanceof Literal) {
					$where[] = $this->quote($value);
				} else {
					$where[] = sprintf('`%s` = %s', $metadata->getColumnName($field), $this->quote($value));
				}
			}

			$sql .= sprintf(' WHERE %s', implode(' AND ', $where));
		}

		return $sql;
	}

	private function quote(mixed $value): string
	{
		if ($value instanceof Literal) {
			return $value->toString($this->em);
		}

		$parameter = new Parameter('void', $value);

		return $this->em->getConnection()->quote($parameter->getValue(), $parameter->getType());
	}

}
