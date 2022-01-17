<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Collection;

use Utilitte\Doctrine\DoctrineIdentityExtractor;

/**
 * @template E
 * @template T
 */
final class CollectionByEntities
{

	/**
	 * @param T[] $data
	 */
	public function __construct(
		private array $data,
		private mixed $default,
		private DoctrineIdentityExtractor $identityExtractor,
	)
	{
	}

	/**
	 * @param E $entity
	 * @return T
	 */
	public function get(object $entity): mixed
	{
		return $this->data[$this->identityExtractor->extractIdentity($entity)] ?? $this->default;
	}

	/**
	 * @param E $entity
	 */
	public function has(object $entity): bool
	{
		return isset($this->data[$this->identityExtractor->extractIdentity($entity)]);
	}

}
