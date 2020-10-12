<?php declare(strict_types = 1);

namespace Utilitte\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

final class DoctrineIdentityExtractor
{

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	/**
	 * @param object[] $entities
	 * @return mixed[]
	 */
	public function extractIdentities(iterable $entities, bool $allowTypeMixing = false): array
	{
		$type = null;
		$ids = [];

		foreach ($entities as $entity) {
			if (!is_object($entity)) {
				throw new InvalidArgumentException(
					sprintf('Given array must be an array of object, %s contained in array', gettype($entity))
				);
			}

			$className = get_class($entity);

			if (!$allowTypeMixing) {
				$this->checkType($className, $type);
			}

			$values = $this->em->getClassMetadata($className)->getIdentifierValues($entity);
			$ids[] = current($values);
		}

		return $ids;
	}

	private function checkType(string $class, ?string $type): void
	{
		if (!$type) {
			return;
		}

		if ($type !== $class) {
			throw new InvalidArgumentException(
				sprintf('Given array must be an array of %s, %s contained in array', $type, $class)
			);
		}
	}

}
