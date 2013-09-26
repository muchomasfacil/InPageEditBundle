<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class GSMStringType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('string', 'text', array(
                'label' => 'string.content',
                'constraints' => new NotBlank(),
            ))
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
        return 'gsm_string';
    }

}
