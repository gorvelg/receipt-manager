<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\TicketType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class TicketController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/ticket/set/{ticket}', name: 'app_ticket', methods: ['GET', 'POST'])]
    public function setTicket(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, ?Ticket $ticket = null): Response
    {
        $isCreated = true;

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur invalide.');
        }

        if (!$ticket) {
            $ticket = new Ticket();
            $ticket->setUser($user);
            $isCreated = false;
        }

        if ($ticket->getUser() !== $user && $ticket->getUser()->getHome() !== $user->getHome()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce ticket.');
        }

        $oldPictureName = $ticket->getPhoto();

        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('photo')->getData();

            if ($pictureFile) {
                if ($oldPictureName) {
                    $oldPicturePath = $this->getParameter('pictures_directory').DIRECTORY_SEPARATOR.$oldPictureName;
                    if (file_exists($oldPicturePath)) {
                        unlink($oldPicturePath);
                    }
                }

                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $ticket->setPhoto($newFilename);
            }

            $em->persist($ticket);
            $em->flush();

            return $this->redirectToRoute('app_index');
        }

        return $this->render('ticket/set.html.twig', [
            'form' => $form->createView(),
            'isCreated' => $isCreated,
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
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur invalide.');
        }

        if ($ticket->getUser() !== $user && $ticket->getUser()->getHome() !== $user->getHome()) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier ce ticket.');
        }

        return $this->render('ticket/get.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
