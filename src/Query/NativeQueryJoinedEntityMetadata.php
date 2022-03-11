<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\Mapping\ClassMetadata;

final class NativeQueryJoinedEntityMetadata extends NativeQueryEntityMetadata {

	/**
	 * @param class-string $entity
	 */
	public function __construct(
		string $entity,
		public string $relation,
		string $alias,
		ClassMetadata $metadata,
		public NativeQueryEntityMetadata $parent,
	)
	{
		parent::__construct($entity, $alias, $metadata);
	}

}
