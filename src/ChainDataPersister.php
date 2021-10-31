<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister;

use WebChemistry\DataPersister\Exceptions\DataPersisterNotFoundException;

final class ChainDataPersister implements ContextAwareDataPersisterInterface
{

	private bool $requireDataPersister = true;

	/**
	 * @param DataPersisterInterface[] $persisters
	 */
	public function __construct(
		private array $persisters,
	)
	{
	}

	public function setRequireDataPersister(bool $requireDataPersister): static
	{
		$this->requireDataPersister = $requireDataPersister;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function supports(object $data, array $context = []): bool
	{
		foreach ($this->persisters as $persister) {
			if ($persister->supports($data, $context)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function persist(object $data, array $context = []): object
	{
		$found = false;

		foreach ($this->persisters as $persister) {
			if ($persister->supports($data, $context)) {
				$found = true;
				$data = $persister->persist($data, $context);
				if ($persister instanceof ResumableDataPersisterInterface && $persister->resumable($context)) {
					continue;
				}

				return $data;
			}
		}

		if ($this->requireDataPersister && !$found) {
			throw new DataPersisterNotFoundException(sprintf('DataPersister not found for %s.', get_debug_type($data)));
		}

		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function remove(object $data, array $context = []): void
	{
		$found = false;
		foreach ($this->persisters as $persister) {
			if ($persister->supports($data, $context)) {
				$found = true;
				$persister->remove($data, $context);
				if ($persister instanceof ResumableDataPersisterInterface && $persister->resumable($context)) {
					continue;
				}

				return;
			}
		}

		if ($this->requireDataPersister && !$found) {
			throw new DataPersisterNotFoundException(sprintf('DataPersister not found for %s.', get_debug_type($data)));
		}
	}

}
