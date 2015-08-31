<?php

namespace Chill\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Chill\MainBundle\Entity\RoleScope;
use Chill\MainBundle\Entity\PermissionsGroup;
use Chill\MainBundle\Form\PermissionsGroupType;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleInterface;

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

            return $this->redirect($this->generateUrl('admin_permissionsgroup_show', array('id' => $permissionsGroup->getId())));
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

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $permissionsGroup,
            'edit_form'   => $editForm->createView(),
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

        return $this->render('ChillMainBundle:PermissionsGroup:edit.html.twig', array(
            'entity'      => $permissionsGroup,
            'edit_form'   => $editForm->createView(),
        ));
    }
}
