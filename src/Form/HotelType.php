<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Hotel;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class HotelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category',EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'title',
            ])
            ->add('title')
            ->add('keywords')
            ->add('description')
            ->add('image', FileType::class,[
                'label'=>'Hotel Gellery Image',
                'mapped'=>false,
                'required'=>false,
                'constraints'=>[
                    new File([
                        'maxSize'=>'4096k',
                        'mimeTypes'=>[
                            'image/*',
                        ],
                        'mimeTypesMessage'=>'Please upload a valid Image File',
                    ])
                ],
            ])

            ->add('star',ChoiceType::class,[
                'choices'=>[
                    '1 Star'=>'1',
                    '2 Star'=>'2',
                    '3 Star'=>'3',
                    '4 Star'=>'4',
                    '5 Star'=>'5,',
                ],
            ])
            ->add('address')
            ->add('phone')
            ->add('fax')
            ->add('email')
            ->add('detail',CKEditorType::class, array(
                'config'=>array(
                    'uiColor'=>'#ffffff',
                    //...
                ),
            ))
            ->add('city',ChoiceType::class,[
                'choices'=>[
                    'Ankara'=>'Ankara',
                    'Bursa'=>'Bursa',
                    'Antalya'=>'Antalya',
                    'Muğla'=>'Muğla'],
            ])
            ->add('country',ChoiceType::class,[
                'choices'=>[
                    'Türkiye'=>'Türkiye',
                    'İspanya'=>'İspanya'],

            ])
            ->add('location')
            ->add('status', ChoiceType::class,[
                'choices'=>[
                    'True'=>'True',
                    'False'=>'False'],

            ])
        ;



    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hotel::class,
        ]);
    }
}
