<?php

namespace Chill\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer;

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
            ->add($builder
                    ->create('enabled', 'choice', array(
                        'choices' => array(
                            0 => 'Disabled, the user is not allowed to login',
                            1  => 'Enabled, the user is active'
                        ),
                        'expanded' => false,
                        'multiple' => false
                        ))
                )
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
