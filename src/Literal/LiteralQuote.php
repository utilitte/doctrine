<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Literal;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ParameterTypeInferer;
use Utilitte\Doctrine\Parameter\ParameterType;

/**
 * @internal
 */
final class LiteralQuote implements LiteralInterface
{

	public function __construct(
		private mixed $value,
	)
	{
	}

	public function toString(EntityManagerInterface $em): string
	{
		return $em->getConnection()->quote($this->value, ParameterTypeInferer::inferType($this->value));
	}

}
