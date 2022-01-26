<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Insertion;

use Doctrine\ORM\EntityManagerInterface;

final class InsertionFactory
{

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	public function create(string $entity): Insertion
	{
		return new Insertion($this->em, $entity);
	}

	public function createIgnore(string $entity): Insertion
	{
		return new Insertion($this->em, $entity, Insertion::TYPE_IGNORE);
	}

	public function createOnDuplicateUpdate(string $entity): Insertion
	{
		return new Insertion($this->em, $entity, Insertion::TYPE_DUPLICATE_UPDATE);
	}

}
