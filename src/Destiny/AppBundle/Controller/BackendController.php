<?php

namespace Destiny\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

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

		$listRepository = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity));

		$list = (method_exists ($listRepository, 'getAll'))
			? $listRepository->getAll ()
			: $listRepository->findAll ();

		return $this->render ('DestinyAppBundle:Backend:list.html.twig',
			[
				'list' => $list,
				'entity' => $entity,
				'listElements' => (method_exists ($this->get ($entity), 'listElements'))
					? $this->get ($entity)->listElements () : NULL
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

			if ($this->get ($entity)->isDeletable ($delete) === TRUE) {
				$em->remove ($delete);
				$em->flush ();

				$fs = new Filesystem();

				//En el caso de que la emtidad tenga una imagen, la elimina.
				if ((method_exists ($delete, 'getWebPath')) and ($fs->exists ($delete->getWebPath ()))) {
					$fs->remove ($delete->getWebPath ());
				}

				$traductor = $this->get ('translator');

				$this->get ('session')->getFlashBag ()->set ('success', [
					'title' => $traductor->trans ('flash.delete.title'),
					'message' => $traductor->trans ('flash.delete.message', ['entidad' => $delete])
				]);

			} else {
				//En caso de que el elemnto, no sea borrable, muestra un mensaje de alerta y te devuelve al listado
				$traductor = $this->get ('translator');

				$this->get ('session')->getFlashBag ()->set ('danger', [
					'title' => $traductor->trans ('flash.notdelete.title'),
					'message' => $traductor->trans ('flash.notdelete.message', ['entidad' => $delete])
				]);
			}


		}

		return $this->redirect ($this->generateUrl ('listBackend', [
			'entity' => $entity,
		]));

	}


	/**
	 * @Route("/change-status/{entity}/{element}/",name="changeStatusBackend")
	 */
	public function changeStatusBackendAction ($entity, $element)
	{
		$em = $this->getDoctrine ()->getManager ();

		$changeRepository = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity));

		$change = (method_exists ($changeRepository, 'getOne'))
			? $changeRepository->getOne ($element)
			: $changeRepository->findOneBySlug ($element);


		if ((NULL != $change) and ($this->get ($entity)->isChangeable ($change) == TRUE)) {

			$list = (method_exists ($changeRepository, 'getAll'))
				? $changeRepository->getAll ()
				: $changeRepository->findAll ();

			$status = ($change->getEstado () == TRUE) ? FALSE : TRUE;
			$change->setEstado ($status);
			$em->persist ($change);


			$em->flush ();

			$traductor = $this->get ('translator');

			$this->get ('session')->getFlashBag ()->set ('success', [
				'title' => $traductor->trans ('flash.change.title'),
				'message' => $traductor->trans ('flash.change.message', ['entidad' => $change])
			]);


		} else {
			//En caso de que el elemnto, no sea borrable, muestra un mensaje de alerta y te devuelve al listado
			$traductor = $this->get ('translator');

			$this->get ('session')->getFlashBag ()->set ('danger', [
				'title' => $traductor->trans ('flash.notChangable.title'),
				'message' => $traductor->trans ('flash.notChangable.message', ['entidad' => $change])
			]);
		}

		return $this->redirect ($this->generateUrl ('listBackend', [
			'entity' => $entity,
		]));

	}

	/**
	 * @Route("/change-default/{entity}/{element}",name="changeDefaultBackend")
	 */
	public function changeDefaultBackendAction ($entity, $element)
	{
		$em = $this->getDoctrine ()->getManager ();

		$changeRepository = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity));

		$change = (method_exists ($changeRepository, 'getOne'))
			? $changeRepository->getOne ($element)
			: $changeRepository->findOneBySlug ($element);


		if (NULL != $change) {

			$list = (method_exists ($changeRepository, 'getAll'))
				? $changeRepository->getAll ()
				: $changeRepository->findAll ();

			foreach ($list as $changeList) {
				$changeList->setDefecto (($changeList === $change) ? TRUE : FALSE);

				$em->persist ($changeList);
			}


			$em->flush ();

			$traductor = $this->get ('translator');

			$this->get ('session')->getFlashBag ()->set ('success', [
				'title' => $traductor->trans ('flash.status.title'),
				'message' => $traductor->trans ('flash.status.message', ['entidad' => $change])
			]);


		}

		return $this->redirect ($this->generateUrl ('listBackend', [
			'entity' => $entity,
		]));

	}
}
