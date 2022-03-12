<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\SQLParserUtils;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;

final class NativeQueryBuilder extends QueryBuilder
{

	private NativeQueryMetadata $nativeQueryMetadata;

	private QueryBuilder $queryBuilder;

	/** @var ArrayCollection<int, Parameter> */
	private ArrayCollection $parameters;

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
		$this->queryBuilder = new QueryBuilder();
		$this->nativeQueryMetadata = new NativeQueryMetadata($this->em);
		$this->parameters = new ArrayCollection();
	}

	public function setParameter(string|int $key, mixed $value, string|int|null $type = null): self
	{
		$existing = $this->getParameter($key);

		if ($existing !== null) {
			$existing->setValue($value, $type);

			return $this;
		}
		$this->parameters->add(new Parameter($key, $value, $type));

		return $this;
	}

	public function getParameter(string|int $key): ?Parameter
	{
		return $this->parameters->get($key);
	}

	public function selectFields(string ...$select): self
	{
		$this->select($this->wrap('select', $select));

		return $this;
	}

	public function selectScalar(string $select, string $alias, ?string $type = null): self
	{
		$this->select($this->processScalar($select, $alias, $type));

		return $this;
	}

	public function addSelectScalar(string $select, string $alias, ?string $type = null): self
	{
		$this->addSelect($this->processScalar($select, $alias, $type));

		return $this;
	}

	private function processScalar(string $select, string $alias, ?string $type = null): string
	{
		return sprintf('%s AS %%scalar(%s%s)', $select, $alias, $type === null ? '' : ', ' . $type);
	}

	public function addSelectFields(string ...$select): self
	{
		$this->addSelect($this->wrap('select', $select));

		return $this;
	}

	public function leftJoinEntity(string $field, string $alias): self
	{
		$this->leftJoin('%' . sprintf('join(%s, %s)', $field, $alias));

		return $this;
	}

	public function rightJoinEntity(string $field, string $alias): self
	{
		$this->rightJoin('%' . sprintf('join(%s, %s)', $field, $alias));

		return $this;
	}

	/**
	 * @param class-string $entity
	 */
	public function fromEntity(string $entity, string $alias): self
	{
		$this->nativeQueryMetadata->addEntity($entity, $alias);
		$this->from('%' . sprintf('from(%s)', $alias));

		return $this;
	}

	/**
	 * @param class-string $entity
	 */
	public function addEntityMeta(string $entity, string $alias): self
	{
		$this->nativeQueryMetadata->addEntity($entity, $alias);

		return $this;
	}

	/**
	 * @param string[] $wrap
	 */
	private function wrap(string $type, array $wrap): string
	{
		return '%' . sprintf('%s(%s)', $type, implode(', ', $wrap));
	}

	public function getResult(): mixed
	{
		return $this->getQuery()->getResult();
	}

	public function getSingleResult(): mixed
	{
		return $this->getQuery()->getSingleResult();
	}

	public function getSingleScalarResult(): mixed
	{
		return $this->getQuery()->getSingleScalarResult();
	}

	public function getOneOrNullResult(): mixed
	{
		return $this->getQuery()->getOneOrNullResult();
	}

	public function getScalarResult(): mixed
	{
		return $this->getQuery()->getScalarResult();
	}

	public function fetchAllAssociative(): mixed
	{
		$types = [];
		$params = [];

		foreach ($this->parameters as $parameter) {
			$types[$parameter->getName()] = $parameter->getType();
			$params[$parameter->getName()] = $parameter->getValue();
		}

		return $this->em->getConnection()->executeQuery($this->getSql(), $params, $types)->fetchAllAssociative();
	}

	public function getArrayResult(): mixed
	{
		return $this->getQuery()->getArrayResult();
	}

	public function getQuery(): \Doctrine\ORM\NativeQuery
	{
		$query = $this->createNativeQuery();

		return $this->em->createNativeQuery($query->getSql(), $query->getRsm())
			->setParameters($this->parameters);
	}

	public function getSql(): string
	{
		return $this->createNativeQuery()->getSql();
	}

	public function createNativeQuery(): NativeQuery
	{
		return new NativeQuery($this->getNativeSql(), $this->nativeQueryMetadata, $this->em);
	}

	public function getNativeSql(): string
	{
		return parent::getSql();
	}

}
