<?php

namespace App\Service;


use App\Repository\HomeRepository;
use Doctrine\ORM\EntityManagerInterface;

class TicketService
{
    private HomeRepository $homeRepository;
    private EntityManagerInterface $em;


    public function __construct(EntityManagerInterface $em, HomeRepository $homeRepository)
    {

        $this->homeRepository = $homeRepository;
        $this->em = $em;
    }


    public function subtractionOfTicketsAmount($connectedUserId): float | string
    {
        $homes = $this->homeRepository->findHomesByUser($connectedUserId);


        foreach ($homes as $home) {
            $foyerUsers = $home->getUsers();

            // Vérifier si le foyer a exactement 2 utilisateurs
            if ($foyerUsers->count() === 2) {
                $usersTickets = [];

                foreach ($foyerUsers as $user) {
                    // Vérifier si la collection de tickets est initialisée
                    if (!$user->getTickets()->isInitialized()) {
                        $user->getTickets()->initialize();
                    }

                    // Accéder à la collection de tickets de l'utilisateur
                    $tickets = $user->getTickets()->toArray();
                    $usersTickets[$user->getId()] = $this->calculateTotalAmount($tickets);
                }


                // Comparer les totaux des tickets des utilisateurs du foyer
                $difference = $usersTickets[1] - $usersTickets[2];
                $formattedDifference = $difference < 0 ? substr($difference, 1) : $difference;

                return $difference < 0 ? 'Vous devez : ' . $formattedDifference . '€' : ($difference > 0 ? 'On vous doit : ' . $difference . '€' : '');


            }
        }

    }

    private function calculateTotalAmount(array $tickets): float
    {
        $totalAmount = 0;

        foreach ($tickets as $ticket) {
            $totalAmount += $ticket->getAmount();
        }

        return $totalAmount;
    }

}
