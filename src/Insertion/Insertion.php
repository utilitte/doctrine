<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Insertion;

use Doctrine\ORM\EntityManagerInterface;

final class Insertion
{

	public const TYPE_BASIC = 1;
	public const TYPE_IGNORE = 2;
	public const TYPE_DUPLICATE_UPDATE = 3;

	/** @var array<int, mixed[]> */
	private array $values = [];

	public function __construct(
		private EntityManagerInterface $em,
		private string $entity,
		private int $type = self::TYPE_BASIC,
	)
	{
		$this->metadata = new InsertionMetadata($this->em, $this->em->getClassMetadata($this->entity));
	}

	public function add(array $values): self
	{
		$this->values[] = $values;

		return $this;
	}

	public function getSql(): string
	{
		if (!$this->values) {
			return '';
		}

		$sql = '';
		foreach ($this->values as $vals) {
			$values = $this->metadata->processValues($vals);

			$sql .= sprintf(
				"%s %s (%s) VALUES (%s)%s;\n",
				$this->prolog(),
				$this->metadata->getTableName(),
				implode(', ', array_keys($values)),
				implode(', ', $values),
				$this->epilog($values),
			);
		}

		return substr($sql, 0, -1);
	}

	public function execute(): void
	{
		$this->em->getConnection()->executeQuery($this->getSql());
	}

	private function prolog(): string
	{
		if ($this->type === self::TYPE_IGNORE) {
			return 'INSERT IGNORE INTO';
		}

		return 'INSERT INTO';
	}

	/**
	 * @param array<string, string> $values
	 */
	private function epilog(array $values): string
	{
		if ($this->type === self::TYPE_DUPLICATE_UPDATE) {
			$sql = '';

			foreach ($values as $column => $value) {
				$sql .= sprintf('%s=%s, ', $column, $value);
			}

			return ' ON DUPLICATE KEY UPDATE ' . substr($sql, 0, -2);
		}

		return '';
	}

}
