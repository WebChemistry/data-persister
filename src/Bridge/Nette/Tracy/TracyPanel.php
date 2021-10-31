<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister\Bridge\Nette\Tracy;

use Tracy\Helpers;
use Tracy\IBarPanel;
use WebChemistry\DataPersister\DataPersisterInterface;

final class TracyPanel implements IBarPanel
{

	private ?TracyDataPersister $dataPersister;

	public function __construct(
		?DataPersisterInterface $dataPersister = null,
	)
	{
		$this->dataPersister = $dataPersister instanceof TracyDataPersister ? $dataPersister : null;
	}

	public function getTab(): string
	{
		if (!$this->dataPersister) {
			return '';
		}

		return Helpers::capture(function (): void {
			$count = count($this->dataPersister->stream);

			require __DIR__ . '/templates/tab.phtml';
		});
	}

	public function getPanel(): string
	{
		if (!$this->dataPersister) {
			return '';
		}

		return Helpers::capture(function (): void {
			$stream = $this->dataPersister->stream;

			require __DIR__ . '/templates/panel.phtml';
		});
	}

}
