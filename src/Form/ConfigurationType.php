<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('folder', TextType::class, [
                'required' => true,
                'label' => 'form.config.label.folder',
                'help' => '(Ex: /Users/devcut/Sites)'
            ])
            ->add('exception', TextType::class, [
                'required' => false,
                'label' => 'form.config.label.exception'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
