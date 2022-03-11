<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use OutOfBoundsException;

class NativeQueryMetadata {

	/** @var array<string, NativeQueryEntityMetadata> */
	private array $entities = [];

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	/**
	 * @param class-string $entity
	 */
	public function addEntity(string $entity, string $alias): self
	{
		if (isset($this->entities[$alias])) {
			throw new LogicException(sprintf('Entity alias %s already exists.', $alias));
		}

		$this->entities[$alias] = new NativeQueryEntityMetadata($entity, $alias, $this->em->getClassMetadata($entity));

		return $this;
	}

	public function addJoinedEntity(string $select, string $alias): self
	{
		if (isset($this->entities[$alias])) {
			throw new LogicException(sprintf('Entity alias %s already exists.', $alias));
		}

		[$parentAlias, $column] = explode('.', $select);
		$parent = $this->getEntityFromAlias($parentAlias);
		$entity = $parent->metadata->getAssociationTargetClass($column);

		if (!isset($this->entities[$parentAlias])) {
			throw new OutOfBoundsException(sprintf('Parent entity alias %s not exists.', $parentAlias));
		}

		$this->entities[$alias] = new NativeQueryJoinedEntityMetadata($entity, $column, $alias, $this->em->getClassMetadata($entity), $parent);

		return $this;
	}

	public function hasEntityWithAlias(string $entity, string $alias): bool
	{
		return ($this->entities[$alias] ?? null)?->entity === $entity;
	}

	public function getEntityFromAlias(string $alias): NativeQueryEntityMetadata
	{
		return $this->entities[$alias] ?? throw new OutOfBoundsException(sprintf('Alias %s not exists.', $alias));
	}

}
