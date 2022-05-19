<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Bridges\Nette\DI;

use Nette\DI\CompilerExtension;
use Utilitte\Doctrine\Collection\EntityCollectionFactory;
use Utilitte\Doctrine\Manipulation\MultipleDataManipulationFactory;

final class DoctrineExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('entityCollection.factory'))
			->setType(EntityCollectionFactory::class);

		$builder->addDefinition($this->prefix('dataManipulation.factory'))
			->setType(MultipleDataManipulationFactory::class);
	}

}
