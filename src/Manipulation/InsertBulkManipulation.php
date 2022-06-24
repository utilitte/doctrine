<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation;

use BadMethodCallException;
use Countable;
use Doctrine\ORM\EntityManagerInterface;
use Utilitte\Doctrine\Manipulation\Builder\Builder;

final class InsertBulkManipulation extends Builder implements Countable
{

	use OnDuplicateKey;

	/** @var array<int, mixed[]> */
	private array $values = [];

	private int $fieldCount;

	/**
	 * @param class-string $entity
	 * @param string[] $fields
	 */
	public function __construct(
		EntityManagerInterface $em,
		private string $entity,
		private array $fields,
		private bool $ignore,
	)
	{
		parent::__construct($em);

		$this->fieldCount = count($this->fields);
	}

	/**
	 * @return int<0, max>
	 */
	public function count(): int
	{
		return count($this->values);
	}

	public function empty(): bool
	{
		return !$this->values;
	}

	public function add(mixed ...$values): self
	{
		if ($this->fieldCount !== count($values)) {
			throw new BadMethodCallException(
				sprintf('Value count (%d) does not match field count (%d)', count($values), $this->fieldCount)
			);
		}

		$this->values[] = $values;

		return $this;
	}

	public function getSql(): string
	{
		if (!$this->values || !$this->fields) {
			return '';
		}

		$metadata = $this->em->getClassMetadata($this->entity);

		$sql = 'INSERT ';

		if ($this->ignore) {
			$sql .= 'IGNORE ';
		}

		$sql .= sprintf(
			'INTO %s (%s) VALUES ',
			$this->quoteColumn($metadata->getTableName()),
			implode(', ', $this->getColumns($metadata, $this->fields)),
		);

		foreach ($this->values as $values) {
			$sql .= sprintf('(%s), ', implode(', ', array_map([$this, 'escape'], $values)));
		}

		$sql = substr($sql, 0, -2);

		$sql .= $this->getOnDuplicateKeySql($metadata);

		return $sql . ";\n";
	}

}
