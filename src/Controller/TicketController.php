<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TicketController extends AbstractController
{
    private EntityManagerInterface $em;
    private TicketService $ticketService;
    private LoggerInterface $logger;
    public function __construct(EntityManagerInterface $em, TicketService $ticketService, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->ticketService = $ticketService;
        $this->logger = $logger;
    }

    #[Route('/ticket/set/{ticket}', name: 'app_ticket', methods: ['GET', 'POST'])]
    public function setTicket(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, Ticket $ticket = null) : Response
    {
        $isCreated = true;

        if (!$ticket) {
            $ticket = new Ticket();
            $ticket->setUser($this->getUser());
            $isCreated = false;
        }

        $user = $this->getUser();
        if ($ticket->getUser() !== $user && $ticket->getUser()->getHome() !== $user->getHome()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce ticket.');
        }

        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $pictureFile = $form->get('photo')->getData();

            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {}
                $ticket->setPhoto($newFilename);
            }

            $em->persist($ticket);
            $em->flush();
            return $this->redirectToRoute('app_index');
        }

        return $this->render('ticket/set.html.twig', [
            'form' => $form->createView(),
            'isCreated' => $isCreated
        ]);
    }
    #[Route('/ticket/{id}', name: 'app_ticket_delete', methods: ['DELETE'])]
    public function deleteTicket(int $id): JsonResponse
    {
        $ticket = $this->em->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return new JsonResponse(['error' => 'Ticket introuvable'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($ticket);
        $this->em->flush();

        return new JsonResponse(['success' => 'Ticket supprimé'], Response::HTTP_OK);
    }


    #[Route('/ticket/{ticket}', name: 'app_get_ticket', methods: ['GET'])]
    public function getTicket(Ticket $ticket): Response
    {
        $user = $this->getUser();
        if ($ticket->getUser() !== $user && $ticket->getUser()->getHome() !== $user->getHome()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce ticket.');
        }

        return $this->render('ticket/get.html.twig', [
            'ticket' => $ticket,
        ]);
    }

}
