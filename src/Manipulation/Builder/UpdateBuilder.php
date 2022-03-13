<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ParameterTypeInferer;
use Utilitte\Doctrine\Literal\Literal;

/**
 * @internal
 */
final class UpdateBuilder extends Builder
{

	/**
	 * @param array<string, mixed> $fields
	 * @param array<string, mixed> $where
	 */
	public function __construct(
		EntityManagerInterface $em,
		private string $entity,
		private array $fields,
		private array $where,
		private bool $ignore = false,
	)
	{
		parent::__construct($em);
	}

	public function getSql(): string
	{
		if (!$this->fields) {
			return '';
		}

		$metadata = $this->em->getClassMetadata($this->entity);
		$fields = [];

		foreach ($this->processFields($metadata, $this->fields) as $column => $value) {
			$fields[] = sprintf('%s = %s', $column, $value);
		}

		$sql = 'UPDATE ';
		if ($this->ignore) {
			$sql .= 'IGNORE ';
		}

		$sql .= sprintf('%s SET %s', $this->quoteColumn($metadata->getTableName()), implode(', ', $fields));
		$sql .= $this->buildWhereSql($metadata, $this->where);

		return $sql;
	}

}
