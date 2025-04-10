<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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

    #[Route('/', name: 'app_index')]
    public function index(ParameterBagInterface $parameterBag): Response
    {
        $storeLogos = $parameterBag->get('store_logos');

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur invalide.');
        }
        $home = $user->getHome();

        if (empty($home)) {
            $this->addFlash('danger', 'L\'utilisateur n\'a pas de Home attribué.');

            return $this->render('errors/error.html.twig', [
            ]);
        }

        $homeId = $home->getId();

        $usersInHome = $this->em->getRepository(User::class)->findBy(['home' => $homeId]);
        $userIds = array_map(fn ($user) => $user->getId(), $usersInHome);

        $tickets = $this->em->getRepository(Ticket::class)->findBy([
            'user' => $userIds,
        ]);
        $total = $this->ticketService->subtractionOfTicketsAmount($user);
        //        dump($this->getUser()->getId());

        return $this->render('index/index.html.twig', [
            'tickets' => $tickets,
            'total' => $total,
            'storeLogos' => $storeLogos,
        ]);
    }

    #[Route('/update-total', name: 'app_update_total', methods: ['PATCH'])]
    public function ajaxTotal(): Response
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur invalide.');
        }
        $total = $this->ticketService->subtractionOfTicketsAmount($user);

        return new Response((string) $total, Response::HTTP_OK);
    }
}
