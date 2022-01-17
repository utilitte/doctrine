<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use Utilitte\Doctrine\DoctrineIdentityExtractor;
use WeakMap;

final class CollectionByEntitiesFactory
{

	public function __construct(
		private DoctrineIdentityExtractor $identityExtractor,
	)
	{
	}

	/**
	 * @template T of object
	 * @template K
	 * @param T[] $entities
	 * @param callable(mixed[]): K $fetcher
	 * @return CollectionByEntities<T, K>
	 */
	public function create(array $entities, callable $fetcher, mixed $default): CollectionByEntities
	{
		return new CollectionByEntities(
			$fetcher($this->identityExtractor->extractIdentityMany($entities)),
			$default,
			$this->identityExtractor,
		);
	}

}
