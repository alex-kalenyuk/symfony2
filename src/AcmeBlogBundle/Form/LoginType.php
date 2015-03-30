<?php

namespace AcmeBlogBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends UserType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'email')
            ->add('password', 'password')
            ->add('submit', 'submit', ['label' => 'Login'])
        ;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acmeblogbundle_login';
    }
}
