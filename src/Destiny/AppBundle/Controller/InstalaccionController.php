<?php

namespace Destiny\AppBundle\Controller;

use Destiny\AppBundle\Entity\EmpresaContacto;
use Destiny\AppBundle\Entity\EmpresaRedesSociales;
use Destiny\AppBundle\Entity\EmpresaWeb;
use Destiny\AppBundle\Entity\Idiomas;
use Destiny\AppBundle\Entity\Usuarios;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/instalacion")
 * @Template()
 */
class InstalaccionController extends Controller
{
    /**
     * @Route("/", name="portadaInstalacion")
     * @Template()
     */
    public function datosEmpresaAction(Request $request)
    {
	    $em = $this->getDoctrine()->getManager();

	    if (null != $em->getRepository('DestinyAppBundle:EmpresaWeb')->getEmpresaActiva())
		    return $this->redirect($this->generateUrl('portadaWeb'));

	    $empresa = new EmpresaWeb();

	    $formulario = $this->createForm($this->get('empresaweb'),$empresa);
	    $formulario->handleRequest($request);

	    if (($formulario->isSubmitted ()) && ($formulario->isValid ()))
	    {
			$empresa->upload();
		    $em->persist ($empresa);
		    $em->flush ();

		    $traductor = $this->get('translator');

		    $this->get ('session')->getFlashBag()->set ('success', [
			    'title' => $traductor->trans ('flash.datosEmpresa.title'),
			    'message' => $traductor->trans ('flash.datosEmpresa.message', ['entidad' => $empresa])
		    ]);

		    return $this->redirect($this->generateUrl('empresaContacto',['empresa' => $empresa->getSlug()]));
	    }

	    $traductor = $this->get('translator');

	    $this->get ('session')->getFlashBag()->set ('info', [
		    'title' => $traductor->trans ('flash.instalacion.title'),
		    'message' => $traductor->trans ('flash.instalacion.message')
	    ]);

		return $this->render('DestinyAppBundle:Instalaccion:datosEmpresa.html.twig',
							['form' => $formulario->createView()]
							);
    }

    /**
     * @Route("/empresa-contacto/{empresa}",name="empresaContacto")
     * @ParamConverter("empresa", class="DestinyAppBundle:EmpresaWeb",
     * options={"empresa" = "slug", "repository_method" = "findOneBySlug"})
     */
    public function datosEmpresaContactoAction(EmpresaWeb $empresa, Request $request)
    {
	    $em = $this->getDoctrine()->getManager();
	    $contacto = new EmpresaContacto();

	    $formulario = $this->createForm($this->get('empresacontacto'),$contacto);
	    $formulario->handleRequest($request);

	    if (($formulario->isSubmitted ()) && ($formulario->isValid ()))
	    {
		    $em->persist ($contacto);
		    $em->flush ();

		    $traductor = $this->get('translator');

		    $this->get ('session')->getFlashBag()->set ('success', [
			    'title' => $traductor->trans ('flash.datosContacto.title'),
			    'message' => $traductor->trans ('flash.datosContacto.message', ['entidad' => $empresa])
		    ]);

		    return $this->redirect($this->generateUrl('empresaRedesSociales',
			        [
				        'empresa'  => $empresa->getSlug(),
				        'contacto' => $contacto->getSlug()
			        ]));
	    }
	    return $this->render ('DestinyAppBundle:Instalaccion:datosEmpresaContacto.html.twig',
		    [
			    'empresa' => $empresa,
			    'form'    => $formulario->createView()
		    ]
	    );
    }

    /**
     * @Route("/empresa-redes-sociales/{empresa}/{contacto}",name="empresaRedesSociales")
     * @ParamConverter("empresa", class="DestinyAppBundle:EmpresaWeb",
     * options={"empresa" = "slug", "repository_method" = "findOneBySlug"})
     * @ParamConverter("contacto", class="DestinyAppBundle:EmpresaContacto",
     * options={"contacto" = "slug", "repository_method" = "findOneBySlug"})
     */
    public function datosEmpresaRedesSocialAction(EmpresaWeb $empresa, EmpresaContacto $contacto, Request $request)
    {
	    $em = $this->getDoctrine()->getManager();
	    $social = new EmpresaRedesSociales();

	    $formulario = $this->createForm($this->get('empresaredessociales'),$social);
	    $formulario->handleRequest($request);

	    if (($formulario->isSubmitted ()) && ($formulario->isValid ()))
	    {
		    $em->persist ($social);
		    $em->flush ();



		    $traductor = $this->get('translator');

		    $this->get ('session')->getFlashBag()->set ('success', [
			    'title' => $traductor->trans ('flash.datosSocial.title'),
			    'message' => $traductor->trans ('flash.datosSocial.message', ['entidad' => $social->getNombre()])
		    ]);

		    unset($social);

	    }
	    return $this->render ('DestinyAppBundle:Instalaccion:datosEmpresaRedesSocial.html.twig',
		    [
			    'empresa' => $empresa,
			    'contacto' => $contacto,
			    'redes'   => $em->getRepository("DestinyAppBundle:EmpresaRedesSociales")->findAll(),
			    'form'    => $formulario->createView()
		    ]
	    );
    }

