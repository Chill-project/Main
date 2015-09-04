<?php

namespace Chill\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PermissionsGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('roleScopes', 'collection', array(
               'type' => 'composed_role_scope',
               'allow_add' => true
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chill\MainBundle\Entity\PermissionsGroup'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chill_mainbundle_permissionsgroup';
    }
}
