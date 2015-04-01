<?php

namespace TrainingBundle\Form;

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
            ->add('name')
            ->add('email', 'email');

        if (in_array('create' , $options['validation_groups'])) {
            $builder
                ->add('password', 'password')
                ->add('submit', 'submit', ['label' => 'Create User']);
        } elseif (in_array('update', $options['validation_groups'])) {
            $builder->add('submit', 'submit', ['label' => 'Update User']);
        }
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TrainingBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'trainingbundle_user';
    }
}
