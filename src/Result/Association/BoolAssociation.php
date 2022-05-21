<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Result\Association;

use JetBrains\PhpStorm\Deprecated;
use Utilitte\Doctrine\DoctrineIdentityExtractor;

#[Deprecated]
final class BoolAssociation implements BoolAssociationInterface
{

	/** @var array<int|string, true> */
	private array $ids;

	/**
	 * @param array<int|string, int|string>
	 */
	public function __construct(
		array $ids,
		private bool $default,
		private DoctrineIdentityExtractor $doctrineIdentityExtractor,
	)
	{
		foreach ($ids as $id) {
			$this->ids[$id] = true;
		}
	}

	public function has(mixed $value): bool
	{
		if (is_object($value)) {
			$value = $this->doctrineIdentityExtractor->extractIdentity($value);
		}

		return $this->ids[$value] ?? $this->default;
	}

}
