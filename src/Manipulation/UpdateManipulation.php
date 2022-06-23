<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation;

use Countable;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\ORM\EntityManagerInterface;
use Utilitte\Doctrine\Literal\Literal;
use Utilitte\Doctrine\Manipulation\Builder\UpdateBuilder;

final class UpdateManipulation implements Countable
{

	/** @var UpdateBuilder[] */
	private array $updates = [];

	public function __construct(
		private EntityManagerInterface $em,
		private bool $defaultIgnore,
	)
	{
	}

	/**
	 * @param array<string, mixed> $values field => valueOrLiteral
	 * @param array<string|int, mixed> $where field|none => valueOrLiteral
	 */
	public function add(string $entity, array $values, array $where = [], ?bool $ignore = null): self
	{
		$this->updates[] = new UpdateBuilder($this->em, $entity, $values, $where, $ignore ?? $this->defaultIgnore);

		return $this;
	}

	public function getSql(): string
	{
		if (!$this->updates) {
			return '';
		}

		return implode(";\n", array_map(fn (UpdateBuilder $builder) => $builder->getSql(), $this->updates)) . ";\n";
	}

	/**
	 * @return int<0, max>
	 */
	public function count(): int
	{
		return count($this->updates);
	}

	public function executeQuery(): Result
	{
		return $this->em->getConnection()->executeQuery($this->getSql());
	}

}
