<?php declare(strict_types = 1);

namespace Utilitte\Doctrine;

use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use LogicException;

final class DoctrineIdentityExtractor
{

	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	/**
	 * @return mixed[]
	 */
	public function extractIdentities(object $entity): array
	{
		return $this->em->getClassMetadata(get_class($entity))->getIdentifierValues($entity);
	}

	/**
	 * @return mixed
	 */
	public function extractIdentity(object $entity)
	{
		$ids = $this->extractIdentities($entity);

		if (count($ids) !== 1) {
			throw new InvalidArgumentException(sprintf('Entity "%s" must have one identity', get_class($entity)));
		}

		return current($ids);
	}

	/**
	 * @param object[] $entities
	 * @return mixed[]
	 */
	public function extractIdentitiesMany(iterable $entities, bool $allowMixing = false): array
	{
		$type = null;
		$ids = [];

		foreach ($entities as $entity) {
			if (!is_object($entity)) {
				throw new InvalidArgumentException(
					sprintf('Given array must be an array of object, %s contained in array', gettype($entity))
				);
			}

			if (!$allowMixing) {
				if (!$type) {
					$type = $this->getClassNameFromEntity($entity);
				} else {
					$this->checkType($entity, $type);
				}
			}

			$ids[] = $this->extractIdentities($entity);
		}

		return $ids;
	}

	/**
	 * @param object[] $entities
	 * @return mixed
	 */
	public function extractIdentityMany(iterable $entities, bool $allowMixing = false)
	{
		$type = null;
		$ids = [];

		foreach ($entities as $entity) {
			if (!is_object($entity)) {
				throw new InvalidArgumentException(
					sprintf('Given array must be an array of object, %s contained in array', gettype($entity))
				);
			}

			if (!$allowMixing) {
				if (!$type) {
					$type = $this->getClassNameFromEntity($entity);
				} else {
					$this->checkType($entity, $type);
				}
			}

			$ids[] = $this->extractIdentity($entity);
		}

		return $ids;
	}

	private function getClassNameFromEntity(object $entity): string
	{
		if ($entity instanceof Proxy) {
			return get_parent_class($entity);
		}

		return get_class($entity);
	}

	private function checkType(object $entity, string $type): void
	{
		if (!$entity instanceof $type) {
			throw new InvalidArgumentException(
				sprintf('Given array must be an array of %s, %s contained in array', $type, $this->getClassNameFromEntity($entity))
			);
		}
	}

}
