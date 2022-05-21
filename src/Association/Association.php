<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Association;

use ArrayAccess;
use Utilitte\Doctrine\Identity\EntityUniqueIdentity;

/**
 * @template TKey of Entity
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 */
interface Association extends ArrayAccess
{

	/**
	 * @param TKey|EntityUniqueIdentity $offset
	 */
	public function has(object $offset): bool;

	/**
	 * @param TKey|EntityUniqueIdentity $offset
	 */
	public function get(object $offset): mixed;

}
