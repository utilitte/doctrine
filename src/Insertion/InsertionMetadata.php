<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Insertion;

use DateTimeInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use LogicException;
use ReflectionClass;
use ReflectionProperty;

final class InsertionMetadata
{

	/** @var array<string, InsertionFieldMetadata> */
	private array $fields = [];

	public function __construct(
		private EntityManagerInterface $em,
		private ClassMetadata $metadata,
	)
	{
		$parameters = [];
		$reflClass = $this->metadata->reflClass;
		if ($reflClass && $reflClass->hasMethod('__construct')) {
			foreach ($reflClass->getMethod('__construct')->getParameters() as $parameter) {
				if ($parameter->isDefaultValueAvailable()) {
					$parameters[$parameter->getName()] = $parameter->getDefaultValue();
				}
			}
		}

		foreach ($this->metadata->fieldMappings as $field) {
			[$hasDefault, $default] = $this->getDefaultValue(
				$field['fieldName'],
				$parameters,
				$this->metadata->reflFields[$field['fieldName']] ?? null,
			);

			$reflClass = $this->metadata->reflClass;
			$this->fields[$field['fieldName']] = new InsertionFieldMetadata(
				$field['columnName'],
				$field['nullable'],
				$hasDefault,
				$default,
				$field['type'],
			);
		}

		foreach ($this->metadata->associationMappings as $field) {
			if ($this->metadata->isAssociationInverseSide($field['fieldName'])) {
				continue;
			}

			if (!$this->metadata->isAssociationWithSingleJoinColumn($field['fieldName'])) {
				continue;
			}

			$this->fields[$field['fieldName']] = new InsertionFieldMetadata(
				$this->metadata->getSingleAssociationJoinColumnName($field['fieldName']),
				$field['joinColumns'][0]['nullable'],
				$field['joinColumns'][0]['nullable'],
				null,
			);
		}
	}

	public function getTableName(): string
	{
		return $this->metadata->getTableName();
	}

	/**
	 * @param array<string, mixed> $parameters
	 * @return array{ bool, mixed }
	 */
	private function getDefaultValue(string $field, array $parameters, ?ReflectionProperty $reflectionProperty): array
	{
		if (array_key_exists($field, $parameters)) {
			return [true, $parameters[$field]];
		}

		return [$reflectionProperty->hasDefaultValue(), $reflectionProperty->getDefaultValue()];
	}

	public function getColumn(string $field): string
	{
		return $this->getField($field)->columnName;
	}

	public function getDefault(string $field): mixed
	{
		if (!$this->getField($field)->hasDefault) {
			if ($this->getField($field)->nullable) {
				return null;
			}

			throw new LogicException(sprintf('Field %s does not have default value.', $field));
		}

		return $this->getField($field)->default;
	}

	public function getField(string $field): InsertionFieldMetadata
	{
		return $this->fields[$field] ?? throw new LogicException(
			sprintf('Field %s not exists in %s', $field, $this->metadata->getName())
		);
	}

	/**
	 * @param mixed[] $values
	 * @return array<string, mixed>
	 */
	public function processValues(array $values): array
	{
		$return = [];
		$fields = $this->fields;

		foreach ($values as $fieldName => $value) {
			$return[$this->getColumn($fieldName)] = $this->escape($value, $this->getField($fieldName));

			unset($fields[$fieldName]);
		}

		foreach ($fields as $fieldName => $field) {
			$return[$field->columnName] = $this->escape($this->getDefault($fieldName), $this->getField($fieldName));
		}

		return $return;
	}

	private function escape(mixed $value, InsertionFieldMetadata $metadata): string
	{
		if ($value instanceof DateTimeInterface) {
			if ($metadata->type === 'date') {
				$value = $value->format('Y-m-d');
			} else if ($metadata->type === 'datetime') {
				$value = $value->format('Y-m-d H:i:s');
			}
		}

		$type = ParameterType::STRING;

		if (is_int($value)) {
			$type = ParameterType::INTEGER;
		} else if (is_bool($type)) {
			$type = ParameterType::BOOLEAN;
		} else if ($value === null) {
			return 'NULL';
		}

		return $this->em->getConnection()->quote($value, $type);
	}

}
