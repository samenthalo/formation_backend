<?php
// src/Controller/EvaluationAssignationController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EvaluationStagiaire;
use App\Entity\Evaluation;
use App\Entity\Stagiaire;
use Symfony\Component\Routing\Annotation\Route;

class EvaluationAssignationController extends AbstractController
{
    // Route pour assigner une évaluation à tous les stagiaires d'une session
    #[Route('/assigner-evaluation-session', name: 'assigner_evaluation_session', methods: ['POST'])]
    public function assignerEvaluationSession(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $idSession = $data['id_session'] ?? null;
        $idEvaluation = $data['id_evaluation'] ?? null;

        if (!$idSession || !$idEvaluation) {
            return new JsonResponse(['error' => 'Missing parameters id_session or id_evaluation'], 400);
        }

        $evaluation = $em->getRepository(Evaluation::class)->find($idEvaluation);
        if (!$evaluation) {
            return new JsonResponse(['error' => 'Evaluation not found'], 404);
        }

        // Récupérer tous les stagiaires de la session
        $stagiaires = $em->getRepository(Stagiaire::class)->findBySessionId($idSession);
        if (empty($stagiaires)) {
            return new JsonResponse(['error' => 'No stagiaires found for this session'], 404);
        }

        $compteurNouveaux = 0;
        foreach ($stagiaires as $stagiaire) {
            // Vérifier si l'évaluation est déjà assignée à ce stagiaire
            $existing = $em->getRepository(EvaluationStagiaire::class)->findOneBy([
                'evaluation' => $evaluation,
                'stagiaire' => $stagiaire,
            ]);

            if ($existing) {
                continue; // déjà assigné, on passe
            }

            $assignation = new EvaluationStagiaire();
            $assignation->setEvaluation($evaluation);
            $assignation->setStagiaire($stagiaire);
            $assignation->setDateAssignation(new \DateTime());
            $assignation->setStatut('non commencé');

            $em->persist($assignation);
            $compteurNouveaux++;
        }

        $em->flush();

        return new JsonResponse([
            'success' => "$compteurNouveaux stagiaire(s) ont reçu l’évaluation (non déjà assignés)."
        ]);
    }

    // Route pour récupérer les évaluations d'un stagiaire
    #[Route('/stagiaire/{id}/evaluations', name: 'stagiaire_evaluations', methods: ['GET'])]
    public function getEvaluationsByStagiaire(int $id, EntityManagerInterface $em): JsonResponse
    {
        $stagiaire = $em->getRepository(Stagiaire::class)->find($id);
        if (!$stagiaire) {
            return new JsonResponse(['error' => 'Stagiaire not found'], 404);
        }

        $assignations = $em->getRepository(EvaluationStagiaire::class)->findBy(['stagiaire' => $stagiaire]);
        $result = [];

        foreach ($assignations as $assignation) {
            $evaluation = $assignation->getEvaluation();
            $questionsData = [];

            foreach ($evaluation->getQuestions() as $question) {
                $reponsesData = [];
                foreach ($question->getReponses() as $reponse) {
                    $reponsesData[] = [
                        'id' => $reponse->getId(),
                        'texte' => $reponse->getContenu(),
                        'est_correcte' => $reponse->isEstCorrect(),
                    ];
                }

                $questionsData[] = [
                    'id' => $question->getId(),
                    'contenu' => $question->getContenu(),
                    'type' => $question->getType(),
                    'options' => $question->getOptions(),
                    'min_note' => $question->getMinNote(),
                    'max_note' => $question->getMaxNote(),
                    'reponses' => $reponsesData,
                ];
            }

            $result[] = [
                'id' => $assignation->getId(),
                'evaluation_id' => $evaluation->getId(),
                'titre' => $evaluation->getTitre(),
                'description' => $evaluation->getDescription(),
                'type' => $evaluation->getType(),
                'statut' => $assignation->getStatut(),
                'score' => $assignation->getScore(),
                'questions' => $questionsData,
            ];
        }

        return new JsonResponse($result);
    }

