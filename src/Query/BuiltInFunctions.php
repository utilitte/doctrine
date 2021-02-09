<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Query;

use Doctrine\ORM\Query\ResultSetMappingBuilder;
use InvalidArgumentException;
use Utilitte\Doctrine\Query\ValueObject\Alias;
use Utilitte\Doctrine\QueryMetadataExtractor;

final class BuiltInFunctions
{

	private QueryMetadataExtractor $queryMetadataExtractor;

	private ResultSetMappingBuilder $rsmBuilder;

	public function __construct(QueryMetadataExtractor $queryMetadataExtractor, ResultSetMappingBuilder $rsmBuilder)
	{
		$this->queryMetadataExtractor = $queryMetadataExtractor;
		$this->rsmBuilder = $rsmBuilder;
	}

	/**
	 * @param Alias[] $aliases
	 */
	public function call(array $aliases, string $name, string ...$args)
	{
		if ($name === 'call' || !method_exists($this, $name)) {
			throw new InvalidArgumentException(sprintf('Raw query function %s not exists', $name));
		}

		return $this->$name($aliases, ...$args);
	}

	private function select(): string
	{
		return $this->rsmBuilder->generateSelectClause();
	}

}
