<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

use JetBrains\PhpStorm\Deprecated;

/**
 * @template E
 * @template V
 */
#[Deprecated]
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
