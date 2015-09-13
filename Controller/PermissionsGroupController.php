<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Chill\MainBundle\Entity\RoleScope;
use Chill\MainBundle\Entity\PermissionsGroup;
use Chill\MainBundle\Form\PermissionsGroupType;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Chill\MainBundle\Entity\Scope;

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
        $permissionsGroup = new PermissionsGroup();
        $form = $this->createCreateForm($permissionsGroup);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($permissionsGroup);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_permissionsgroup_edit', 
                    array('id' => $permissionsGroup->getId())));
        }

        return $this->render('ChillMainBundle:PermissionsGroup:new.html.twig', array(
            'entity' => $permissionsGroup,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a PermissionsGroup entity.
     *
     * @param PermissionsGroup $permissionsGroup The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PermissionsGroup $permissionsGroup)
    {
        $form = $this->createForm(new PermissionsGroupType(), $permissionsGroup, array(
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
        $permissionsGroup = new PermissionsGroup();
        $form   = $this->createCreateForm($permissionsGroup);

        return $this->render('ChillMainBundle:PermissionsGroup:new.html.twig', array(
            'entity' => $permissionsGroup,
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

        $permissionsGroup = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$permissionsGroup) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }
        
        $translatableStringHelper = $this->get('chill.main.helper.translatable_string');
        $roleScopes = $permissionsGroup->getRoleScopes()->toArray();
        usort($roleScopes,
              function(RoleScope $a, RoleScope $b) use ($translatableStringHelper) {
                  return strcmp(
                        $translatableStringHelper->localize($a->getScope()->getName()),
                        $translatableStringHelper->localize($b->getScope()->getName())
                        );
              });
              
        $expandedRoles = array();
        foreach ($roleScopes as $roleScope) {
            if (!array_key_exists($roleScope->getRole(), $expandedRoles)) {
                $expandedRoles[$roleScope->getRole()] = 
                      array_map(
                            function(RoleInterface $role) {
                          
                                return $role->getRole();
                            },
                            $this->get('security.role_hierarchy')
                                ->getReachableRoles(
                                      array(new Role($roleScope->getRole()))
                                    )
                        );
            }
        }

        return $this->render('ChillMainBundle:PermissionsGroup:show.html.twig', array(
            'entity'      => $permissionsGroup,
            'role_scopes' => $roleScopes,
            'expanded_roles' => $expandedRoles
        ));
    }

    /**
     * Displays a form to edit an existing PermissionsGroup entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $permissionsGroup = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$permissionsGroup) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }

        $editForm = $this->createEditForm($permissionsGroup);
        
        $deleteRoleScopesForm = array();
        foreach ($permissionsGroup->getRoleScopes() as $roleScope) {
            $deleteRoleScopesForm[$roleScope->getId()] = $this->createDeleteRoleScopeForm(
                    $permissionsGroup, $roleScope);
        }
        
        $addRoleScopesForm = $this->createAddRoleScopeForm($permissionsGroup);

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $permissionsGroup,
            'edit_form'   => $editForm->createView(),
            'delete_role_scopes_form' => array_map( function($form) { 
                
                return $form->createView(); 
            }, $deleteRoleScopesForm),
            'add_role_scopes_form' => $addRoleScopesForm->createView()
        ));
    }

    /**
    * Creates a form to edit a PermissionsGroup entity.
    *
    * @param PermissionsGroup $permissionsGroup The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PermissionsGroup $permissionsGroup)
    {
        $form = $this->createForm(new PermissionsGroupType(), $permissionsGroup, array(
            'action' => $this->generateUrl('admin_permissionsgroup_update', array('id' => $permissionsGroup->getId())),
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

        $permissionsGroup = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$permissionsGroup) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }

        $editForm = $this->createEditForm($permissionsGroup);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            
            $em->flush();

            return $this->redirect($this->generateUrl('admin_permissionsgroup_edit', array('id' => $id)));
        }
        
        $deleteRoleScopesForm = array();
        foreach ($permissionsGroup->getRoleScopes() as $roleScope) {
            $deleteRoleScopesForm[$roleScope->getId()] = $this->createDeleteRoleScopeForm(
                    $permissionsGroup, $roleScope);
        }
        
        $addRoleScopesForm = $this->createAddRoleScopeForm($permissionsGroup);

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $permissionsGroup,
            'edit_form'   => $editForm->createView(),
            'delete_role_scopes_form' => array_map( function($form) { 
                
                return $form->createView(); 
            }, $deleteRoleScopesForm),
            'add_role_scopes_form' => $addRoleScopesForm->createView()
        ));
    }
    
    /**
     * get a role scope by his parameters. The role scope is persisted if it 
     * doesn't exists in database.
     * 
     * @param Scope $scope
     * @param string $role
     * @return RoleScope
     */
    protected function getPersistentRoleScopeBy(Scope $scope, $role) 
    {
        $em = $this->getDoctrine()->getManager();
        
        $roleScope = $em->getRepository('ChillMainBundle:RoleScope')
                ->findOneBy(array('role' => $role, 'scope' => $scope));
        
        if ($roleScope === NULL) {
            $roleScope = (new RoleScope())
                    ->setRole($role)
                    ->setScope($scope)
                    ;
            
            $em->persist($roleScope);
        }
        
        return $roleScope;
    }
    
    /**
     * remove an association between permissionsGroup and roleScope
     * 
     * @param int $pgid permissionsGroup id
     * @param int $rsid roleScope id
     * @return redirection to edit form
     */
    public function deleteLinkRoleScopeAction($pgid, $rsid) 
    {
        $em = $this->getDoctrine()->getManager();
        
        $permissionsGroup = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($pgid);
        $roleScope = $em->getRepository('ChillMainBundle:RoleScope')->find($rsid);

        if (!$permissionsGroup) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }
        
        if (!$roleScope) {
            throw $this->createNotFoundException('Unable to find RoleScope entity');
        }
        
        try {
            $permissionsGroup->removeRoleScope($roleScope);
        } catch (\RuntimeException $ex) {
            $this->addFlash('notice', 
                $this->get('translator')->trans("The role '%role%' and circle "
                        . "'%scope%' is not associated with this permission group", array(
                            '%role%' => $this->get('translator')->trans($roleScope->getRole()),
                            '%scope%' => $this->get('chill.main.helper.translatable_string')
                                ->localize($roleScope->getScope()->getName())
                        )));
            
            return $this->redirect($this->generateUrl('admin_permissionsgroup_edit', 
                array('id' => $pgid)));
        }
        
        $em->flush();
        
        $this->addFlash('notice', 
                $this->get('translator')->trans("The role '%role%' on circle "
                        . "'%scope%' has been removed", array(
                            '%role%' => $this->get('translator')->trans($roleScope->getRole()),
                            '%scope%' => $this->get('chill.main.helper.translatable_string')
                                ->localize($roleScope->getScope()->getName())
                        )));
        
        return $this->redirect($this->generateUrl('admin_permissionsgroup_edit', 
                array('id' => $pgid)));
    }
    
    /**
     * 
     * @param Request $request
     * @param int $id
     * @return Respon
     * @throws type
     */
    public function addLinkRoleScopeAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $permissionsGroup = $em->getRepository('ChillMainBundle:PermissionsGroup')->find($id);

        if (!$permissionsGroup) {
            throw $this->createNotFoundException('Unable to find PermissionsGroup entity.');
        }
        
        $form = $this->createAddRoleScopeForm($permissionsGroup);
        $form->handleRequest($request);
        
        if ($form->isValid()) {
            $roleScope = $this->getPersistentRoleScopeBy(
                    $form['composed_role_scope']->getData()->getScope(), 
                    $form['composed_role_scope']->getData()->getRole()
                    );
            
            $permissionsGroup->addRoleScope($roleScope);
            $violations = $this->get('validator')->validate($permissionsGroup);
            
            if ($violations->count() === 0) {
                $em->flush();
            
                $this->addFlash('notice', 
                    $this->get('translator')->trans("The role '%role%' on circle "
                            . "'%scope%' has been added", array(
                                '%role%' => $this->get('translator')->trans($roleScope->getRole()),
                                '%scope%' => $this->get('chill.main.helper.translatable_string')
                                    ->localize($roleScope->getScope()->getName())
                            )));
            
                return $this->redirect($this->generateUrl('admin_permissionsgroup_edit', 
                    array('id' => $id)));
            } else {
                foreach($violations as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }

        }
        
        $editForm = $this->createEditForm($permissionsGroup);
        
        $deleteRoleScopesForm = array();
        foreach ($permissionsGroup->getRoleScopes() as $roleScope) {
            $deleteRoleScopesForm[$roleScope->getId()] = $this->createDeleteRoleScopeForm(
                    $permissionsGroup, $roleScope);
        }
        
        $addRoleScopesForm = $this->createAddRoleScopeForm($permissionsGroup);

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $permissionsGroup,
            'edit_form'   => $editForm->createView(),
            'delete_role_scopes_form' => array_map( function($form) { 
                
                return $form->createView(); 
            }, $deleteRoleScopesForm),
            'add_role_scopes_form' => $addRoleScopesForm->createView()
        ));
        
    }
    
    /**
     * Creates a form to delete a link to roleScope.
     *
     * @param mixed $permissionsGroup The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteRoleScopeForm(PermissionsGroup $permissionsGroup,
            RoleScope $roleScope)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_permissionsgroup_delete_role_scope', 
                    array('pgid' => $permissionsGroup->getId(), 'rsid' => $roleScope->getId())))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * creates a form to add a role scope to permissionsgroup
     * 
     * @param PermissionsGroup $permissionsGroup
     * @return \Symfony\Component\Form\Form The form
     */
    private function createAddRoleScopeForm(PermissionsGroup $permissionsGroup)
    {
        return $this->createFormBuilder()
                ->setAction($this->generateUrl('admin_permissionsgroup_add_role_scope',
                        array('id' => $permissionsGroup->getId())))
                ->setMethod('PUT')
                ->add('composed_role_scope', 'composed_role_scope')
                ->add('submit', 'submit', array('label' => 'Add permission'))
                ->getForm()
                ;
    }
    
    
}
