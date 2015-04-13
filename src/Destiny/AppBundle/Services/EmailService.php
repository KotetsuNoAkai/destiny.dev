<?php
/**
 * Created by PhpStorm.
 * User: HP
 * Date: 06/04/2015
 * Time: 12:52
 */
namespace Destiny\AppBundle\Services;


use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;

class EmailService
{
	protected $twig, $mailer, $entityManager, $container;

	public function __construct (\Swift_Mailer $mailer,
	                             EngineInterface $templating,
	                             EntityManager $entityManager,
	                             Container $container)
	{
		$this->mailer = $mailer;
		$this->twig = $templating;
		$this->entityManager = $entityManager;
		$this->container = $container;

	}

	/**
	 * @param $message
	 *
	 * @TODO Defimir asunto y contenido del mensaje de manera dinamica.
	 */
	public function enviarEmailContacto ($message)
	{
		$email = \Swift_Message::newInstance ()
			->setSubject ('')
			->setTo ($message->getEmail ())
			->setBody ($this->twig
				->render ('AppBundle:Email:contacto.html.twig', ['message' => $message]));

		$this->mailer->send ($email);

	}

	public function enviarEmailUsuario ($metodo, $usuario)
	{

		$email = \Swift_Message::newInstance ()
			->setSubject ('')
			->setTo ($usuario->getEmail ())
			->setBody ($this->twig
				->render ('DestinyAppBundle:Email:usuario.html.twig',
					['usuario' => $usuario,
						'metodo' => $metodo]));

		$this->mailer->send ($email);
	}

	public function enviarEmailInstalacion ($empresa, $usuario, $contacto, $redes)
	{

		$email = \Swift_Message::newInstance ()
			->setSubject ('')
			->setTo ($usuario->getEmail (),$empresa->getEmail())
			->setBody ($this->twig
				->render ('DestinyAppBundle:Email:instalacion.html.twig',
					['usuario' => $usuario,
						'empresa' => $empresa,
						'contacto' => $contacto,
						'redes' => $redes
					]));

		$this->mailer->send ($email);
	}


}