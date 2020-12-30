<?php declare(strict_types = 1);

namespace Utilitte\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;

final class QueryMetadataExtractor
{

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	public function getTableName(string $entity): string
	{
		return $this->em->getClassMetadata($entity)->getTableName();
	}

	/**
	 * @param string[]|null $fields
	 * @return string[]
	 * @throws MappingException
	 */
	public function getColumns(string $entity, ?array $fields = null): array
	{
		$metadata = $this->em->getClassMetadata($entity);
		$fields ??= array_merge($metadata->getFieldNames(), $this->getSingleAssociationFields($metadata));

		$columns = [];

		foreach ($fields as $field) {
			$columns[$field] = $metadata->hasField($field)
				? $metadata->getColumnName($field)
				: $metadata->getSingleAssociationJoinColumnName($field);
		}

		return $columns;
	}

	/**
	 * @throws MappingException
	 */
	public function getColumn(string $entity, string $field): string
	{
		$metadata = $this->em->getClassMetadata($entity);

		return $metadata->hasField($field)
			? $metadata->getColumnName($field)
			: $metadata->getSingleAssociationJoinColumnName($field);
	}

	public function getClassMetadata(string $entity): ClassMetadata
	{
		return $this->em->getClassMetadata($entity);
	}

	/**
	 * @return string[]
	 */
	private function getSingleAssociationFields(ClassMetadata $metadata): array
	{
		$associations = array_map(
			fn (array $mapping): string => $mapping['fieldName'],
			$metadata->getAssociationMappings()
		);

		return array_filter(
			$associations,
			fn (string $field): bool => $metadata->isAssociationWithSingleJoinColumn($field)
		);
	}

}
