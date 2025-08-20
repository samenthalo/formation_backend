<?php
namespace App\Controller;

use App\Repository\SessionFormationRepository;
use App\Service\StatistiquesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatistiquesController extends AbstractController
{
    private StatistiquesService $statistiquesService;

    public function __construct(StatistiquesService $statistiquesService)
    {
        $this->statistiquesService = $statistiquesService;
    }

    // Route pour récupérer les statistiques des sessions
    #[Route('/statistiques/sessions', name: 'statistiques_sessions', methods: ['GET'])]
    public function statistiquesSessions(SessionFormationRepository $sessionFormationRepository): JsonResponse
    {
        $sessions = $sessionFormationRepository->findAll();
        $statistiques = [
            'par_annee' => [],
            'details' => []
        ];

        foreach ($sessions as $session) {
            $dates = [];
            foreach ($session->getCreneaux() as $creneau) {
                $jour = $creneau->getJour();
                $annee = $jour->format('Y');
                $dates[] = $jour->format('Y-m-d');

                if (!isset($statistiques['par_annee'][$annee])) {
                    $statistiques['par_annee'][$annee] = 0;
                }
                $statistiques['par_annee'][$annee]++;
            }

            $statistiques['details'][] = [
                'id_session' => $session->getIdSession(),
                'titre' => $session->getTitre(),
                'mode' => $session->getMode(),
                'dates' => array_unique($dates),
            ];
        }

        return new JsonResponse($statistiques);
    }

    // Route pour récupérer un aperçu des statistiques des stagiaires
    #[Route('/statistiques/stagiaires', name: 'statistiques_stagiaires', methods: ['GET'])]
    public function statistiquesOverview(): JsonResponse
    {
        $stats = $this->statistiquesService->getStatistiques();
        return new JsonResponse($stats);
    }

    // Route pour récupérer le taux de satisfaction
    #[Route('/statistiques/taux-satisfaction', name: 'statistiques_taux_satisfaction', methods: ['GET'])]
    public function tauxSatisfaction(): JsonResponse
    {
        $taux = $this->statistiquesService->calculerTauxSatisfactionQuestionnaires();
        return new JsonResponse([
            'taux_satisfaction' => $taux,
            'unite' => '%'
        ]);
    }

    // Route pour récupérer le taux de réussite
    #[Route('/statistiques/taux-reussite', name: 'statistiques_taux_reussite', methods: ['GET'])]
    public function tauxReussite(): JsonResponse
    {
        $taux = $this->statistiquesService->calculerTauxReussiteQuiz();
        return new JsonResponse([
            'taux_reussite' => $taux,
            'unite' => '%'
        ]);
    }

    // Route pour récupérer le taux de satisfaction par questionnaire
    #[Route('/statistiques/taux-satisfaction-par-questionnaire', name: 'statistiques_satisfaction_par_questionnaire', methods: ['GET'])]
    public function satisfactionParQuestionnaire(): JsonResponse
    {
        return new JsonResponse($this->statistiquesService->calculerTauxSatisfactionParQuestionnaire());
    }

    // Route pour récupérer le taux de réussite par quiz
    #[Route('/statistiques/taux-reussite-par-quiz', name: 'statistiques_reussite_par_quiz', methods: ['GET'])]
    public function reussiteParQuiz(): JsonResponse
    {
        return new JsonResponse($this->statistiquesService->calculerTauxReussiteParQuiz());
    }
}
