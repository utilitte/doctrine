<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use ArrayAccess;
use LogicException;
use OutOfBoundsException;
use WeakMap;

/**
 * @template TKey of object
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 */
final class EntityCollection implements ArrayAccess
{

	/** @var WeakMap<TKey, TValue> */
	private WeakMap $weakMap;

	/**
	 * @param iterable<TKey, TValue> $collection
	 */
	public function __construct(iterable $collection)
	{
		$this->weakMap = new WeakMap();

		foreach ($collection as $key => $value) {
			$this->weakMap[$key] = $value;
		}
	}

	/**
	 * @param TKey $offset
	 */
	public function has(object $offset): bool
	{
		return isset($this->weakMap[$offset]);
	}

	/**
	 * @param TKey $offset
	 */
	public function get(object $offset): mixed
	{
		return $this->weakMap[$offset]
			   ??
			   throw new OutOfBoundsException(
				   sprintf(
					   'Given object %s is not in collection.',
					   get_debug_type($offset),
				   )
			   );
	}

	/**
	 * @param TKey $offset
	 */
	public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	/**
	 * @param TKey $offset
	 * @return TValue
	 */
	public function offsetGet(mixed $offset): mixed
	{
		return $this->get($offset);
	}

	/**
	 * @never
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		throw new LogicException('Cannot set entity collection.');
	}

	/**
	 * @never
	 */
	public function offsetUnset(mixed $offset): void
	{
		throw new LogicException('Cannot unset entity collection.');
	}

}
