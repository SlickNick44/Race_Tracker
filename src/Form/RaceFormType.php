<?php

namespace App\Form;

use App\Entity\Race;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RaceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raceName', TextType::class, [
                'attr' => array(
                    'class' => 'bg-transparent block border-b-2 w-full h-20 text-6xl outline-none',
                    'placeholder' => 'Enter name of race',
                    'autocomplete' => 'off'
                ),

                'label' => false
            ])

            ->add('date', TextType::class, [
                'attr' => array(
                    'class' => 'bg-transparent block border-b-2 w-full h-20 text-6xl outline-none',
                    'placeholder' => 'Enter date of race',
                    'autocomplete' => 'off'
                ),

                'label' => false
            ])

            ->add('filePath', FileType::class, [
                'attr' => array(
                    'class' => 'py-10',
                    'placeholder' => 'Upload your csv file here'
                ),
                'required' => false,
                'mapped' => false,
                'label' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Race::class,
        ]);
    }
}
