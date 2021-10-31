<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister;

interface DataPersisterInterface
{

	public function supports(object $data): bool;

	public function persist(object $data): object;

	public function remove(object $data): void;

}
