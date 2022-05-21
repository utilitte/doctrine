<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result;

use JetBrains\PhpStorm\Deprecated;
use Utilitte\Doctrine\DoctrineIdentityExtractor;
use Utilitte\Doctrine\Result\Association\BoolAssociation;
use Utilitte\Doctrine\Result\Association\BoolAssociationInterface;
use Utilitte\Doctrine\Result\Association\FalseAssociation;
use Utilitte\Doctrine\Result\Association\TrueAssociation;

#[Deprecated]
final class ResultFactory
{

	public function __construct(
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
	)
	{
	}

	public function createFixedBoolAssociation(bool $result): BoolAssociationInterface
	{
		return $result ? new TrueAssociation() : new FalseAssociation();
	}

	public function createBoolAssociationByPrimary(array $results, int|string $key, bool $default = false): BoolAssociationInterface
	{
		return new BoolAssociation(array_column($results, $key), $default, $this->doctrineIdentityExtractor);
	}

}
