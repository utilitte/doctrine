<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

interface BoolAssociationInterface
{

	public function has(mixed $value): bool;

}
