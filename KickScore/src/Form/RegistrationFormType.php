<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Mail')
            ->add(
                'FirstName',
                null,
                [
                'label' => 'form.firstname.label',
                'attr' => ['placeholder' => 'form.firstname.placeholder'],
                'constraints' => [
                    new NotBlank(
                        [
                        'message' => 'form.firstname.not_blank',
                        ]
                    ),
                    new Length(
                        [
                        'min' => 2,
                        'minMessage' => 'form.firstname.min_length',
                        'max' => 32,
                        ]
                    ),
                ],
                'translation_domain' => 'validators',
                ]
            )
            ->add(
                'Name',
                null,
                [
                'label' => 'Nom de famille',
                'attr' => ['placeholder' => 'Insérer votre nom de famille...'],
                'required' => true,
                'constraints' => [
                    new NotBlank(
                        [
                        'message' => 'Veuillez insérer votre nom de famille.',
                        ]
                    ),
                    new Length(
                        [
                        'min' => 2,
                        'minMessage' => 'Votre nom de famille doit être de{{ limit }} caractères minimum.',
                        'max' => 32,
                        ]
                    ),
                ],
                ]
            )
            ->add(
                'plainPassword',
                PasswordType::class,
                [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(
                        [
                        'message' => 'Veuillez insérer un mot de passe.',
                        ]
                    ),
                    new Length(
                        [
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit être de{{ limit }} caractères minimum.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                        ]
                    ),
                ],
                ]
            )
            ->add(
                'IsOrganizer',
                HiddenType::class,
                [
                'data' => 0,
                'empty_data' => 0,
                'required' => true,
                'mapped' => true,
                ]
            )
            //for future adaptation of organizer adding new organizer from the registration form
        //            ->add('IsOrganizer', CheckboxType::class, [
        //                'data' => false,
        //                'data_class' => null,
        //                'empty_data' => false,
        //                'required' => false,
        //                'label' => 'Faire de cet utilisateur un organisateur ?',
        //                'attr' => ['class' => 'form-check-input'],
        //            ])
        ;
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
