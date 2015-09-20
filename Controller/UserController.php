<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Chill\MainBundle\Entity\User;
use Chill\MainBundle\Form\UserType;
use Chill\MainBundle\Entity\GroupCenter;
use Chill\MainBundle\Form\Type\ComposedGroupCenterType;
use Chill\MainBundle\Form\UserPasswordType;

/**
 * User controller.
 *
 */
class UserController extends Controller
{
    
    const FORM_GROUP_CENTER_COMPOSED = 'composed_groupcenter';

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
            
            $user->setPassword($this->get('security.password_encoder')
                    ->encodePassword($user, $form['plainPassword']['password']->getData()));
            
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
     * Creates a form to create a User entity.
     *
     * @param User $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('admin_user_create'),
            'method' => 'POST',
            'is_creation' => true
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
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

        return $this->render('ChillMainBundle:User:show.html.twig', array(
            'entity'      => $user,
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
            'add_groupcenter_form' => $this->createAddLinkGroupCenterForm($user)->createView(),
            'delete_groupcenter_form' => array_map( 
                    function(\Symfony\Component\Form\Form $form) {
                        return $form->createView();
                
                    },
                    iterator_to_array($this->getDeleteLinkGroupCenterByUser($user), true))
        ));
    }
    
    /**
     * Displays a form to edit the user password.
     *
     */
    public function editPasswordAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditPasswordForm($user);

        return $this->render('ChillMainBundle:User:edit_password.html.twig', array(
            'entity'      => $user,
            'edit_form'   => $editForm->createView()
        ));
    }
    
    /**
     * 
     * 
     * @param User $user
     * @return \Symfony\Component\Form\Form
     */
    private function createEditPasswordForm(User $user)
    {
        return $this->createForm(new UserPasswordType(), $user, array(
            'action' => 
                $this->generateUrl('admin_user_update_password', array('id' => $user->getId())),
            'method' => 'PUT'
            ))
                ->add('submit', 'submit', array('label' => 'Change password'))
                ;
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
        
        $this->addFlash('success', $this->get('translator')
                ->trans('The permissions where removed.'));
        
        return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $uid)));
        
    }
    
    public function addLinkGroupCenterAction(Request $request, $uid) 
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($uid);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        
        $form = $this->createAddLinkGroupCenterForm($user);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $groupCenter = $this->getPersistedGroupCenter(
                    $form[self::FORM_GROUP_CENTER_COMPOSED]->getData());
            $user->addGroupCenter($groupCenter);
            
            if ($this->get('validator')->validate($user)->count() === 0) {
                $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('The '
                    . 'permissions have been successfully added to the user'));
            
            return $this->redirect($this->generateUrl('admin_user_edit',
                    array('id' => $uid)));
            } else {
                foreach($this->get('validator')->validate($user) as $error)
                $this->addFlash('error', $error->getMessage());
            }
        }
        
        return $this->render('ChillMainBundle:User:edit.html.twig', array(
            'entity'      => $user,
            'edit_form'   => $this->createEditForm($user)->createView(),
            'add_groupcenter_form' => $this->createAddLinkGroupCenterForm($user)->createView(),
            'delete_groupcenter_form' => array_map( 
                    function(\Symfony\Component\Form\Form $form) {
                        return $form->createView();
                
                    },
                    iterator_to_array($this->getDeleteLinkGroupCenterByUser($user), true))
        ));
    }
    
    private function getPersistedGroupCenter(GroupCenter $groupCenter)
    {
        $em = $this->getDoctrine()->getManager();
        
        $groupCenterManaged = $em->getRepository('ChillMainBundle:GroupCenter')
                ->findOneBy(array(
                    'center' => $groupCenter->getCenter(),
                    'permissionsGroup' => $groupCenter->getPermissionsGroup()
                ));
        
        if (!$groupCenterManaged) {
            $em->persist($groupCenter);
            return $groupCenter;
        }
        
        return $groupCenterManaged;
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

        $editForm = $this->createEditForm($user);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:User:edit.html.twig', array(
            'entity'      => $user,
            'edit_form'   => $editForm->createView(),
            'add_groupcenter_form' => $this->createAddLinkGroupCenterForm($user)->createView(),
            'delete_groupcenter_form' => array_map( 
                    function(\Symfony\Component\Form\Form $form) {
                        return $form->createView();
                
                    },
                    iterator_to_array($this->getDeleteLinkGroupCenterByUser($user), true))
        ));
    }
    
    /**
     * Edits the user password
     *
     */
    public function updatePasswordAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('ChillMainBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $editForm = $this->createEditPasswordForm($user);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $password = $editForm->getData();
            
            $user->setPassword($this->get('security.password_encoder')
                    ->encodePassword($user, $password));
            
            $em->flush();
            
            $this->addFlash('success', $this->get('translator')->trans('Password successfully updated!'));

            return $this->redirect($this->generateUrl('admin_user_edit', array('id' => $id)));
        }

        return $this->render('ChillMainBundle:User:edit_password.html.twig', array(
            'entity'      => $user,
            'edit_form'   => $editForm->createView(),
        ));
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
     * create a form to add a link to a groupcenter
     * 
     * @param User $user
     * @return \Symfony\Component\Form\Form
     */
    private function createAddLinkGroupCenterForm(User $user)
    {
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('admin_user_add_group_center', 
                        array('uid' => $user->getId())))
                ->setMethod('POST')
                ->add(self::FORM_GROUP_CENTER_COMPOSED, new ComposedGroupCenterType())
                ->add('submit', 'submit', array('label' => 'Add a new groupCenter'))
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
