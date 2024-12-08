<?php

namespace App\Controller\Admin\User;

use App\Entity\User;
use App\Form\UserAdminType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin/get', name: 'app_admin_get_user')]
    public function get(): Response
    {
        $users = $this->em->getRepository(User::class)->findAll();

        return $this->render('admin/user/get.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/admin/set/{id}', name: 'app_admin_set_user')]
    public function set(Request $request, int $id): Response
    {
        $user = $this->em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur n\'existe pas.');
        }

        $form = $this->createForm(UserAdminType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('app_admin_get_user');
        }

        // Afficher le formulaire dans la vue
        return $this->render('admin/user/set.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

}