<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation;

use Doctrine\ORM\EntityManagerInterface;

final class MultipleDataManipulationFactory
{

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	public function createUpdate(bool $defaultIgnore = false): UpdateManipulation
	{
		return new UpdateManipulation($this->em, $defaultIgnore);
	}

	public function createInsert(bool $defaultIgnore = false): InsertManipulation
	{
		return new InsertManipulation($this->em, $defaultIgnore);
	}

	/**
	 * @param class-string $entity
	 * @param string[] $fields
	 */
	public function createInsertBulk(string $entity, array $fields, bool $ignore = false): InsertBulkManipulation
	{
		return new InsertBulkManipulation($this->em, $entity, $fields, $ignore);
	}

}
