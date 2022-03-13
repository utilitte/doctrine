<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Literal;

use Doctrine\ORM\EntityManagerInterface;

interface LiteralInterface
{

	public function toString(EntityManagerInterface $em): string;

}
