<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister;

interface ContextAwareDataPersisterInterface extends DataPersisterInterface
{

	public function supports(object $data, array $context = []): bool;

	public function persist(object $data, array $context = []): object;

	public function remove(object $data, array $context = []): void;
}
