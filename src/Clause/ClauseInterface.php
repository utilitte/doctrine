<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Clause;

use Doctrine\ORM\QueryBuilder;

interface ClauseInterface
{

	public function apply(QueryBuilder $queryBuilder): void;

}
