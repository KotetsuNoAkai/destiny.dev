<?php

namespace Destiny\AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
/**
 * Class BackendController
 * @package Destiny\AppBundle\Controller
 * @Route("backend")
 */
class BackendController extends Controller
{



	protected function listElements ($entity)
	{
		$em = $this->getDoctrine ()->getManager ();

		if (method_exists ($this->get ($entity), 'groups')) {
			$groups = $this->get ($entity)->groups ();
			foreach ($groups as $group) {
				$list[ $group ] = $this->getElements ($entity, NULL, 'group', $group);
			}

		} else {
			$list = $this->getElements ($entity);
		}

		return $list;
	}

	protected function getElements ($entity, $element = NULL, $type = 'list', $group = NULL)
	{
		$em = $this->getDoctrine ()->getManager ();

		$repository = $em->getRepository ('DestinyAppBundle:' . ucfirst ($entity));


		switch ($type) {
			case ($type === 'list'):
				return (method_exists ($repository, 'getAll'))
					? $repository->getAll ()
					: $repository->findAll ();
				break;

			case ($type === 'one'):
				return (method_exists ($repository, 'getOne'))
					? $repository->getOne ($element)
					: $repository->findOneBySlug ($element);
				break;

			case ($type === 'group'):
				return $repository->getAllByGroup ($group);
				break;
		}


	}

	/**
	 * @Route("/",name="portadaBackend")
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

		return $this->render ('DestinyAppBundle:Backend:list.html.twig',
			[
				'entity' => $entity,
				'list' => $this->listElements($entity),
				'group' => (method_exists($this->get($entity),'groups')) ? $this->get($entity)->groups() : false,
				'listElements' => (method_exists ($this->get ($entity), 'listElements'))
					? $this->get ($entity)->listElements () : NULL,
				'cantCreate' => (method_exists($this->get ($entity), 'cantCreate'))
					? True : false,
			]);
	}

	/**
	 * @Route("/create/{entity}/", name="createBackend")
	 *
	 */
	public function createBackendAction (Request $request, $entity)
	{
		$em = $this->getDoctrine ()->getManager ();

		if (!(method_exists($this->get ($entity), 'cantCreate')))
		{

			$new = $this->get (strtolower ($entity))->newEntity ();

			$formulario = $this->createForm ($this->get (strtolower ($entity)), $new);

			$formulario->handleRequest ($request);

			if (($formulario->isSubmitted ()) && ($formulario->isValid ())) {
				if (method_exists ($new, 'upload')) $new->upload ();
				if (method_exists ($this->get ($entity), 'postCreate')) $this->get ($entity)->postCreate ($new);
				$em->persist ($new);
				$em->flush ();

				$traductor = $this->get('translator');

				$this->get ('session')->getFlashBag()->set ('success', [
					'title' => $traductor->trans ('flash.edit.title'),
					'message' => $traductor->trans ('flash.edit.message', ['entidad' => $new])
				]);
			}

			return $this->render ('DestinyAppBundle:Backend:editCreate.html.twig',
				[
					'form' => $formulario->createView (),
					'entity' => $entity,
					'list' => $this->listElements($entity),
					'group' => (method_exists($this->get($entity),'groups')) ? true : false,
					'listElements' => (method_exists ($this->get ($entity), 'listElements'))
						? $this->get ($entity)->listElements () : NULL,
					'cantCreate' => (!(method_exists($this->get ($entity), 'cantCreate'))) ? true : false,
					'notList' => (property_exists($this->get ($entity), 'notList'))
						? True : false
				]);

		} else{

			$traductor = $this->get('translator');

			$this->get ('session')->getFlashBag()->set ('danger', [
				'title' => $traductor->trans ('flash.cantcreate.title'),
				'message' => $traductor->trans ('flash.cantcreate.message')
			]);

			return $this->redirect($request->headers->get('referer'));
		}

	}

	/**
	 * @Route("/edit/{entity}/{element}", name="editBackend")
	 *
	 */
	public function editBackendAction (Request $request, $entity, $element)
	{
		$em = $this->getDoctrine()->getManager ();

		$edit = $this->getElements($entity,$element,'one');

		$formulario = $this->createForm ($this->get($entity), $edit);

		if (method_exists ($edit, 'upload'))
		{
			$formulario->add ('archivo', 'file', ['required' => FALSE]);
		}
		if (method_exists ($this->get($entity), 'preEdit')) $this->get($entity)->preEdit($edit);

		$formulario->handleRequest($request);

		if (($formulario->isSubmitted ()) && ($formulario->isValid ())) {
			if (method_exists ($edit, 'upload')) $edit->upload();
			if (method_exists ($this->get ($entity), 'postEdit')) $this->get ($entity)->postEdit ($edit);
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
				'form'   => $formulario->createView(),
				'entity' => $entity,
				'list' => $this->listElements($entity),
				'group' => (method_exists($this->get($entity),'groups')) ? true : false,
				'listElements' => (method_exists ($this->get ($entity), 'listElements'))
								   ? $this->get ($entity)->listElements () : NULL,
				'cantCreate' => (method_exists($this->get ($entity), 'cantCreate'))
					? True : false,
				'notList' => (property_exists($this->get ($entity), 'notList'))
					? True : false
			]);
	}

	/**
	 * @Route("/delete/{entity}/{element}",name="deleteBackend")
	 */
	public function deleteBackendAction(Request $request,$entity, $element)
	{
		$em = $this->getDoctrine()->getManager();

		$deleteRepository = $em->getRepository('DestinyAppBundle:' . ucfirst ($entity));

		$delete = (method_exists ($deleteRepository, 'getOne'))
			? $deleteRepository->getOne($element)
			: $deleteRepository->findOneBySlug($element);


		if (NULL != $delete) {

			if ($this->get ($entity)->isDeletable ($delete) === TRUE) {
				if (method_exists ($this->get ($entity), 'postDelete')) $this->get ($entity)->postDelete ($delete);
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




		return $this->redirect($request->headers->get('referer'));

	}


	/**
	 * @Route("/change-status/{entity}/{element}/",name="changeStatusBackend")
	 */
	public function changeStatusBackendAction (Request $request, $entity, $element)
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

		return $this->redirect($request->headers->get('referer'));

	}

	/**
	 * @Route("/change-default/{entity}/{element}",name="changeDefaultBackend")
	 */
	public function changeDefaultBackendAction (Request $request, $entity, $element)
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
				$changeList->setEstado(true);
				$em->persist ($changeList);
			}


			$em->flush ();

			$traductor = $this->get ('translator');

			$this->get ('session')->getFlashBag ()->set ('success', [
				'title' => $traductor->trans ('flash.status.title'),
				'message' => $traductor->trans ('flash.status.message', ['entidad' => $change])
			]);


		}

		return $this->redirect($request->headers->get('referer'));

	}
}
