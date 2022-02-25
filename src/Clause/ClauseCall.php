<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Clause;

final class ClauseCall
{

	/**
	 * @param mixed[] $arguments
	 */
	public function __construct(
		public string $method,
		public array $arguments,
	)
	{
	}

}
