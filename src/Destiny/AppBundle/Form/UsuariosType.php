<?php

namespace Destiny\AppBundle\Form;



use Destiny\AppBundle\Entity\Newsletter;
use Destiny\AppBundle\Entity\Usuarios;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
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
	protected $em, $translator;

	public function __construct (EntityManager $em, Translator $translator)
	{
		$this->em = $em;
		$this->translator = $translator;

	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array                $options
	 *  * @ TODO Añadir y traducir campos
	 */
	public function buildForm (FormBuilderInterface $builder, array $options)
	{
		$builder
			->add ('email', 'text', ['label' => $this->translator->trans ('newsletter.form.email'),
									  'max_length' => 10]);
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

	public function preCreateSave($usuario)
	{
		$usuario->setEstado(false);

		return $usuario;
	}

	//@TODO El ROOT no puede ser borrado
	public function isDeletable ($usuario)
	{
		return TRUE;
	}

	//@TODO El ROOT no puede ser desactivado
	public function isChangeable ($usuario)
	{

		return TRUE;
	}

	//@TODO Añadir elementos personalizados
	public function listElements ()
	{
		return null;
	}
}
