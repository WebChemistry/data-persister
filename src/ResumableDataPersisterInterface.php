<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister;

interface ResumableDataPersisterInterface
{

	/**
	 * Should we continue calling the next DataPersister or stop after this one?
	 * Defaults to stop the ChainDataPersister if this interface is not implemented.
	 *
	 * @param mixed[] $context
	 */
	public function resumable(array $context = []): bool;

}
