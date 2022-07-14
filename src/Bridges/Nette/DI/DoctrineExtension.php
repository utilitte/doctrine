<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Bridges\Nette\DI;

use Nette\DI\CompilerExtension;
use Utilitte\Doctrine\Association\EntityAssociationFactory;
use Utilitte\Doctrine\Collection\EntityCollectionFactory;
use Utilitte\Doctrine\DoctrineIdentityExtractor;
use Utilitte\Doctrine\FetchByIdentifiers;
use Utilitte\Doctrine\Insertion\InsertionFactory;
use Utilitte\Doctrine\Manipulation\MultipleDataManipulationFactory;
use Utilitte\Doctrine\Query\DefaultNativeQueryBuilderFactory;
use Utilitte\Doctrine\Query\RawQueryFactory;
use Utilitte\Doctrine\QueryMetadataExtractor;
use Utilitte\Doctrine\Result\ResultFactory;
use Utilitte\Doctrine\Updation\UpdationFactory;

final class DoctrineExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('entityCollection.factory'))
			->setType(EntityCollectionFactory::class);

		$builder->addDefinition($this->prefix('dataManipulation.factory'))
			->setType(MultipleDataManipulationFactory::class);

		$builder->addDefinition($this->prefix('association.entityFactory'))
			->setType(EntityAssociationFactory::class);

		$builder->addDefinition($this->prefix('identityExtractor'))
			->setType(DoctrineIdentityExtractor::class);

		$builder->addDefinition($this->prefix('insertionFactory'))
			->setType(InsertionFactory::class);

		$builder->addDefinition($this->prefix('updationFactory'))
			->setType(UpdationFactory::class);

		$builder->addDefinition($this->prefix('resultFactory'))
			->setType(ResultFactory::class);

		$builder->addDefinition($this->prefix('nativeQueryBuilderFactory'))
			->setType(DefaultNativeQueryBuilderFactory::class);

		$builder->addDefinition($this->prefix('fetchByIdentifiers'))
			->setType(FetchByIdentifiers::class);

		$builder->addDefinition($this->prefix('rawQueryFactory'))
			->setType(RawQueryFactory::class);

		$builder->addDefinition($this->prefix('queryMetadataExtractor'))
			->setType(QueryMetadataExtractor::class);
	}

}
