<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Updation;

use Doctrine\ORM\EntityManagerInterface;

final class UpdationFactory
{
	
	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	public function create(): Updation
	{
		return new Updation($this->em);
	}

}
