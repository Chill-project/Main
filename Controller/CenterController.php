<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Chill\MainBundle\Entity\Center;
use Chill\MainBundle\Form\CenterType;

/**
 * Center controller.
 *
 */
class CenterController extends Controller
{

    /**
     * Lists all Center entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ChillMainBundle:Center')->findAll();

        return $this->render('ChillMainBundle:Center:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Center entity.
     *
     */
    public function createAction(Request $request)
    {
        $center = new Center();
        $form = $this->createCreateForm($center);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($center);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_center_show', array('id' => $center->getId())));
        }

        return $this->render('ChillMainBundle:Center:new.html.twig', array(
            'entity' => $center,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Center entity.
     *
     * @param Center $center The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Center $center)
    {
        $form = $this->createForm(new CenterType(), $center, array(
            'action' => $this->generateUrl('admin_center_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Center entity.
     *
     */
    public function newAction()
    {
        $center = new Center();
        $form   = $this->createCreateForm($center);

        return $this->render('ChillMainBundle:Center:new.html.twig', array(
            'entity' => $center,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Center entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $center = $em->getRepository('ChillMainBundle:Center')->find($id);

        if (!$center) {
            throw $this->createNotFoundException('Unable to find Center entity.');
        }

        return $this->render('ChillMainBundle:Center:show.html.twig', array(
            'entity'      => $center
        ));
    }

    /**
     * Displays a form to edit an existing Center entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $center = $em->getRepository('ChillMainBundle:Center')->find($id);

        if (!$center) {
            throw $this->createNotFoundException('Unable to find Center entity.');
        }

        $editForm = $this->createEditForm($center);
        return $this->render('ChillMainBundle:Center:edit.html.twig', array(
            'entity'      => $center,
            'edit_form'   => $editForm->createView()
            ));
    }

    /**
    * Creates a form to edit a Center entity.
    *
    * @param Center $center The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Center $center)
    {
        $form = $this->createForm(new CenterType(), $center, array(
            'action' => $this->generateUrl('admin_center_update', array('id' => $center->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Center entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $center = $em->getRepository('ChillMainBundle:Center')->find($id);

        if (!$center) {
            throw $this->createNotFoundException('Unable to find Center entity.');
        }

        $editForm = $this->createEditForm($center);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_center_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:Center:edit.html.twig', array(
            'entity'      => $center,
            'edit_form'   => $editForm->createView()
        ));
    }
}
