<?php
namespace App\Controller;

use App\Entity\Home;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeUsersController extends AbstractController
{
#[Route('/foyer', name: 'app_home_users')]
public function index(): Response
{
// Récupérer l'utilisateur connecté
$user = $this->getUser();

// Vérifier si l'utilisateur est connecté
if (!$user) {
// Gérer le cas où l'utilisateur n'est pas connecté
// Redirection, affichage d'un message, etc.
}

// Récupérer tous les foyers (homes) de l'utilisateur connecté
$homes = $user->getFoyer();

// Initialiser une liste pour stocker tous les utilisateurs dans les foyers de l'utilisateur connecté
$usersInHomes = [];

// Parcourir tous les foyers de l'utilisateur
foreach ($homes as $home) {
// Récupérer la collection d'utilisateurs associés à ce foyer
$usersCollection = $home->getUsers();

// Si la collection n'est pas initialisée, vous pouvez le faire ici
if (!$usersCollection->isInitialized()) {
$usersCollection->initialize();
}

// Ajouter les utilisateurs de ce foyer à la liste globale
$usersInHomes = array_merge($usersInHomes, $usersCollection->toArray());
}


return $this->render('home_users/index.html.twig', [
'usersInHomes' => $usersInHomes,

]);
}
}
