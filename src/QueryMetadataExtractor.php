<?php declare(strict_types = 1);

namespace Utilitte\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;

final class QueryMetadataExtractor
{

	/** @var EntityManagerInterface */
	private $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}

	public function getTableName(string $entity): string {
		return $this->em->getClassMetadata($entity)->getTableName();
	}

	/**
	 * @param string[]|null $fields
	 * @return string[]
	 * @throws MappingException
	 */
	public function getColumns(string $entity, ?array $fields = null): array {
		$metadata = $this->em->getClassMetadata($entity);
		$fields = $fields === self::ALL
			? array_merge($metadata->getFieldNames(), $this->getSingleAssociationFields($metadata))
			: $fields;

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
	public function getColumn(string $entity, string $field): string {
		$metadata = $this->em->getClassMetadata($entity);

		return $metadata->hasField($field)
			? $metadata->getColumnName($field)
			: $metadata->getSingleAssociationJoinColumnName($field);
	}

	/**
	 * @return string[]
	 */
	private function getSingleAssociationFields(ClassMetadata $metadata): array {
		$associations = array_map(
			function (array $mapping): string {
				return $mapping['fieldName'];
			},
			$metadata->getAssociationMappings()
		);

		return array_filter(
			$associations,
			function (string $field) use ($metadata): bool {
				return $metadata->isAssociationWithSingleJoinColumn($field);
			}
		);
	}

}
