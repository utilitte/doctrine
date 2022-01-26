<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Insertion;

final class InsertionFieldMetadata
{

	public function __construct(
		public string $columnName,
		public bool $nullable,
		public bool $hasDefault,
		public mixed $default,
		public ?string $type = null,
	)
	{
	}

}
