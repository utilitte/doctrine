<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use Utilitte\Doctrine\DoctrineIdentityExtractor;

final class EntityCollectionFactory
{

	public function __construct(
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
	)
	{
	}

	/**
	 * @template T
	 * @param mixed[] $values id => mixed
	 * @return EntityCollection<T>
	 */
	public function create(array $values): EntityCollection
	{
		return new EntityCollection($values, $this->doctrineIdentityExtractor);
	}

	/**
	 * @template T
	 * @param mixed[] $values id => mixed
	 * @return EntityCollection<T>
	 */
	public function createMany(array $values): EntityCollection
	{
		return new EntityCollection($values, $this->doctrineIdentityExtractor);
	}

}
