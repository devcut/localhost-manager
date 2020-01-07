<?php

namespace App\Form;

use App\Service\LocalhostManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationType extends AbstractType
{
    private $lm;

    public function __construct(LocalhostManager $lm)
    {
        $this->lm = $lm;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('folder', TextType::class, [
                'required' => true,
                'label' => 'form.config.label.folder',
                'help' => '(Ex: /Users/devcut/Sites)',
                'attr' => [
                    'class' => 'js-configuration-folder'
                ]
            ])
            ->add('exception', ChoiceType::class, [
                'required' => false,
                'label' => 'form.config.label.exception',
                'choices' => $this->lm->getExceptionsFolder(),
                'multiple' => true,
                'attr' => [
                    'class' => 'js-configuration-exception'
                ]
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $exceptions = [];

            if (isset($data['exception'])) {
                foreach ($data['exception'] as $folder)
                {
                    $exceptions[$folder] = $folder;
                }
            }

            $form->add('exception', ChoiceType::class, [
                'required' => false,
                'label' => 'form.config.label.exception',
                'choices' => $exceptions,
                'multiple' => true,
                'attr' => [
                    'class' => 'js-configuration-exception'
                ]
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
