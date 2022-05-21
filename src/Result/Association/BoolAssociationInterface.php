<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
interface BoolAssociationInterface
{

	public function has(mixed $value): bool;

}
