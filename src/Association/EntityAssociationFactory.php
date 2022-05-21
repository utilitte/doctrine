<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Association;

final class EntityAssociationFactory
{

	/**
	 * @template TKey of Entity
	 * @template TValue
	 * @param class-string<TKey> $className
	 * @param iterable<TKey|string|int> $collection values are entities or ids
	 * @param TValue $value
	 * @return EntityAssociation<TKey, TValue>
	 */
	public function createFixed(string $className, iterable $collection, mixed $value): EntityAssociation
	{
		return new EntityAssociation(
			$className,
			(function () use ($collection, $value): iterable {
				foreach ($collection as $entity) {
					yield $entity => $value;
				}
			})(),
		);
	}

}
