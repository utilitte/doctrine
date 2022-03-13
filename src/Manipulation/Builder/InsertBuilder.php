<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation\Builder;

use Doctrine\ORM\EntityManagerInterface;
use Utilitte\Doctrine\Literal\Literal;
use Utilitte\Doctrine\Manipulation\OnDuplicateKey;

final class InsertBuilder extends Builder
{

	use OnDuplicateKey;

	/**
	 * @param class-string $entity
	 * @param array<string, string|Literal> $values field => valueOrLiteral
	 */
	public function __construct(
		EntityManagerInterface $em,
		private string $entity,
		private array $values,
		private bool $ignore,
	)
	{
		parent::__construct($em);
	}

	public function setIgnore(bool $ignore): self
	{
		$this->ignore = $ignore;

		return $this;
	}

	public function getSql(): string
	{
		if (!$this->values) {
			return '';
		}

		$metadata = $this->em->getClassMetadata($this->entity);
		$columns = [];
		$values = [];

		foreach ($this->processFields($metadata, $this->values) as $column => $value) {
			$columns[] = $column;
			$values[] = $value;
		}

		$sql = 'INSERT ';

		if ($this->ignore) {
			$sql .= 'IGNORE ';
		}

		$sql .= sprintf(
			'INTO %s (%s) VALUES(%s)',
			$this->quoteColumn($metadata->getTableName()),
			implode(', ', $columns),
			implode(', ', $values),
		);

		$sql .= $this->getOnDuplicateKeySql($metadata);

		return $sql;
	}

}
