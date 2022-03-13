<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Literal;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use LogicException;

final class Literal
{

	/** @var LiteralInterface[] */
	private array $parameters = [];

	private function __construct(
		private string $literal,
	)
	{
	}

	public static function create(string $literal): Literal
	{
		return new self($literal);
	}

	public function quote(mixed $value): Literal
	{
		$this->parameters[] = new LiteralQuote($value);

		return $this;
	}

	/**
	 * @param class-string $entity
	 */
	public function field(string $entity, string $field): self
	{
		$this->parameters[] = new LiteralField($entity, $field);

		return $this;
	}

	public function toString(EntityManagerInterface $em): string
	{
		$literal = $this->literal;

		foreach ($this->parameters as $parameter) {
			$literal = preg_replace('/\?/', $parameter->toString($em), $literal, 1, $count);

			if ($count === 0) {
				throw new LogicException(sprintf('Insufficient placeholders in literal %s.', $this->literal));
			}
		}

		if (str_contains($literal, '?')) {
			throw new LogicException(
				sprintf('Count of placeholders does not match of parameter in literal %s.', $this->literal)
			);
		}

		return $literal;
	}

}
