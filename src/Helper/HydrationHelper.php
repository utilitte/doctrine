<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Helper;

use LogicException;
use Nette\Utils\Arrays;

final class HydrationHelper
{

	/**
	 * @param array<string|int, mixed[]> $data
	 * @return mixed[]
	 */
	public static function associateKeyValue(array $data, string|int|null $key = null, string|int|null $value = null): array
	{
		if (!$data) {
			return [];
		}

		if ($key === null) {
			$first = current($data);

			if (count($first) < 2) {
				throw new LogicException('Result query must have 2 elements at least.');
			}

			$key = key($first);
			next($first);
			$value = key($first);
		}

		$return = [];
		foreach ($data as $item) {
			$return[$item[$key]] = $item[$value];
		}

		return $return;
	}

}
