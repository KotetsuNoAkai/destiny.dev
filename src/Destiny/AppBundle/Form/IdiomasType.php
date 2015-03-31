<?php

namespace Destiny\AppBundle\Form;


use Destiny\AppBundle\Entity\Idiomas;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Class IdiomasType
 * @package Destiny\AppBundle\Form
 *
 * @Todo CRUD + Test de cambio de estado + cambio de estado especial(Solo un unico TRUE)
 */
class IdiomasType extends AbstractType
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
	 */
	public function buildForm (FormBuilderInterface $builder, array $options)
	{
		$builder
			->add ('nombre', 'text', ['label' => $this->translator->trans ('languages.form.name')])
			->add ('isoCode', 'text', ['label' => $this->translator->trans ('languages.form.isoCode')])
			->add ('archivo', 'file', ['label' => $this->translator->trans ('languages.form.file')])
			->add ('estado', 'choice', ['label' => $this->translator->trans ('languages.form.status'),
				'choices' => [TRUE => $this->translator->trans ('form.status.active'),
					FALSE => $this->translator->trans ('form.status.active')]]);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions (OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults (array (
			'data_class' => 'Destiny\AppBundle\Entity\Idiomas'
		));
	}

	/**
	 * @return string
	 */
	public function getName ()
	{
		return 'destiny_appbundle_idiomas';
	}

	public function newEntity ()
	{
		$idioma = new Idiomas();

		return $idioma;
	}

	public function isDeletable ($idioma)
	{
		if ($idioma->getDefecto () === TRUE) {
			return FALSE;
		}

		return TRUE;
	}

	public function isChangeable ($idioma)
	{
		if ($idioma->getDefecto () === TRUE) {
			return FALSE;
		}

		return TRUE;
	}

	public function listElements ()
	{
		return
			[
				$this->translator->trans ('languages.list.isoCode') => 'isoCode',
				$this->translator->trans ('languages.list.flag') => 'image',
				$this->translator->trans ('languages.list.default') => 'changeStatus'
			];

	}
}
