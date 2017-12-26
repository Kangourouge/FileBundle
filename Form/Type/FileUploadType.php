<?php

namespace KRG\FileBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use KRG\FileBundle\Entity\FileInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use KRG\FileBundle\Form\DataTransformer\FileDataTransformer;

class FileUploadType extends AbstractType {

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * FileUploadType constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('path', 'file', array(
                    'data_class' => null,
                    'required' => false,
                    'multiple' => false,
                    'label' => false,
                    'attr' => array(
                        'accept' => $options['accept']
                    )
                ))
                ->add('remove', CheckboxType::class, array(
                    'required' => false,
                    'mapped' => false
                ));

        if ($options['edit_name']) {
            $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
                if ($event->getData() === null) {
                    return;
                }
                $event->getForm()->add('name', TextType::class, array(
                    'required' => false
                ));
            });
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event){
            $data = $event->getData();

            /* @var $file FileInterface */
            $file = $event->getForm()->getData();
            if ($file !== null && !isset($data['remove']) && $data['path'] === null) {
                $data['path'] = $file->getPath();
            }

            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
            $form = $event->getForm();

            /* @var $file FileInterface */
            $file = $event->getData();

            if ($form->get('remove')->getData()) {
                $event->setData(null);
            } else if ($file->getId() && $file->getPath() instanceof UploadedFile) {
                $classMetadata = $this->entityManager->getClassMetadata(FileInterface::class);
                $this->entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($classMetadata, $file);
            }
        });

        $builder->addModelTransformer(new FileDataTransformer());
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_merge($view->vars, array(
            'preview_placeholder' => $options['preview_placeholder'],
            'upload_icon' => $options['upload_icon'],
            'edit_icon' => $options['edit_icon'],
            'remove_icon' => $options['remove_icon']
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => FileInterface::class,
            'edit_name' => true,
            'multiple' => false,
            'accept' => '',
            'max_size' => 100000,
            'error_bubbling' => false,
            'preview_placeholder' => '<i class="glyphicon glyphicon-picture"></i>',
            'upload_icon' => '<i class="glyphicon glyphicon-upload"></i> Browse ...',
            'edit_icon' => '<i class="glyphicon glyphicon-edit"></i>',
            'remove_icon' => '<i class="glyphicon glyphicon-trash"></i>'
        ));
    }

    public function getName() {
        return 'file_upload';
    }

}