    // Route pour récupérer les détails d'une évaluation par son ID
    #[Route('/evaluations/{id}', name: 'evaluation_detail', methods: ['GET'])]
    public function getEvaluationById(int $id, EntityManagerInterface $em): JsonResponse
    {
        $evaluation = $em->getRepository(Evaluation::class)->find($id);
        if (!$evaluation) {
            return new JsonResponse(['error' => 'Évaluation non trouvée'], 404);
        }

        $questionsData = [];
        foreach ($evaluation->getQuestions() as $question) {
            $reponsesData = [];
            foreach ($question->getReponses() as $reponse) {
                $reponsesData[] = [
                    'id' => $reponse->getId(),
                    'texte' => $reponse->getContenu(),
                    'est_correcte' => $reponse->isEstCorrect(),
                    'note' => $reponse->getNote(),
                ];
            }

            $questionsData[] = [
                'id' => $question->getId(),
                'contenu' => $question->getContenu(),
                'type' => $question->getType(),
                'options' => $question->getOptions(),
                'min_note' => $question->getMinNote(),
                'max_note' => $question->getMaxNote(),
                'reponses' => $reponsesData
            ];
        }

        return new JsonResponse([
            'id' => $evaluation->getId(),
            'titre' => $evaluation->getTitre(),
            'description' => $evaluation->getDescription(),
            'type' => $evaluation->getType(),
            'questions' => $questionsData
        ]);
    }

    // Route pour enregistrer les réponses d'un stagiaire à une évaluation
    #[Route('/evaluation/repondre', name: 'enregistrer_reponses', methods: ['POST'])]
    public function enregistrerReponses(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id_stagiaire = $data['stagiaire_id'] ?? null;
        $reponses = $data['reponses'] ?? [];

        if (!$id_stagiaire || empty($reponses)) {
            return new JsonResponse(['error' => 'Paramètres stagiaire_id ou reponses manquants'], 400);
        }

        $stagiaire = $em->getRepository(Stagiaire::class)->find($id_stagiaire);
        if (!$stagiaire) {
            return new JsonResponse(['error' => 'Stagiaire non trouvé'], 404);
        }

        foreach ($reponses as $reponseData) {
            $questionId = $reponseData['question_id'] ?? null;
            $contenuReponse = $reponseData['reponse'] ?? null;
            $idReponsePredefinie = $reponseData['id_reponse'] ?? null;

            if (!$questionId) {
                continue; // Ignore si pas de question
            }

            $question = $em->getRepository(\App\Entity\Question::class)->find($questionId);
            if (!$question) {
                continue; // Ignore les questions inexistantes
            }

            $reponseUtilisateur = new \App\Entity\ReponseUtilisateur();
            $reponseUtilisateur->setStagiaire($stagiaire);
            $reponseUtilisateur->setQuestion($question);
            $reponseUtilisateur->setDateReponse(new \DateTime());

            if ($idReponsePredefinie) {
                $reponsePredefinie = $em->getRepository(\App\Entity\Reponse::class)->find($idReponsePredefinie);
                if ($reponsePredefinie) {
                    $reponseUtilisateur->setReponsePredefinie($reponsePredefinie);
                    $reponseUtilisateur->setReponse(null);
                } else {
                    continue; // Si l'id_reponse donné n'existe pas, on ignore cette réponse
                }
            } elseif ($contenuReponse !== null) {
                $reponseUtilisateur->setReponse($contenuReponse);
                $reponseUtilisateur->setReponsePredefinie(null);
            } else {
                continue; // Ni id_reponse ni réponse texte, on ignore
            }

            $em->persist($reponseUtilisateur);
        }

        $em->flush();

        return new JsonResponse(['success' => 'Réponses enregistrées avec succès']);
    }

    // Route pour mettre à jour le score d'une évaluation stagiaire
    #[Route('/mettre-a-jour-score', name: 'mettre_a_jour_score', methods: ['POST'])]
    public function mettreAJourScore(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $idEvaluationStagiaire = $data['id'] ?? null;
        $score = $data['score'] ?? null;

        if ($idEvaluationStagiaire === null || $score === null) {
            return new JsonResponse(['error' => 'Missing id or score'], 400);
        }

        $assignation = $em->getRepository(EvaluationStagiaire::class)->find($idEvaluationStagiaire);
        if (!$assignation) {
            return new JsonResponse(['error' => 'Assignation not found'], 404);
        }

        $assignation->setScore($score);
        $assignation->setStatut('terminé');
        $em->flush();

        return new JsonResponse(['success' => 'Score mis à jour']);
    }
}
