<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Manipulation;

use Doctrine\ORM\Mapping\ClassMetadataInfo;

trait OnDuplicateKey
{

	/** @var array<string|int, mixed> */
	private array $duplicateKey = [];

	public function onDuplicateKey(?string $field, mixed $value): self
	{
		if ($field === null) {
			$this->duplicateKey[] = $value;
		} else {
			$this->duplicateKey[$field] = $value;
		}

		return $this;
	}

	private function getOnDuplicateKeySql(ClassMetadataInfo $metadata): string
	{
		if (!$this->duplicateKey) {
			return '';
		}

		return sprintf(
			' ON DUPLICATE KEY UPDATE %s',
			implode(', ', $this->processAssignment($metadata, $this->duplicateKey, false))
		);
	}

}
