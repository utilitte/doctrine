<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Association;

use Doctrine\Common\Collections\Collection;
use Utilitte\Php\Arrays;
use Utilitte\Php\ValueObject\ArraySynchronized;

final class ArrayCollectionSynchronizer
{

	public const REMOVE = 0b0001;
	public const ADD = 0b0010;

	/**
	 * @template T of object
	 * @phpstan-param Collection<mixed, T> $collection
	 * @phpstan-param T[] $values
	 * @param object[] $values
	 */
	public static function synchronize(
		Collection $collection,
		array $values,
		int $options = self::ADD | self::REMOVE
	): ArraySynchronized
	{
		return self::synchronizeByComparator($collection, $values, null, $options);
	}

	/**
	 * @template T of object
	 * @phpstan-param Collection<mixed, T> $collection
	 * @phpstan-param T[] $values
	 * @param object[] $values
	 */
	public static function synchronizeByComparator(
		Collection $collection,
		array $values,
		?callable $comparator = null,
		int $options = self::ADD | self::REMOVE
	): ArraySynchronized
	{
		$synchronized = Arrays::synchronize($collection->toArray(), $values, $comparator);

		if ($opRemove = $options & self::REMOVE) {
			foreach ($synchronized->getRemoved() as $key => $_) {
				$collection->remove($key);
			}
		}

		if ($opAdd = $options & self::ADD) {
			foreach ($synchronized->getAdded() as $element) {
				$collection->add($element);
			}
		}

		return new ArraySynchronized(
			$opAdd ? $synchronized->getAdded() : [],
			$opRemove ? $synchronized->getRemoved() : [],
			$collection->toArray()
		);
	}

}
