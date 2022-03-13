<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Literal;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @internal
 */
final class LiteralField implements LiteralInterface
{

	/**
	 * @param class-string $entity
	 */
	public function __construct(
		private string $entity,
		private string $field,
	)
	{
	}

	public function toString(EntityManagerInterface $em): string
	{
		return $em->getClassMetadata($this->entity)->getColumnName($this->field);
	}

}
