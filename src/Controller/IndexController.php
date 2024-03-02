<?php

namespace App\Controller;

use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();

        return $this->render('index/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }
}
