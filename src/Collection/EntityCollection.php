<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use ArrayAccess;
use LogicException;
use OutOfBoundsException;
use Utilitte\Doctrine\DoctrineIdentityExtractor;

/**
 * @template T
 * @implements ArrayAccess<int|string|object, T>
 */
final class EntityCollection implements ArrayAccess
{

	/** @var mixed[] */
	private $collection = [];

	/**
	 * @param mixed[] $map
	 */
	public function __construct(
		private array $map,
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
	)
	{
	}

	public function has(mixed $offset): bool
	{
		if (is_object($offset)) {
			$offset = $this->doctrineIdentityExtractor->extractIdentity($offset);
		}

		return array_key_exists($offset, $this->map);
	}

	public function get(mixed $offset): mixed
	{
		if (is_object($offset)) {
			$offset = $this->doctrineIdentityExtractor->extractIdentity($offset);
		}

		return array_key_exists($offset, $this->map) ? $this->map[$offset] :
			throw new OutOfBoundsException(sprintf('%s is not in array.', (string) $offset));
	}

	public function offsetExists(mixed $offset): bool
	{
		return $this->has($offset);
	}

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
