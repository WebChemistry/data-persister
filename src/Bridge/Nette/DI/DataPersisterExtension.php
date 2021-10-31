<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister\Bridge\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;
use Tracy\Bar;
use Tracy\IBarPanel;
use WebChemistry\DataPersister\Bridge\Nette\Tracy\TracyDataPersister;
use WebChemistry\DataPersister\Bridge\Nette\Tracy\TracyPanel;
use WebChemistry\DataPersister\ChainDataPersister;
use WebChemistry\DataPersister\ContextAwareDataPersisterInterface;

final class DataPersisterExtension extends CompilerExtension
{

	public function __construct(
		private bool $debugMode = false,
	)
	{
	}

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'persisters' => Expect::arrayOf(Expect::anyOf(Expect::string(), Expect::type(Statement::class))),
			'tracy' => Expect::structure([
				'enable' => Expect::bool($this->debugMode),
			]),
			'requireDataPersister' => Expect::bool(true),
		]);
	}

	public function loadConfiguration(): void
	{
		/** @var stdClass $config */
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();
		$persisters = [];

		foreach ($config->persisters as $name => $persister) {
			if (!$persister instanceof Statement) {
				$persister = new Statement($persister);
			}

			$persisters[] = $builder->addDefinition($this->prefix('dataPersister.' . $name))
				->setAutowired(false)
				->setFactory($persister);
		}


		if ($config->tracy->enable && ($service = $builder->getByType(Bar::class))) {
			$persister = $builder->addDefinition($this->prefix('tracy.dataPersister'))
				->setType(ContextAwareDataPersisterInterface::class)
				->setFactory(TracyDataPersister::class, [$persisters]);

			$builder->addDefinition($this->prefix('tracy.panel'))
				->setType(IBarPanel::class)
				->setFactory(TracyPanel::class, [$persister]);

			$this->initialization->addBody(
				'$this->getService(?)->addPanel($this->getService(?));',
				[$service, $this->prefix('tracy.panel')]
			);
		} else {
			$service = new ServiceDefinition();
			$service->setType(ContextAwareDataPersisterInterface::class)
				->setFactory(ChainDataPersister::class, [$persisters])
				->addSetup('setRequireDataPersister', [$config->requireDataPersister]);

			$builder->addDefinition($this->prefix('chainDataPersister'), $service);
		}
	}

}
