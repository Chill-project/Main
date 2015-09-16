<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Form\UserType;
use Chill\MainBundle\Entity\GroupCenter;

/**
 * User controller.
 *
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ChillMainBundle:User')->findAll();

        return $this->render('ChillMainBundle:User:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request)
    {
        $user = new User();
        $form = $this->createCreateForm($user);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_user_show', array('id' => $user->getId())));
        }

        return $this->render('ChillMainBundle:User:new.html.twig', array(
            'entity' => $user,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction()
    {
        $user = new User();
        $form   = $this->createCreateForm($user);

        return $this->render('ChillMainBundle:User:new.html.twig', array(
            'entity' => $user,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ChillMainBundle:User:show.html.twig', array(
            'entity'      => $user,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditForm($user);

        return $this->render('ChillMainBundle:User:edit.html.twig', array(
            'entity'      => $user,
            'edit_form'   => $editForm->createView(),
            'delete_groupcenter_form' => array_map( 
                    function(\Symfony\Component\Form\Form $form) {
                        return $form->createView();
                
                    },
                    iterator_to_array($this->getDeleteLinkGroupCenterByUser($user), true))
        ));
    }
    
    public function deleteLinkGroupCenterAction($uid, $gcid) 
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($uid);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        
        $groupCenter = $em->getRepository('ChillMainBundle:GroupCenter')
                ->find($gcid);
        
        if (!$groupCenter) {
            throw $this->createNotFoundException('Unable to find groupCenter entity');
        }
        
        try {
            $user->removeGroupCenter($groupCenter);
        } catch (\RuntimeException $ex) {
            $this->addFlash('error', $this->get('translator')->trans($ex-getMessage()));
            
            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $uid)));
        }
        
        $em->flush();
        
        $this->addFlash('notice', $this->get('translator')
                ->trans('The permissions where removed.'));
        
        return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $uid)));
        
        
        
        
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $user The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $user)
    {
        $form = $this->createForm(new UserType(), $user, array(
            'action' => $this->generateUrl('admin_user_update', array('id' => $user->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($user);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:User:edit.html.twig', array(
            'entity'      => $user,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('ChillMainBundle:User')->find($id);

            if (!$user) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($user);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_user'));
    }

    /**
     * Creates a form to delete a User entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * Creates a form to delete a link to a GroupCenter
     *
     * @param mixed $permissionsGroup The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteLinkGroupCenterForm(User $user, GroupCenter $groupCenter)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_user_delete_group_center', 
                    array('uid' => $user->getId(), 'gcid' => $groupCenter->getId())))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * 
     * @param User $user
     */
    private function getDeleteLinkGroupCenterByUser(User $user)
    {
        foreach ($user->getGroupCenters() as $groupCenter) {
            yield $groupCenter->getId() => $this
                    ->createDeleteLinkGroupCenterForm($user, $groupCenter);
        }
    }
}
