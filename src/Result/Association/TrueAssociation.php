<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

final class TrueAssociation implements BoolAssociationInterface
{

	public function has(mixed $value): bool
	{
		return true;
	}

}
