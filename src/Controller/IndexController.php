<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    private EntityManagerInterface $em;
    private TicketService $ticketService;

    public function __construct(EntityManagerInterface $em, TicketService $ticketService)
    {
        $this->em = $em;
        $this->ticketService = $ticketService;
    }


    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();
        $total = $this->ticketService->subtractionOfTicketsAmount($this->getUser());
//        dump($this->getUser()->getId());


        return $this->render('index/index.html.twig', [

            'tickets' => $tickets,
            'total' => $total,

        ]);
    }


}