    /**
     * @Route("/empresa-idioma-defecto/{empresa}/{contacto}",name="empresaIdiomaDefecto")
     * @ParamConverter("empresa", class="DestinyAppBundle:EmpresaWeb",
     * options={"empresa" = "slug", "repository_method" = "findOneBySlug"})
     * @ParamConverter("contacto", class="DestinyAppBundle:EmpresaContacto",
     * options={"contacto" = "slug", "repository_method" = "findOneBySlug"})
     */
    public function idiomaDefectoAction(EmpresaWeb $empresa, EmpresaContacto $contacto, Request $request)
    {
	    $em = $this->getDoctrine()->getManager();
	    $idioma = new Idiomas();

	    $formulario = $this->createForm($this->get('idiomas'),$idioma);
	    $formulario->remove('estado');
	    $formulario->handleRequest($request);

	    if (($formulario->isSubmitted ()) && ($formulario->isValid ()))
	    {
		    $idioma->upload();
		    $idioma->setDefecto(true);
		    $idioma->setEstado(true);
		    $em->persist ($idioma);
		    $em->flush ();

		    $traductor = $this->get('translator');

		    $this->get ('session')->getFlashBag()->set ('success', [
			    'title' => $traductor->trans ('flash.idioma.title'),
			    'message' => $traductor->trans ('flash.idioma.message', ['entidad' => $idioma])
		    ]);

		    return $this->redirect($this->generateUrl('empresaUsuarioAdmin',
			    [
				    'empresa'  => $empresa->getSlug(),
				    'contacto' => $contacto->getSlug(),
				    'idioma'   => $idioma->getSlug()
			    ]));

	    }

	    return $this->render ('DestinyAppBundle:Instalaccion:idiomaDefecto.html.twig',
		    [
			    'empresa' => $empresa,
			    'contacto' => $contacto,
			    'redes'   => $em->getRepository("DestinyAppBundle:EmpresaRedesSociales")->findAll(),
			    'form'    => $formulario->createView()
		    ]
	    );
    }

	/**
	 * @Route("/empresa-usuario-admin/{empresa}/{contacto}/{idioma}",name="empresaUsuarioAdmin")
	 * @ParamConverter("empresa", class="DestinyAppBundle:EmpresaWeb",
	 * options={"empresa" = "slug", "repository_method" = "findOneBySlug"})
	 * @ParamConverter("contacto", class="DestinyAppBundle:EmpresaContacto",
	 * options={"contacto" = "slug", "repository_method" = "findOneBySlug"})
	 *  @ParamConverter("idioma", class="DestinyAppBundle:Idiomas",
	 * options={"idioma" = "slug", "repository_method" = "findOneBySlug"})
	 */

	public function crearUsuarioAdminAction(EmpresaWeb $empresa, EmpresaContacto $contacto, Idiomas $idioma, Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$usuarios = new Usuarios();

		$formulario = $this->createForm($this->get('usuarios'),$usuarios);
		$formulario->remove('estado');
		$formulario->handleRequest($request);
		$redes = $em->getRepository ("DestinyAppBundle:EmpresaRedesSociales")->findAll ();
		if (($formulario->isSubmitted ()) && ($formulario->isValid ()))
		{

			$usuarios->setRoles(['ROLE_ROOT']);
			$usuarios->setEstado(true);
			$em->persist ($usuarios);
			$em->flush ();

			$traductor = $this->get('translator');

			$this->get ('session')->getFlashBag()->set ('success', [
				'title' => $traductor->trans ('flash.usuarioRoot.title'),
				'message' => $traductor->trans ('flash.usuarioRoot.message', ['entidad' => $usuarios])
			]);

			$this->get('email')->enviarEmailInstalacion($empresa,$usuarios, $contacto, $redes);

			return $this->redirect($this->generateUrl('portadaWeb'));

		}
		return $this->render ('DestinyAppBundle:Instalaccion:crearUsuarioAdmin.html.twig',
			[
				'empresa'  => $empresa,
				'contacto' => $contacto,
				'redes'    => $redes,
				'idioma'   => $idioma,
				'form'     => $formulario->createView ()
			]
		);

	}

}
