<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\Mapping\ClassMetadata;

class NativeQueryEntityMetadata {

	/**
	 * @param class-string $entity
	 */
	public function __construct(
		public string $entity,
		public string $alias,
		public ClassMetadata $metadata,
	)
	{
	}

}
