<?php

namespace App\Controller;

use App\Repository\SessionFormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SessionFormationTestController extends AbstractController
{
    #[Route('/test/sessionformation', name: 'test_sessionformation')]
    public function index(SessionFormationRepository $sessionFormationRepository): Response
    {
        // Récupérer toutes les sessions
        $sessions = $sessionFormationRepository->findAllSessions();

        // Afficher les données dans la barre de débogage
        dump($sessions); // Vérifie ici si les données sont bien récupérées

        return $this->render('session_formation_test/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }
}

