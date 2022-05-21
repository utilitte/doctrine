<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use JetBrains\PhpStorm\Deprecated;
use Utilitte\Doctrine\DoctrineIdentityExtractor;

#[Deprecated]
final class EntityCollectionFactory
{

	public function __construct(
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
	)
	{
	}

	/**
	 * @template TKey of object
	 * @template TValue
	 * @param iterable<TKey, TValue> $collection
	 * @return EntityCollection<TKey, TValue>
	 */
	public function create(iterable $collection): EntityCollection
	{
		return new EntityCollection($this->doctrineIdentityExtractor, $collection);
	}

	/**
	 * @template TKey of object
	 * @template TValue
	 * @param callable(TKey): TValue $callback
	 * @param iterable<TKey> $entities
	 * @return EntityCollection<TKey, TValue>
	 */
	public function createByCallback(callable $callback, iterable $entities): EntityCollection
	{
		return new EntityCollection($this->doctrineIdentityExtractor, $this->callableCreateByCallback($callback,$entities));
	}

	/**
	 * @template TKey of object
	 * @template TValue
	 * @param iterable<TKey> $entities
	 * @param TValue $value
	 * @return EntityCollection<TKey, TValue>
	 */
	public function createStatic(iterable $entities, mixed $value): EntityCollection
	{
		return new EntityCollection($this->doctrineIdentityExtractor, $this->callableCreateByCallback(fn () => $value, $entities));
	}

	/**
	 * @template TKey of object
	 * @template TValue
	 * @param callable(TKey): TValue $callback
	 * @param iterable<TKey> $entities
	 * @return iterable<TKey, TValue>
	 */
	private function callableCreateByCallback(callable $callback, iterable $entities): iterable
	{
		foreach ($entities as $entity) {
			yield $entity => $callback($entity);
		}
	}

}
