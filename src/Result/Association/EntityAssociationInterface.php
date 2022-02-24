<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

/**
 * @template E
 * @template V
 */
interface EntityAssociationInterface
{

	/**
	 * @param E $entity
	 * @return V
	 */
	public function get(object $entity): mixed;

	/**
	 * @param E $entity
	 */
	public function has(object $entity): bool;

}
