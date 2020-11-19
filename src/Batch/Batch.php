<?php declare(strict_types = 1);

namespace Utilitte\Doctrine\Batch;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Iterator;
use Nette\Utils\Paginator as NettePaginator;

/**
 * @template T as object
 * @implements Iterator<T>
 */
final class Batch implements Iterator
{

	/**
	 * @phpstan-var Paginator<T>
	 */
	private Paginator $paginator;

	private NettePaginator $nettePaginator;

	private bool $continue = true;

	private int $limitPerBatch;

	/**
	 * @phpstan-param Paginator<T> $paginator
	 */
	public function __construct(Paginator $paginator, int $limitPerBatch)
	{
		$this->paginator = $paginator;
		$this->limitPerBatch = $limitPerBatch;
	}

	/**
	 * @return mixed[]
	 */
	public function current(): iterable
	{
		return $this->paginator->getQuery()
			->setMaxResults($this->limitPerBatch)
			->setFirstResult($this->nettePaginator->getOffset())
			->getResult();
	}

	public function next(): void
	{
		$this->nettePaginator->setPage($this->nettePaginator->getPage() + 1);
	}

	public function key(): int
	{
		return $this->nettePaginator->getPage();
	}

	public function valid(): bool
	{
		$continue = $this->continue;
		$this->continue = !$this->nettePaginator->isLast();

		return $continue;
	}

	public function rewind(): void
	{
		$paginator = new NettePaginator();
		$paginator->setItemCount($this->paginator->count());
		$paginator->setItemsPerPage($this->limitPerBatch);
		$paginator->setPage(1);

		$this->nettePaginator = $paginator;
		$this->continue = $paginator->getItemCount() !== 0;
	}

}
