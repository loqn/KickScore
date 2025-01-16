<?php

namespace App\Form;

use App\Entity\Tournament;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class TournamentType extends AbstractType
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tournament', ChoiceType::class, [
                'choices' => $this->getTournamentsChoices(),
                'placeholder' => 'Choisir un tournoi',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ])
            ->add('action_type', HiddenType::class, [
                'mapped' => false, // Ce champ n'est pas lié à l'entité
            ]);
    }

    private function getTournamentsChoices()
    {
        // Récupérer l'EntityManager depuis le ManagerRegistry
        $entityManager = $this->managerRegistry->getManager();
        $tournaments = $entityManager->getRepository(Tournament::class)->findAll();

        $choices = [];
        foreach ($tournaments as $tournament) {
            $choices[$tournament->getTRMNAME()] = $tournament->getId(); // Remplacez getName() si nécessaire
        }

        return $choices;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
        ]);
    }
}
