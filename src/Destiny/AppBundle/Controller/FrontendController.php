<?php

namespace Destiny\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
/**
 * @Route("/")
 */
class FrontendController extends Controller
{
    /**
     * @Route("/", name="portadaWeb")
     */
    public function portadaAction()
    {
	    $em = $this->getDoctrine()->getManager();
	    $empresa = $em->getRepository('DestinyAppBundle:EmpresaWeb')->getEmpresaActiva();

	    if (null === $empresa)return $this->redirect($this->generateUrl('portadaInstalacion'));

		return $this->render ('DestinyAppBundle:Frontend:portada.html.twig',
			[

			]);

    }

}
