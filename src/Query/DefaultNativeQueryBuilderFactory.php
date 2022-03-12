<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;

final class DefaultNativeQueryBuilderFactory implements NativeQueryBuilderFactory
{

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	public function create(?string $entity = null, ?string $alias = null): NativeQueryBuilder
	{
		$queryBuilder = new NativeQueryBuilder($this->em);

		if ($entity && $alias) {
			$queryBuilder->fromEntity($entity, $alias);
		}

		return $queryBuilder;
	}

}
