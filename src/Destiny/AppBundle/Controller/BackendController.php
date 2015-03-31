<?php

namespace Destiny\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class BackendController
 * @package Destiny\AppBundle\Controller
 *
 * @TODO Añadir el listado de entidades cuando estas editando.
 */
class BackendController extends Controller
{
	/**
	 * @Route("/",name="indexBackend")
	 */
	public function indexAction ()
	{
		//@Todo Personalizar la salida de la portada.
		return $this->render ('DestinyAppBundle:Backend:index.html.twig',
			[

			]);
	}

	/**
	 * @Route("/list/{entity}/",name="listBackend")
	 */
	public function listBackendAction ($entity)
	{
		$em = $this->getDoctrine ()->getManager ();

		//Listamos todas los elementos de la entidad.
		$list = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity))->findAll ();

		//@Todo Personalizar la salida. listar solo los campos seleccionados.
		return $this->render ('DestinyAppBundle:Backend:list.html.twig',
			[
				'list' => $list,
				'entity' => $entity
			]);
	}

	/**
	 * @Route("/create/{entity}/", name="createBackend")
	 *
	 */
	public function createBackendAction (Request $request, $entity)
	{
		$em = $this->getDoctrine ()->getManager ();

		$new = $this->get (strtolower ($entity))->newEntity ();

		$formulario = $this->createForm ($this->get (strtolower ($entity)), $new);

		$formulario->handleRequest ($request);

		if (($formulario->isSubmitted ()) && ($formulario->isValid ())) {
			if (method_exists ($new, 'upload')) $new->upload ();
			$em->persist ($new);
			$em->flush ();

			$traductor = $this->get ('translator');

			$this->get ('session')->getFlashBag ()->set ('success', [
				'title' => $traductor->trans ('flash.create.title'),
				'message' => $traductor->trans ('flash.create.message', ['entidad' => $new])
			]);

			return $this->redirect
			($this->generateUrl ('editBackend',
				[
					'entity' => $entity,
					'element' => $new->getSlug()
				]));
		}

		return $this->render ('DestinyAppBundle:Backend:editCreate.html.twig',
			[
				'form' => $formulario->createView(),
				'entity' => $entity
			]);
	}

	/**
	 * @Route("/edit/{entity}/{element}", name="editBackend")
	 *
	 */
	public function editBackendAction (Request $request, $entity, $element)
	{
		$em = $this->getDoctrine()->getManager ();


		$editRepository = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity));

		$edit = (method_exists ($editRepository, 'getOne'))
			? $editRepository->getOne($element)
			: $editRepository->findOneBySlug($element);


		$formulario = $this->createForm ($this->get('idiomas'), $edit);
		$formulario->add ('archivo', 'file', ['required' => FALSE]);
		$formulario->handleRequest($request);

		if (($formulario->isSubmitted()) && ($formulario->isValid())) {
			if (method_exists ($edit, 'upload')) $edit->upload();
			$em->persist ($edit);
			$em->flush ();


			$traductor = $this->get('translator');

			$this->get ('session')->getFlashBag()->set ('success', [
				'title' => $traductor->trans ('flash.edit.title'),
				'message' => $traductor->trans ('flash.edit.message', ['entidad' => $edit])
			]);
		}

		return $this->render ('DestinyAppBundle:Backend:editCreate.html.twig',
			[
				'form' => $formulario->createView(),
				'entity' => $entity
			]);
	}

	/**
	 * @Route("/delete/{entity}/{element}",name="deleteBackend")
	 */
	public function deleteBackendAction($entity, $element)
	{
		$em = $this->getDoctrine()->getManager();

		$deleteRepository = $em->getRepository('DestinyAppBundle:' . ucfirst ($entity));

		$delete = (method_exists ($deleteRepository, 'getOne'))
			? $deleteRepository->getOne($element)
			: $deleteRepository->findOneBySlug($element);

		

		if (NULL != $delete) {
			//@Todo Verificar si el elemento es borrable o no.
			$em->remove ($delete);
			$em->flush ();

			$fs = new Filesystem();

			//En el caso de que la emtidad tenga una imagen, la elimina.
			if ((method_exists ($delete, 'getWebPath')) and ($fs->exists ($delete->getWebPath ()))) {
				$fs->remove($delete->getWebPath ());
			}

			$traductor = $this->get('translator');

			$this->get ('session')->getFlashBag()->set ('success', [
				'title' => $traductor->trans('flash.delete.title'),
				'message' => $traductor->trans ('flash.delete.message', ['entidad' => $delete])
			]);


		}

		return $this->redirect ($this->generateUrl ('listBackend', [
			'entity' => $entity,
		]));

	}

	//@Todo Funcione de cambio de estado especial(Solo puede haber un elemento verdadero).
	/**
	 * @Route("/change-status/{entity}/{element}",name="changeStatusBackend")
	 */
	public function changeStatusBackendAction ($entity, $element)
	{
		$em = $this->getDoctrine ()->getManager ();

		$changeRepository = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity));

		$change = (method_exists ($changeRepository, 'getOne'))
			? $changeRepository->getOne ($element)
			: $changeRepository->findOneBySlug ($element);


		if (NULL != $change) {

			$status = ($change->getEstado () == TRUE) ? FALSE : TRUE;
			$change->setEstado ($status);
			$em->flush ();

			$traductor = $this->get ('translator');

			$this->get ('session')->getFlashBag ()->set ('success', [
				'title' => $traductor->trans ('flash.change.title'),
				'message' => $traductor->trans ('flash.change.message', ['entidad' => $change])
			]);


		}

		return $this->redirect ($this->generateUrl ('listBackend', [
			'entity' => $entity,
		]));

	}
}
