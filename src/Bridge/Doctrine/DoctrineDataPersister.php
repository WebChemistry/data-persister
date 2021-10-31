<?php declare(strict_types = 1);

namespace WebChemistry\DataPersister\Bridge\Doctrine;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use WebChemistry\DataPersister\DataPersisterInterface;

class DoctrineDataPersister implements DataPersisterInterface
{

	public function __construct(
		private EntityManagerInterface $em,
	)
	{
	}

	public function supports(object $data): bool
	{
		return !$this->em->getMetadataFactory()->isTransient(ClassUtils::getClass($data));
	}

	public function persist(object $data): object
	{
		$this->em->persist($data);
		$this->em->flush();

		return $data;
	}

	public function remove(object $data): void
	{
		$this->em->remove($data);
		$this->em->flush();
	}


}
