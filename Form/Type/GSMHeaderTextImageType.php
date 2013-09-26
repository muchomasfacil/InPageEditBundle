<?php

namespace MuchoMasFacil\InPageEditBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Validator\Constraints\NotBlank;

class GSMHeaderTextImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('string', 'text', array(
                'label' => 'label.header',
                'constraints' => new NotBlank(),
            ))
            ->add('text', 'textarea', array(
                'label' => 'label.text',
                'constraints' => new NotBlank(),
            ))
            ->add('image', 'string', array(
                'attr' => array('data-ef-input-connector' => 'images'),
                'label' => 'label.image',
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
        return 'gsm_header_text_image';
    }

}
