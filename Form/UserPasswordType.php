<?php

namespace Chill\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserPasswordType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', 'repeated', array(
                'type' => 'password',
                'required' => false,
                'options' => array(),
                'first_options' => array(
                    'label' => 'Password'
                ), 
                'second_options' => array(
                    'label' => 'Repeat the password'
                ),
                'invalid_message' => "The password fields must match",
                'constraints' => array(
                    new Length(array(
                        'min' => 9, 
                        'minMessage' => 'The password must be greater than {{ limit }} characters'
                        )),
                    new NotBlank(),
                    new Regex(array(
                        'pattern' => "/((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%!,;:+\"'-\/{}~=µ\(\)£]).{6,})/",
                        'message' => "The password must contains one letter, one "
                        . "capitalized letter, one number and one special character "
                        . "as *[@#$%!,;:+\"'-/{}~=µ()£]). Other characters are allowed."
                        ))
                )
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_classds' => 'Chill\MainBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'chill_mainbundle_user_password';
    }
}
