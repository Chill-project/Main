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
        $scope = new Scope();
        $form = $this->createCreateForm($scope);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($scope);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_scope_show', array('id' => $scope->getId())));
        }

        return $this->render('ChillMainBundle:Scope:new.html.twig', array(
            'entity' => $scope,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Scope entity.
     *
     * @param Scope $scope The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Scope $scope)
    {
        $form = $this->createForm(new ScopeType(), $scope, array(
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
        $scope = new Scope();
        $form   = $this->createCreateForm($scope);

        return $this->render('ChillMainBundle:Scope:new.html.twig', array(
            'entity' => $scope,
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

        $scope = $em->getRepository('ChillMainBundle:Scope')->find($id);

        if (!$scope) {
            throw $this->createNotFoundException('Unable to find Scope entity.');
        }

        return $this->render('ChillMainBundle:Scope:show.html.twig', array(
            'entity'      => $scope
        ));
    }

    /**
     * Displays a form to edit an existing Scope entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $scope = $em->getRepository('ChillMainBundle:Scope')->find($id);

        if (!$scope) {
            throw $this->createNotFoundException('Unable to find Scope entity.');
        }

        $editForm = $this->createEditForm($scope);

        return $this->render('ChillMainBundle:Scope:edit.html.twig', array(
            'entity'      => $scope,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Scope entity.
    *
    * @param Scope $scope The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Scope $scope)
    {
        $form = $this->createForm(new ScopeType(), $scope, array(
            'action' => $this->generateUrl('admin_scope_update', array('id' => $scope->getId())),
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

        $scope = $em->getRepository('ChillMainBundle:Scope')->find($id);

        if (!$scope) {
            throw $this->createNotFoundException('Unable to find Scope entity.');
        }

        $editForm = $this->createEditForm($scope);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_scope_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:Scope:edit.html.twig', array(
            'entity'      => $scope,
            'edit_form'   => $editForm->createView()
        ));
    }
}
