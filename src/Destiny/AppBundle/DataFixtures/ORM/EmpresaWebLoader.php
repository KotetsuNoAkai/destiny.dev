<?php
namespace Argidomin\AppBundle\DataFixtures\ORM;


use Destiny\AppBundle\Entity\EmpresaWeb;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class EmpresaWebLoader extends AbstractFixture implements FixtureInterface, OrderedFixtureInterface
{
	public function load (ObjectManager $manager)
	{
		$empresa = new EmpresaWeb();

		$empresa->setNombre ('Destiny Proyect');
		$empresa->setEmail ('info@destiny-proyect.dev');
		$empresa->setSlogan ('Chouse your Destiny');
		$empresa->setMensajeBloqueo ('La web esta bloqueada, no toques los cojones');
		$empresa->setEstado (TRUE);
		$empresa->setRuta ('destiny-proyect.jpg');

		$manager->persist ($empresa);

		$manager->flush ();
	}

	public function getOrder ()
	{
		return 5; // the order in which fixtures will be loaded
	}
}