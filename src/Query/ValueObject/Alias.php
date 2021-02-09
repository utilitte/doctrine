<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query\ValueObject;

final class Alias
{

	private string $class;

	private string $alias;

	public function __construct(string $class, string $alias)
	{
		$this->class = $class;
		$this->alias = $alias;
	}

	public function getClass(): string
	{
		return $this->class;
	}

	public function getAlias(): string
	{
		return $this->alias;
	}

}
