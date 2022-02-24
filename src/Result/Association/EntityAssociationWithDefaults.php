<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

use InvalidArgumentException;
use OutOfBoundsException;
use Utilitte\Doctrine\DoctrineIdentityExtractor;

/**
 * @template E
 * @template V
 * @implements EntityAssociationInterface<E, V>
 */
final class EntityAssociationWithDefaults implements EntityAssociationInterface
{

	/**
	 * @param class-string<E> $className
	 * @param array<int|string, V> $values
	 * @param V $default
	 */
	public function __construct(
		private string $className,
		private array $values,
		private mixed $default,
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
	)
	{
	}

	public function get(object $entity): mixed
	{
		if (!$entity instanceof $this->className) {
			throw new InvalidArgumentException(
				sprintf('Given class must be instance of %s, %s given.', $this->className, $entity::class)
			);
		}

		$key = $this->doctrineIdentityExtractor->extractIdentity($entity);

		if (array_key_exists($key, $this->values)) {
			return $this->values[$key];
		}

		return $this->default;
	}

	public function has(object $entity): bool
	{
		if (!$entity instanceof $this->className) {
			throw new InvalidArgumentException(
				sprintf('Given class must be instance of %s, %s given.', $this->className, $entity::class)
			);
		}

		$key = $this->doctrineIdentityExtractor->extractIdentity($entity);

		return array_key_exists($key, $this->values);
	}

}
