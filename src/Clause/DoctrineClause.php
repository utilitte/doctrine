<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Clause;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\QueryBuilder;

final class DoctrineClause implements ClauseInterface
{

	/** @var array<int|string, mixed> */
	private array $parameters = [];

	/** @var ClauseCall[] */
	private array $calls = [];

	public function leftJoin(
		string $join,
		string $alias,
		?string $conditionType = null,
		string|Comparison|Composite|null $condition = null,
		?string $indexBy = null
	): self
	{
		$this->calls[] = new ClauseCall('leftJoin', func_get_args());

		return $this;
	}

	public function andWhere(mixed ... $clauses): self
	{
		$this->calls[] = new ClauseCall('andWhere', $clauses);

		return $this;
	}

	public function orWhere(mixed ... $clauses): self
	{
		$this->calls[] = new ClauseCall('orWhere', $clauses);

		return $this;
	}

	public function orderBy(string $field, string $order): self
	{
		$this->calls[] = new ClauseCall('orderBy', [$field, $order]);

		return $this;
	}

	public function addOrderBy(string $field, string $order): self
	{
		$this->calls[] = new ClauseCall('addOrderBy', [$field, $order]);

		return $this;
	}

	public function setParameter(string|int $key, mixed $value): self
	{
		$this->parameters[$key] = $value;

		return $this;
	}

	public function apply(QueryBuilder $builder): void
	{
		foreach ($this->parameters as $key => $value) {
			$builder->setParameter($key, $value);
		}


		foreach ($this->calls as $call) {
			$builder->{$call->method}(...$call->arguments);
		}
	}

	public static function create(): self
	{
		return new self();
	}

	/**
	 * @param ClauseInterface[] $clauses
	 */
	public static function applyAll(array $clauses, QueryBuilder $builder): void
	{
		foreach ($clauses as $clause) {
			$clause->apply($builder);
		}
	}

}
