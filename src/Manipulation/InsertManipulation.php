<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation;

use Countable;
use Doctrine\DBAL\ForwardCompatibility\Result;
use Doctrine\ORM\EntityManagerInterface;
use Utilitte\Doctrine\Literal\Literal;
use Utilitte\Doctrine\Manipulation\Builder\InsertBuilder;
use Utilitte\Doctrine\Manipulation\Builder\UpdateBuilder;

final class InsertManipulation implements Countable
{

	/** @var InsertBuilder[] */
	private array $inserts = [];

	public function __construct(
		private EntityManagerInterface $em,
		private bool $defaultIgnore,
	)
	{
	}

	/**
	 * @param class-string $entity
	 * @param array<string, string|Literal> $values field => valueOrLiteral
	 */
	public function add(string $entity, array $values): InsertBuilder
	{
		return $this->inserts[] = new InsertBuilder($this->em, $entity, $values, $this->defaultIgnore);
	}

	public function empty(): bool
	{
		return !$this->inserts;
	}

	public function getSql(): string
	{
		if (!$this->inserts) {
			return '';
		}

		return implode(";\n", array_map(fn (InsertBuilder $builder) => $builder->getSql(), $this->inserts)) . ";\n";
	}

	/**
	 * @return int<0, max>
	 */
	public function count(): int
	{
		return count($this->inserts);
	}

	public function executeQuery(): Result
	{
		return $this->em->getConnection()->executeQuery($this->getSql());
	}

}
