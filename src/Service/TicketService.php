<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\User;
use App\Repository\HomeRepository;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function subtractionOfTicketsAmount(): string
    {
        $homeUsers = $this->em->getRepository(User::class)->findBy(['home' => 1]);
//        dump($homeUsers);


        $userAmount= [];
        foreach ($homeUsers as $user) {
            // Utilise la méthode getUserTickets pour obtenir le montant pour chaque utilisateur
            $userAmount[] = $this->getUserTickets($user);

        }


        $subtractionResult = ($userAmount[0] - $userAmount[1]) / 2;


        return $subtractionResult < 0 ? 'Vous devez payer ' . abs($subtractionResult) . '€' : 'Vous avez droit à un remboursement de ' . $subtractionResult . '€';
    }

    private function getUserTickets(User $user): float
    {
        // Utilise la méthode getUserTickets pour obtenir le montant pour chaque utilisateur
        $tickets = $this->em->getRepository(Ticket::class)->findBy(['user' => $user]);

        // Calcule et retourne le montant total des tickets pour un utilisateur
        return array_reduce(
            $tickets,
            fn($sum, $ticket) => $sum + $ticket->getAmount(),
            0
        );
    }


    private function calculateTotalAmount(?array $tickets): float
    {
        if (!$tickets) {
            return 0;
        }

        return array_reduce(
            $tickets,
            fn($sum, $ticket) => $sum + $ticket->getAmount(),
            0
        );
    }
}
