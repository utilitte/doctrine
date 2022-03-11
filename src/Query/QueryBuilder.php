<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\DBAL\Query\Expression\CompositeExpression;

class QueryBuilder
{


	/** @var mixed[] */
	private array $parts = [
		'select' => [],
		'from' => [],
		'join' => [],
		'where' => null,
		'having' => null,
		'groupBy' => [],
		'orderBy' => [],
	];

	/** @phpstan-var int<0, max>|null */
	private ?int $maxResults = null;

	/** @phpstan-var int<0, max>|null */
	private ?int $firstResult = null;

	/**
	 * @phpstan-param int<0, max>|null $maxResults
	 */
	public function setMaxResults(?int $maxResults): static
	{
		$this->maxResults = $maxResults;

		return $this;
	}

	/**
	 * @phpstan-param int<0, max>|null $firstResult
	 */
	public function setFirstResult(?int $firstResult): static
	{
		$this->firstResult = $firstResult;

		return $this;
	}

	public function select(string ...$select): static
	{
		$this->set('select', $select);

		return $this;
	}

	public function addSelect(string ...$select): static
	{
		$this->add('select', $select);

		return $this;
	}

	public function join(string $join): static
	{
		$this->innerJoin($join);

		return $this;
	}

	public function innerJoin(string $join): static
	{
		$this->addJoin('INNER', $join);

		return $this;
	}

	public function leftJoin(string $join): static
	{
		$this->addJoin('LEFT', $join);

		return $this;
	}

	public function rightJoin(string $join): static
	{
		$this->addJoin('RIGHT', $join);

		return $this;
	}

	public function outerJoin(string $join): static
	{
		$this->addJoin('OUTER', $join);

		return $this;
	}

	public function from(string ...$from): static
	{
		$this->add('from', $from);

		return $this;
	}

	public function andHaving(string ...$having): static
	{
		$having = array_filter($having);
		if (!$having) {
			return $this;
		}

		$this->composite('having', 'and', $having);

		return $this;
	}

	public function orHaving(string ...$having): static
	{
		$having = array_filter($having);
		if (!$having) {
			return $this;
		}

		$this->composite('having', 'or', $having);

		return $this;
	}

	public function andWhere(string ...$where): static
	{
		$where = array_filter($where);
		if (!$where) {
			return $this;
		}

		$this->composite('where', 'and', $where);

		return $this;
	}

	public function orWhere(string ...$where): static
	{
		$where = array_filter($where);
		if (!$where) {
			return $this;
		}

		$this->composite('where', 'or', $where);

		return $this;
	}

	public function groupBy(string ...$groupBy): static
	{
		$this->set('groupBy', $groupBy);

		return $this;
	}

	public function addGroupBy(string ...$groupBy): static
	{
		$this->add('groupBy', $groupBy);

		return $this;
	}

	public function orderBy(string ...$orderBy): static
	{
		$this->set('orderBy', $orderBy);

		return $this;
	}

	public function addOrderBy(string ...$orderBy): static
	{
		$this->add('orderBy', $orderBy);

		return $this;
	}

	/**
	 * @param string[] $arguments
	 */
	private function composite(string $part, string $type, array $arguments): void
	{
		$clause = $this->parts[$part];
		if ($clause instanceof CompositeExpression && $clause->getType() === strtoupper($type)) {
			$clause = $clause->with(...$arguments);
		} else {
			array_unshift($arguments, $clause);
			$clause = match($type) {
				'or' => CompositeExpression::or(...$arguments),
				default => CompositeExpression::and(...$arguments),
			};
		}

		$this->parts[$part] = $clause;
	}

	private function addJoin(string $type, string $join): void
	{
		$this->parts['join'][] = sprintf('%s JOIN %s', $type, $join);
	}

	/**
	 * @param string[] $contents
	 */
	private function add(string $part, array $contents): void
	{
		$this->parts[$part] = array_merge($this->parts[$part], $contents);
	}

	/**
	 * @param string[] $contents
	 */
	private function set(string $part, array $contents): void
	{
		$this->parts[$part] = $contents;
	}

	public function getSql(): string
	{
		$query = 'SELECT ' . implode(', ', $this->parts['select']);

		$query .= ($this->parts['from'] ? ' FROM ' . implode(', ', $this->parts['from']) : '')
				  . ($this->parts['join'] ? ' ' . implode(' ', $this->parts['join']) : '')
				  . ($this->parts['where'] !== null ? ' WHERE ' . ((string) $this->parts['where']) : '')
				  . ($this->parts['groupBy'] ? ' GROUP BY ' . implode(', ', $this->parts['groupBy']) : '')
				  . ($this->parts['having'] !== null ? ' HAVING ' . ((string) $this->parts['having']) : '')
				  . ($this->parts['orderBy'] ? ' ORDER BY ' . implode(', ', $this->parts['orderBy']) : '')
				  . ($this->maxResults !== null ? ' LIMIT ' . $this->maxResults : '')
				  . ($this->firstResult ? ' OFFSET ' . $this->firstResult : '');

		return $query;
	}

}
