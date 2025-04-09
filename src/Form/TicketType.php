<?php

namespace App\Form;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $connectedUser = $this->security->getUser();

        if (!$connectedUser instanceof User) {
            throw new \LogicException('L\'utilisateur connecté n\'est pas valide.');
        }

        $homeId = $connectedUser->getHome() ? $connectedUser->getHome()->getId() : null;

        $builder
            ->add('title', TextType::class, [
                'label' => 'Enseigne',
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
            ])
            ->add('photo', FileType::class, [
                'data_class' => null,
                'label' => 'Photo',
                'required' => false,
            ])
            ->add('created_at', DateTimeType::class, [
                'label' => 'Date du ticket',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'label' => 'Utilisateur',
                'choice_label' => 'username',
                'query_builder' => function (EntityRepository $er) use ($homeId) {
                    // Si un homeId est défini, on filtre les utilisateurs par homeId
                    return $er->createQueryBuilder('u')
                        ->where('u.home = :homeId')
                        ->setParameter('homeId', $homeId);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
