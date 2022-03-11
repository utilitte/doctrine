<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use LogicException;
use OutOfBoundsException;

final class NativeQuery
{

	private const SEARCH = ['join'];
	private const CONVERT_TYPES = ['int' => 'integer', 'bool' => 'boolean'];

	private ResultSetMappingBuilder $rsmBuilder;

	private int $sqlCounter = 0;

	public function __construct(
		private string $sql,
		private NativeQueryMetadata $nativeQueryMetadata,
		private EntityManagerInterface $em,
	)
	{
		$this->rsmBuilder = new ResultSetMappingBuilder($this->em, ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT);
		$this->sql = $this->parse($sql);
	}

	private function parse(string $sql): string
	{
		$functions = implode('|', array_map(fn (string $str) => preg_quote($str, '#'), self::SEARCH));
		if (preg_match_all('#(\\\\)?%(' . $functions . ')\(([^)]+)\)#', $sql, $matches, PREG_UNMATCHED_AS_NULL | PREG_SET_ORDER)) {
			/** @param array{string, string|null, string, string} $matches */
			foreach ($matches as $match) {
				if ($match[1] !== null) {
					continue;
				}

				$this->processJoin($this->parseArguments($match[3]));
			}
		}

		return preg_replace_callback(
			'#(\\\\)?%([a-zA-Z0-9_\.]+)(?:\(([^)]+)\))?#',
			[$this, 'pregReplace'],
			$sql,
			flags: PREG_UNMATCHED_AS_NULL
		);
	}

	/**
	 * @param array{string, string|null, string, string|null} $matches
	 */
	private function pregReplace(array $matches): string
	{
		if (isset($matches[1])) {
			return $matches[0];
		}

		$name = $matches[2];
		$arguments = [];
		if ($matches[3]) {
			$arguments = $this->parseArguments($matches[3]);
		}

		return $this->replace($name, $arguments);
	}

	public function getSql(): string
	{
		return $this->sql;
	}

	public function getRsm(): ResultSetMapping
	{
		return $this->rsmBuilder;
	}

	/**
	 * @param string $name
	 * @param string[] $arguments
	 */
	private function replace(string $name, array $arguments): string
	{
		return match ($name) {
			'select' => $this->replaceSelect($arguments),
			'join' => $this->replaceJoin($arguments),
			'from' => $this->replaceFrom($arguments),
			'scalar' => $this->replaceScalar($arguments),
			default => $this->replaceColumn([$name]),
		};
	}

	/**
	 * @param string[] $arguments
	 */
	private function replaceSelect(array $arguments): string
	{
		$selects = [];
		foreach ($arguments as $argument) {
			if (!str_contains($argument, '.')) {
				$entity = $this->nativeQueryMetadata->getEntityFromAlias($argument);

				if (!$entity instanceof NativeQueryJoinedEntityMetadata) {
					$this->rsmBuilder->addRootEntityFromClassMetadata($entity->entity, $argument);
				} else {
					$this->rsmBuilder->addJoinedEntityFromClassMetadata($entity->entity, $argument, $entity->parent->alias, $entity->relation);
				}

				$selects[] = $this->rsmBuilder->generateSelectClause([
					$argument => $argument,
				]);
			} else {
				[$alias, $field] = explode('.', $argument);

				$entity = $this->nativeQueryMetadata->getEntityFromAlias($alias);
				$column = $entity->metadata->getColumnName($field);
				$columnAlias = $column . '_' . $this->sqlCounter++;

				$this->rsmBuilder->addEntityResult($entity->entity, $entity->alias);
				$this->rsmBuilder->addFieldResult($entity->alias, $columnAlias, $field, $entity->entity);

				$selects[] = sprintf('%s.%s AS %s', $alias, $column, $columnAlias);
			}
		}

		return implode(', ', $selects);
	}

	/**
	 * @param string[] $arguments
	 */
	private function replaceJoin(array $arguments): string
	{
		$metadata = $this->nativeQueryMetadata->getEntityFromAlias($arguments[1]);

		if (!$metadata instanceof NativeQueryJoinedEntityMetadata) {
			throw new LogicException(sprintf('Alias %s must be joined entity.', $arguments[1]));
		}

		if ($metadata->parent->metadata->isAssociationInverseSide($metadata->relation)) {
			$field = $metadata->parent->metadata->getAssociationMapping($metadata->relation)['mappedBy'];
			$on = sprintf('ON %s.%s = %s.%s',
				$metadata->parent->alias,
				$metadata->metadata->getSingleAssociationReferencedJoinColumnName($field),
				$metadata->alias,
				$metadata->metadata->getSingleAssociationJoinColumnName($field),
			);
		} else {
			$on = sprintf('ON %s.%s = %s.%s',
				$metadata->parent->alias,
				$metadata->parent->metadata->getSingleAssociationJoinColumnName($metadata->relation),
				$metadata->alias,
				$metadata->parent->metadata->getSingleAssociationReferencedJoinColumnName($metadata->relation),
			);
		}

		return implode(' ', [$metadata->metadata->getTableName(), $metadata->alias, $on]);
	}

	public function replaceFrom(array $arguments): string
	{
		$metadata = $this->nativeQueryMetadata->getEntityFromAlias($arguments[0]);

		return implode(' AS ', [$metadata->metadata->getTableName(), $metadata->alias]);
	}

	private function replaceScalar(array $arguments): string
	{
		$type = $arguments[1] ?? 'string';
		$this->rsmBuilder->addScalarResult($arguments[0], $arguments[0], self::CONVERT_TYPES[$type] ?? $type);

		return $arguments[0];
	}

	/**
	 * @return string[]
	 */
	private function parseArguments(string $arguments): array
	{
		return array_map('trim', explode(',', $arguments));
	}

	/**
	 * @param string[] $arguments
	 */
	private function processJoin(array $arguments): void
	{
		try {
			$this->nativeQueryMetadata->addJoinedEntity($arguments[0], $arguments[1]);
		} catch (OutOfBoundsException) {}
	}

	/**
	 * @param string[] $arguments
	 */
	private function replaceColumn(array $arguments): string
	{
		$explode = explode('.', $arguments[0] ?? '');
		if (count($explode) !== 2) {
			throw new LogicException(sprintf('Invalid column %s', $arguments[0]));
		}

		[$alias, $field] = $explode;

		$entity = $this->nativeQueryMetadata->getEntityFromAlias($alias);
		if ($entity->metadata->hasAssociation($field)) {
			$column = $entity->metadata->getSingleAssociationJoinColumnName($field);
		} else {
			$column = $entity->metadata->getColumnName($field);
		}

		return implode('.', [$alias, $column]);
	}

}
