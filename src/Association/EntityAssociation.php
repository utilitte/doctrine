<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Association;

use DomainException;
use LogicException;
use OutOfBoundsException;
use Utilitte\Doctrine\Entity;
use Utilitte\Doctrine\Identity\EntityUniqueIdentity;

/**
 * @template TKey of Entity
 * @template TValue
 * @implements Association<TKey, TValue>
 */
final class EntityAssociation implements Association
{

	/** @var array<TValue> */
	private array $map = [];

	/**
	 * @param class-string<TKey> $className
	 * @param iterable<TKey|EntityUniqueIdentity|string|int, TValue> $collection
	 */
	public function __construct(
		private string $className,
		iterable $collection,
	)
	{
		if (!is_subclass_of($this->className, Entity::class)) {
			throw new LogicException(sprintf('Class name %s must implements %s.', $this->className, Entity::class));
		}

		foreach ($collection as $key => $value) {
			$this->map[EntityUniqueIdentity::create($this->className, $key)->getUniqueId()] = $value;
		}
	}

	/**
	 * @param TKey|EntityUniqueIdentity $offset
	 */
	public function has(object $offset): bool
	{
		$key = EntityUniqueIdentity::create($this->className, $offset)->getUniqueId();

		return array_key_exists($key, $this->map);
	}

	/**
	 * @param TKey|EntityUniqueIdentity $offset
	 * @return TValue
	 */
	public function get(object $offset): mixed
	{
		$key = EntityUniqueIdentity::create($this->className, $offset)->getUniqueId();

		if (!array_key_exists($key, $this->map)) {
			throw new OutOfBoundsException(sprintf('Given entity %s is not in association.', $key));
		}

		return $this->map[$key];
	}

	/**
	 * @param TValue $default
	 * @return TValue
	 */
	public function trySingle(mixed $default): mixed
	{
		$count = count($this->map);

		return match ($count) {
			0 => $default,
			1 => current($this->map),
			default => throw new DomainException(
				sprintf('Cannot get single value from association which have %d records.', $count)
			),
		};
	}

	/**
	 * @param TValue $default
	 * @return TValue
	 */
	public function single(): mixed
	{
		$count = count($this->map);

		return match ($count) {
			1 => current($this->map),
			default => throw new DomainException(
				sprintf('Cannot get single value from association which have %d records.', $count)
			),
		};
	}

	/**
	 * @param TKey|EntityUniqueIdentity $offset
	 * @param TValue $default
	 * @return TValue
	 */
	public function try(object $offset, mixed $default): mixed
	{
		$key = EntityUniqueIdentity::create($this->className, $offset)->getUniqueId();

		if (!array_key_exists($key, $this->map)) {
			return $default;
		}

		return $this->map[$key];
	}

	/**
	 * @param TKey|EntityUniqueIdentity $offset
	 */
	public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

	/**
	 * @param TKey|EntityUniqueIdentity $offset
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
		throw new LogicException('Setting is not allowed in association.');
	}

	/**
	 * @never
	 */
	public function offsetUnset(mixed $offset): void
	{
		throw new LogicException('Unsetting is not allowed in association.');
	}

}
