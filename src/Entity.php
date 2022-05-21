<?php declare(strict_types = 1);

namespace Utilitte\Doctrine;

use Utilitte\Doctrine\Identity\EntityUniqueIdentity;

interface Entity
{

	public function getUniqueIdentity(): EntityUniqueIdentity;

}
