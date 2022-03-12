<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

interface NativeQueryBuilderFactory
{

	public function create(?string $entity = null, ?string $alias = null): NativeQueryBuilder;

}
