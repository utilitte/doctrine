<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
final class TrueAssociation implements BoolAssociationInterface
{

	public function has(mixed $value): bool
	{
		return true;
	}

}
