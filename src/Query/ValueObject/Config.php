<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query\ValueObject;

final class Config
{

	/** @var mixed[] */
	private array $joins = [];

	public function addJoin(string $alias, string $join): self
	{
		$this->joins[$alias] = $join;

		return $this;
	}

	public function getJoin(string $alias): string
	{
		return $this->joins[$alias];
	}

}
