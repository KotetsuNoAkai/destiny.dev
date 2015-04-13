<?php

namespace Destiny\AppBundle\Form;

use Destiny\AppBundle\Entity\EmpresaContacto;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\Translator;

class EmpresaContactoType extends AbstractType
{
	protected $em, $translator;
	public $notList;

	public function __construct (EntityManager $em, Translator $translator)
	{
		$this->em = $em;
		$this->translator = $translator;

	}
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre','text',['label' =>$this->translator->trans ('empresaContacto.form.nombre')])
            ->add('direccion','text',['label' =>$this->translator->trans ('empresaContacto.form.direccion')])
            ->add('ciudad','text',['label' =>$this->translator->trans ('empresaContacto.form.ciudad')])
            ->add('provincia','text',['label' =>$this->translator->trans ('empresaContacto.form.provincia')])
            ->add('pais','text',['label' =>$this->translator->trans ('empresaContacto.form.pais')])
            ->add('telefono','integer',['label' =>$this->translator->trans ('empresaContacto.form.telefono')])
            ->add('movil','integer',['label' =>$this->translator->trans ('empresaContacto.form.movil')])
            ->add('fax','integer',['label' =>$this->translator->trans ('empresaContacto.form.fax')])
	        ->add ('estado', 'choice', ['label' => $this->translator->trans ('empresaContacto.form.estado'),
		        'choices' => [TRUE => $this->translator->trans ('form.active'),
			        FALSE => $this->translator->trans ('form.desactive')]])
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Destiny\AppBundle\Entity\EmpresaContacto'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'destiny_appbundle_empresacontacto';
    }

	public function newEntity ()
	{
		$empresa = new EmpresaContacto();

		return $empresa;
	}

	public function isDeletable ($empresa)
	{
		return FALSE;

	}

	public function isChangeable ($empresa)
	{

		return TRUE;
	}

	public function cantCreate ($empresa)
	{

		return TRUE;
	}
}
