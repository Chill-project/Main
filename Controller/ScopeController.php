<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Chill\MainBundle\Entity\Scope;
use Chill\MainBundle\Form\ScopeType;

/**
 * Scope controller.
 *
 */
class ScopeController extends Controller
{

    /**
     * Lists all Scope entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ChillMainBundle:Scope')->findAll();

        return $this->render('ChillMainBundle:Scope:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Scope entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Scope();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_scope_show', array('id' => $entity->getId())));
        }

        return $this->render('ChillMainBundle:Scope:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Scope entity.
     *
     * @param Scope $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Scope $entity)
    {
        $form = $this->createForm(new ScopeType(), $entity, array(
            'action' => $this->generateUrl('admin_scope_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Scope entity.
     *
     */
    public function newAction()
    {
        $entity = new Scope();
        $form   = $this->createCreateForm($entity);

        return $this->render('ChillMainBundle:Scope:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Scope entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChillMainBundle:Scope')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Scope entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChillMainBundle:Scope:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Scope entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChillMainBundle:Scope')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Scope entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChillMainBundle:Scope:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Scope entity.
    *
    * @param Scope $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Scope $entity)
    {
        $form = $this->createForm(new ScopeType(), $entity, array(
            'action' => $this->generateUrl('admin_scope_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Scope entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ChillMainBundle:Scope')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Scope entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_scope_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:Scope:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Scope entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ChillMainBundle:Scope')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Scope entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_scope'));
    }

    /**
     * Creates a form to delete a Scope entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_scope_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
