<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StaticPagesController extends AbstractController
{
    #[Route('/legal-info', name: 'app_legal_info')]
    public function index(): Response
    {
        return $this->render('static_pages/legal_info.html.twig', [
            'controller_name' => 'StaticPagesController',
        ]);
    }

    #[Route('/help', name: 'app_help')]
    public function help(): Response
    {
        return $this->render('static_pages/help.html.twig', [
            'controller_name' => 'StaticPagesController',
        ]);
    }
}
