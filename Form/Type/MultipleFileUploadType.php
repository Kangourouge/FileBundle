<?php

namespace KRG\FileBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultipleFileUploadType extends CollectionType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'entry_type'=> FileUploadType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_options' => array(
                'label' => false,
            )
        ));
    }

    public function getBlockPrefix()
    {
        return $this->getName();
    }


    public function getName()
    {
        return 'multiple_file_upload';
    }
}