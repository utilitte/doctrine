<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
use OutOfBoundsException;
use Utilitte\Doctrine\DoctrineIdentityExtractor;

/**
 * @template E
 * @template V
 * @implements EntityAssociationInterface<E, V>
 */
#[Deprecated]
final class EntityAssociation implements EntityAssociationInterface
{

	/**
	 * @param class-string<E> $className
	 * @param array<int|string, V> $values
	 */
	public function __construct(
		private string $className,
		private array $values,
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

		if (!array_key_exists($key, $this->values)) {
			throw new OutOfBoundsException(
				sprintf('Entity %s(%s) not found in association.', $entity::class, (string) $key)
			);
		}

		return $this->values[$key];
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
