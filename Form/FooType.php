<?php

namespace MuchoMasFacil\InPageEditBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FooType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('address')
            ->add('phone_number')
            ->add('company')
            ->add('email')
            ->add('url')
            ->add('datetime')
            ->add('date')
            ->add('time')
            ->add('text')
            ->add('path', 'file', array('required' => false, 'data_class' => null, 'mapped' => true))
            //->add('mimeType')
            //->add('size')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MuchoMasFacil\InPageEditBundle\Entity\Foo'
        ));
    }

    public function getName()
    {
        return 'muchomasfacil_inpageeditbundle_footype';
    }
}
