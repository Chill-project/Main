<?php

namespace Chill\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            /*->add('password', 'repeated', array(
                'type' => 'password',
                'required' => false,
                'options' => array(),
                'first_options' => array(
                    'label' => 'Password'
                ), 
                'second_options' => array(
                    'label' => 'Repeat the password'
                )
            ))*/
            ->add('enabled')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chill\MainBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chill_mainbundle_user';
    }
}
