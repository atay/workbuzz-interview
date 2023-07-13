<?php

namespace App\Infrastructure\Framework\Symfony\Form;

use App\Infrastructure\Persistence\Doctrine\Entity\Survey;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $validStatuses = [Survey::STATUS_NEW, Survey::STATUS_LIVE, Survey::STATUS_CLOSED];

        $builder
            ->add('status', ChoiceType::class, ['choices' => array_combine($validStatuses, $validStatuses)])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}