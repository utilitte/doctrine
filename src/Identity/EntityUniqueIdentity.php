<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Identity;

use InvalidArgumentException;
use LogicException;
use Utilitte\Doctrine\Entity;

final class EntityUniqueIdentity
{

	/**
	 * @param class-string<Entity> $className
	 */
	public function __construct(
		private string $className,
		private string|int $id,
	)
	{
		if (!is_subclass_of($this->className, Entity::class)) {
			throw new LogicException(sprintf('Class name %s must implements %s.', $this->className, Entity::class));
		}
	}

	public function getId(): int|string
	{
		return $this->id;
	}

	/**
	 * @return class-string<Entity>
	 */
	public function getClassName(): string
	{
		return $this->className;
	}

	public function getUniqueId(): string
	{
		return sprintf('%s(%s)', $this->className, $this->id);
	}

	/**
	 * @param class-string<Entity> $className
	 */
	public static function create(string $className, Entity|self|string|int $entity): self
	{
		if (is_object($entity)) {
			if ($entity instanceof self) {
				if ($entity->className !== $className) {
					throw new InvalidArgumentException(
						sprintf('Given identity is of class %s, %s expected.', $entity->className, $className)
					);
				}

				return $entity;
			}

			if (!$entity instanceof $className) {
				throw new InvalidArgumentException(
					sprintf(
						'Given object of %s is not instance of %s.',
						get_debug_type($entity),
						$className,
					)
				);
			}

			return $entity->getUniqueIdentity();
		}

		return new self($className, $entity);
	}

}
