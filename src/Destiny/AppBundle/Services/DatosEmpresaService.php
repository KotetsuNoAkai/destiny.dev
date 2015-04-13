<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 06/04/2015
 * Time: 12:52
 */
namespace Destiny\AppBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;

class DatosEmpresaService
{
	protected $entityManager, $container;
	public $web, $contacto, $redesSociales;

	public function __construct (EntityManager $entityManager,Container $container)
	{
		$this->entityManager = $entityManager;
		$this->container = $container;
		$this->web = $this->getEmpresa();
	}

	public function getEmpresa()
	{
		return $this->entityManager->getRepository('DestinyAppBundle:EmpresaWeb')->getEmpresaActiva();
	}




}