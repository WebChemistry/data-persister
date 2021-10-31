<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister\Bridge\Nette\Tracy;

use JetBrains\PhpStorm\Immutable;
use WebChemistry\DataPersister\ContextAwareDataPersisterInterface;
use WebChemistry\DataPersister\DataPersisterInterface;
use WebChemistry\DataPersister\Exceptions\DataPersisterNotFoundException;
use WebChemistry\DataPersister\ResumableDataPersisterInterface;

final class TracyDataPersister implements ContextAwareDataPersisterInterface
{

	/** @var array{bool, string} */
	#[Immutable(Immutable::PRIVATE_WRITE_SCOPE)]
	public array $stream = [];

	private bool $requireDataPersister = true;

	/**
	 * @param DataPersisterInterface[]
	 */
	public function __construct(
		private array $persisters
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
		$used = [];
		foreach ($this->persisters as $persister) {
			if ($persister->supports($data, $context)) {
				$found = true;
				$used[] = $persister::class;

				$data = $persister->persist($data, $context);
				if ($persister instanceof ResumableDataPersisterInterface && $persister->resumable($context)) {
					continue;
				}

				break;
			}
		}

		if ($this->requireDataPersister && !$found) {
			throw new DataPersisterNotFoundException(sprintf('DataPersister not found for %s.', get_debug_type($data)));
		}

		$this->stream[] = [
			true,
			is_object($data) ? sprintf('%s #%d', get_debug_type($data), spl_object_id($data)) : sprintf('array(%d)', count($data)),
			$used,
		];

		return $data;
	}

	/**
	 * @inheritdoc
	 */
	public function remove(object $data, array $context = []): void
	{
		$found = false;
		$used = [];
		foreach ($this->persisters as $persister) {
			if ($persister->supports($data, $context)) {
				$found = true;
				$used[] = $persister::class;

				$persister->remove($data, $context);
				if ($persister instanceof ResumableDataPersisterInterface && $persister->resumable($context)) {
					continue;
				}

				break;
			}
		}

		if ($this->requireDataPersister && !$found) {
			throw new DataPersisterNotFoundException(sprintf('DataPersister not found for %s.', get_debug_type($data)));
		}

		$this->stream[] = [
			false,
			is_object($data) ? sprintf('%s #%d', get_debug_type($data), spl_object_id($data)) : sprintf('array(%d)', count($data)),
			$used,
		];
	}

}
