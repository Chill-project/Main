<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Chill\MainBundle\Entity\PermissionsGroup;
use Chill\MainBundle\Form\PermissionsGroupType;

/**
 * PermissionsGroup controller.
 *
 */
class PermissionsGroupController extends Controller
{

    /**
     * Lists all PermissionsGroup entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ChillMainBundle:PermissionsGroup')->findAll();

        return $this->render('ChillMainBundle:PermissionsGroup:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new PermissionsGroup entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new PermissionsGroup();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_permissionsgroup_show', array('id' => $entity->getId())));
        }

        return $this->render('ChillMainBundle:PermissionsGroup:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PermissionsGroup entity.
     *
     * @param PermissionsGroup $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PermissionsGroup $entity)
    {
        $form = $this->createForm(new PermissionsGroupType(), $entity, array(
            'action' => $this->generateUrl('admin_permissionsgroup_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new PermissionsGroup entity.
     *
     */
    public function newAction()
    {
        $entity = new PermissionsGroup();
        $form   = $this->createCreateForm($entity);

        return $this->render('ChillMainBundle:PermissionsGroup:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a PermissionsGroup entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }

        return $this->render('ChillMainBundle:PermissionsGroup:show.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing PermissionsGroup entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }

        $editForm = $this->createEditForm($entity);

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a PermissionsGroup entity.
    *
    * @param PermissionsGroup $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PermissionsGroup $entity)
    {
        $form = $this->createForm(new PermissionsGroupType(), $entity, array(
            'action' => $this->generateUrl('admin_permissionsgroup_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing PermissionsGroup entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }

        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_permissionsgroup_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }
}