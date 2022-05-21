<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Association;

final class AssociationFactory
{

	/**
	 * @template TKey of Entity
	 * @template TValue
	 * @param class-string<TKey> $className
	 * @param iterable<TKey|string|int> $collection values are entities or ids
	 * @param TValue $value
	 * @return Association<TKey, TValue>
	 */
	public function createFixed(string $className, iterable $collection, mixed $value): Association
	{
		return new Association(
			$className,
			(function () use ($collection, $value): iterable {
				foreach ($collection as $entity) {
					yield $entity => $value;
				}
			})(),
		);
	}

}
