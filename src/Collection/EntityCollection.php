<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use ArrayAccess;
use JetBrains\PhpStorm\Deprecated;
use LogicException;
use OutOfBoundsException;
use Utilitte\Doctrine\DoctrineIdentityExtractor;
use WeakMap;

/**
 * @template TKey of object
 * @template TValue
 * @implements ArrayAccess<TKey, TValue>
 */
#[Deprecated]
final class EntityCollection implements ArrayAccess
{

	/** @var WeakMap<TKey, TValue> */
	private WeakMap $weakMap;

	/** @var array<string|int, TValue> */
	private array $idMap;

	/**
	 * @param iterable<TKey|string|int, TValue> $collection
	 */
	public function __construct(
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
		iterable $collection,
	)
	{
		if (is_array($collection)) {
			return;
		}

		$this->weakMap = new WeakMap();

		foreach ($collection as $key => $value) {
			if (!is_object($key)) {
				return;
			}

			$this->weakMap[$key] = $value;
		}
	}

	/**
	 * @param array<TKey|string|int, TValue> $collection
	 */
	private function invokeIdMap(iterable $collection): void
	{
		$this->idMap = [];

		foreach ($collection as $key => $value) {
			if (is_object($key)) {
				$key = $this->doctrineIdentityExtractor->extractIdentity($key);
			}

			$this->idMap[$key] = $value;
		}
	}

	/**
	 * @return array<string|int, TValue>
	 */
	public function getIdMap(): array
	{
		if (!isset($this->idMap)) {
			$this->idMap = [];

			foreach ($this->weakMap as $entity => $value) {
				$this->idMap[$this->doctrineIdentityExtractor->extractIdentity($entity)] = $value;
			}
		}

		return $this->idMap;
	}

	/**
	 * @param TKey|string|int $offset
	 */
	public function has(object|string|int $offset): bool
	{
		if (!is_object($offset)) {
			return array_key_exists($offset, $this->getIdMap());
		}

		return isset($this->weakMap[$offset]);
	}

	/**
	 * @param TKey|string|int $offset
	 */
	public function get(object|string|int $offset): mixed
	{
		if (!is_object($offset)) {
			$map = $this->getIdMap();

			if (!array_key_exists($offset, $map)) {
				throw new OutOfBoundsException(sprintf('Given key %s is not in collection.', $offset));
			}

			return $map;
		}

		return $this->weakMap[$offset]
			   ??
			   throw new OutOfBoundsException(
				   sprintf('Given object %s is not in collection.', get_debug_type($offset))
			   );
	}

	/**
	 * @param TKey|string|int $offset
	 */
	public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	/**
	 * @param TKey|string|int $offset
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
