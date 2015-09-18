<?php

namespace Chill\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Chill\MainBundle\Form\UserPasswordType;

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
            ;
        if ($options['is_creation']) {
            $builder->add('plainPassword', new UserPasswordType(), array(
                'mapped' => false
            ));
            
        } else {
            $builder->add($builder
                    ->create('enabled', 'choice', array(
                        'choices' => array(
                            0 => 'Disabled, the user is not allowed to login',
                            1  => 'Enabled, the user is active'
                        ),
                        'expanded' => false,
                        'multiple' => false
                        ))
                );
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Chill\MainBundle\Entity\User'
        ));
        
        $resolver
                ->setDefaults(array('is_creation' => false))
                ->addAllowedValues(array('is_creation' => array(true, false)))
                ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chill_mainbundle_user';
    }
}
