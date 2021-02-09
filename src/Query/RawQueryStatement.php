<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use FrontBundle\Entities\Status;
use InvalidArgumentException;
use Nette\Utils\Strings;
use Utilitte\Doctrine\Query\ValueObject\Alias;
use Utilitte\Doctrine\QueryMetadataExtractor;

final class RawQueryStatement
{

	private QueryMetadataExtractor $queryMetadataExtractor;

	private string $sql;

	private BuiltInFunctions $functions;

	private ResultSetMappingBuilder $rsmBuilder;

	/** @var Alias[] */
	private array $aliases = [];

	public function __construct(QueryMetadataExtractor $queryMetadataExtractor, string $sql)
	{
		$this->queryMetadataExtractor = $queryMetadataExtractor;
		$this->sql = $sql;

		$this->rsmBuilder = new ResultSetMappingBuilder(
			$queryMetadataExtractor->getEntityManager(),
			ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT
		);
		$this->functions = new BuiltInFunctions($queryMetadataExtractor, $this->rsmBuilder);
	}

	public function addScalarResult(string $column, string $alias, string $type = 'string'): self
	{
		$this->rsmBuilder->addScalarResult($column, $alias, $type);

		return $this;
	}

	public function addRootEntity(string $class, string $alias): self
	{
		$this->aliases[$alias] = new Alias($class, $alias);

		$this->rsmBuilder->addRootEntityFromClassMetadata($class, $alias);

		return $this;
	}

	public function addEntity(string $class, string $alias): self
	{
		$this->aliases[$alias] = new Alias($class, $alias);

		return $this;
	}

	public function getSql(): string
	{
		return Strings::replace(
			$this->sql,
			$this->buildRegex(array_keys($this->aliases)),
			fn (array $matches) => $this->replace($matches)
		);
	}

	public function getNativeQuery(): NativeQuery
	{
		$em = $this->queryMetadataExtractor->getEntityManager();

		return $em->createNativeQuery($this->getSql(), $this->rsmBuilder);
	}

	/**
	 * @param mixed[] $matches
	 */
	private function replace(array $matches): string
	{
		if ($matches[2] === 'fn') {
			return $this->replaceFunctions($matches);
		}
		if (!isset($this->aliases[$matches[2]])) {
			throw new InvalidArgumentException(sprintf('Alias %s not set', $matches[1]));
		}

		$alias = $this->aliases[$matches[2]];

		$prefix = $alias->getAlias();

		if (isset($matches[3])) {
			return $prefix . '.' . $this->queryMetadataExtractor->getColumn($alias->getClass(), $matches[3]);
		}

		return $this->queryMetadataExtractor->getTableName($alias->getClass()) . ' AS ' . $prefix;
	}

	/**
	 * @param mixed[] $matches
	 */
	private function replaceFunctions(array $matches): string
	{
		$name = $matches[3];
		$args = array_map('trim', explode(',', ($matches[4] ?? '')));

		return $this->functions->call($this->aliases, $name, ...$args);
	}

	/**
	 * @param string[] $aliases
	 */
	private function buildRegex(array $aliases): string
	{
		$aliases[] = 'fn';
		$group = implode('|', array_map(fn (string $alias) => preg_quote($alias, '#'), $aliases));

		$args = '(?:\((.*?)\))?';

		return '#%(%?)(' . $group . ')(?:\.(\w+)' . $args . ')?#';
	}

}
