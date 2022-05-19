<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Updation;

use Countable;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
final class Updation implements Countable
{

	/** @var UpdationSqlBuilder[] */
	private array $updations = [];

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	/**
	 * @param array<string, mixed> $fields
	 * @param array<string, mixed> $where
	 */
	public function add(string $entity, array $fields, array $where = []): self
	{
		$this->updations[] = new UpdationSqlBuilder($this->em, $entity, $fields, $where);

		return $this;
	}

	public function getSql(): string
	{
		$sql = '';

		foreach ($this->updations as $updation) {
			$sql .= $updation->getSql() . ";\n";
		}

		return substr($sql, 0, -2);
	}

	public function count(): int
	{
		return count($this->updations);
	}

	public function execute(): void
	{
		$this->em->getConnection()->executeQuery($this->getSql());
	}

}
