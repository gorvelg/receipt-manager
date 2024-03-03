<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TicketController extends AbstractController
{
    #[Route('/ticket/{ticket}', name: 'app_ticket', methods: ['GET', 'POST'])]
    public function setTicket(Request $request, EntityManagerInterface $em, Ticket $ticket = null) : Response
    {
        if (!$ticket) {
            $ticket = new Ticket();
        }
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('app_index');
        }

        return $this->render('ticket/set.html.twig', [
            'form' => $form->createView()
        ]);
    }
    #[Route('/ticket/{ticket}', name: 'app_ticket_delete', methods: 'DELETE')]
    public function removeTicket(Request $request, EntityManagerInterface $em, Ticket $ticket): Response
    {
        $em->remove($ticket);
        $em->flush();
        return $this->redirectToRoute('app_index');
    }

}
