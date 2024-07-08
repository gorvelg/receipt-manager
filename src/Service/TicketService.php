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

    public function subtractionOfTicketsAmount(User $connectedUser): string
    {
        $homeUsers = $this->em->getRepository(User::class)->findBy(['home' => 1]);

        $userAmount = [];
        $totalConnectedUserTickets = $this->calculateTotalAmount($connectedUser->getTickets()->toArray());

        foreach ($homeUsers as $user) {
            $userAmount[] = $this->getUserTickets($user);
        }

        if ($totalConnectedUserTickets === $userAmount[0]) {
        $subtractionResult = ($userAmount[0] - $userAmount[1]) / 2;
        } else {
            $subtractionResult = ($userAmount[1] - $userAmount[0]) / 2;
        }

        return $subtractionResult;
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
