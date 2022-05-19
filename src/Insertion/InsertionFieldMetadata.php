<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Insertion;

use JetBrains\PhpStorm\Deprecated;

#[Deprecated]
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
