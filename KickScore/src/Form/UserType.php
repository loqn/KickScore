<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('FirstName')
            ->add('name')
            ->add('mail')
            ->add('isOrganizer')
            ->add('password')
            ->add(
                'team',
                EntityType::class,
                [
                'class' => Team::class,
                'choice_label' => 'id',
                ]
            )
            ->add(
                'member',
                EntityType::class,
                [
                'class' => Member::class,
                'choice_label' => 'id',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            'data_class' => User::class,
            ]
        );
    }
}
