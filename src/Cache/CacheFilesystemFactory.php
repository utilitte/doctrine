<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Cache;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

final class CacheFilesystemFactory
{
	
	public function __construct(
		private string $tempDir,
	)
	{
	}

	public function create(string $namespace): Cache
	{
		return new FilesystemCache($this->tempDir . '/cache/' . $namespace);
	}

}
