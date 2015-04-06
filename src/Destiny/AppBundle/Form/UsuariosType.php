<?php

namespace Destiny\AppBundle\Form;



use Destiny\AppBundle\Entity\Usuarios;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Class UserType
 * @package Destiny\AppBundle\Form
 *

 */
class UsuariosType extends AbstractType
{
	protected $em, $translator, $container;

	public function __construct (EntityManager $em, Translator $translator, Container $container)
	{
		$this->em = $em;
		$this->translator = $translator;
		$this->container = $container;

	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 */
	public function buildForm (FormBuilderInterface $builder, array $options)
	{
		$builder
			->add ('email', 'email', ['label' => $this->translator->trans ('usuarios.form.email'),
									  'max_length' => 100])

			->add('username','text',['label' => $this->translator->trans ('usuarios.form.username')])
			->add('plainPassword','password',['label' => $this->translator->trans ('usuarios.form.password')])
			;
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions (OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults (array (
				'data_class' => 'Destiny\AppBundle\Entity\Usuarios'
		));
	}

	/**
	 * @return string
	 */
	public function getName ()
	{
		return 'destiny_appbundle_usuarios';
	}

	//@TODO Por defecto, los usuarios son NORMAL_USERS
	public function newEntity ()
	{
		$usuario = new Usuarios();

		return $usuario;
	}

	public function isDeletable ($usuario)
	{
		if (in_array('ROLE_ROOT',$usuario->getRoles()))
		{
			return false;
		}

		return TRUE;
	}


	public function isChangeable ($usuario)
	{

		if (in_array('ROLE_ROOT',$usuario->getRoles()))
		{
			return false;
		}

		return TRUE;
	}


	public function groups ()
	{
		return ['ROLE_NORMALUSER' =>'Normal','ROLE_ROOT' => 'Root'];
	}

	public function postEdit ($usuario)
	{
		$this->container->get ('email')->enviarEmailUsuario ('edit', $usuario);
	}

	public function postCreate ($usuario)
	{
		$usuario->setEstado (FALSE);
		$this->container->get ('email')->enviarEmailUsuario ('create', $usuario);

		return $usuario;
	}

	public function postDelete ($usuario)
	{

		$this->container->get ('email')->enviarEmailUsuario ('delete', $usuario);
	}
}
