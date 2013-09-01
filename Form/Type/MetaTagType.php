<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MetaTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('string') //me falta el label
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MuchoMasFacil\InPageEditBundle\Entity\GroupedSortedMappedString'
        ));
    }

    public function getName()
    {
        return 'meta_tag';
    }

}
