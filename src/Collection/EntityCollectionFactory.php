<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

final class EntityCollectionFactory
{

	/**
	 * @template TKey of object
	 * @template TValue
	 * @param iterable<TKey, TValue> $collection
	 * @return EntityCollection<TKey, TValue>
	 */
	public function create(iterable $collection): EntityCollection
	{
		return new EntityCollection($collection);
	}

}
