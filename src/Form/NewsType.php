<?php

namespace App\Form;

use App\Entity\News;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'عنوان الخبر',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'أدخل عنوان الخبر'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'محتوى الخبر',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 8,
                    'placeholder' => 'أدخل محتوى الخبر'
                ]
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'صورة الخبر',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'يرجى رفع صورة بصيغة صحيحة: JPG, PNG, WEBP',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => News::class,
        ]);
    }
}
