<?php declare(strict_types = 1);

namespace Utilitte\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Utilitte\Php\ArraySort;

final class FetchByIdentifiers
{

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	public function fetch(string $entity, array $ids): array
	{
		$metadata = $this->em->getClassMetadata($entity);
		$identifiers = $metadata->getIdentifierFieldNames();
		if (count($identifiers) !== 1) {
			throw new LogicException(sprintf('%s entity must have one identifier', $entity));
		}
		$column = $identifiers[0];

		$result = $this->em->createQueryBuilder()
			->select('e')
			->from($entity, 'e')
			->where(sprintf('e.%s IN(:ids)', $column))
			->setParameter('ids', $ids)
			->getQuery()
			->getResult();

		return ArraySort::byGivenValues(
			$ids,
			$result,
			fn (object $entity) => $metadata->getIdentifierValues($entity)[$column]
		);
	}

}
