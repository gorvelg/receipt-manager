<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin/get', name: 'app_admin_get_user')]
    public function set(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->render('admin/user/get.html.twig', [
            'users' => $users
        ]);
    }
}