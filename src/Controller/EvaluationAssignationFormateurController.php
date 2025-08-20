<?php
// src/Controller/EvaluationAssignationFormateurController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\EvaluationFormateur;
use App\Entity\Evaluation;
use App\Entity\Formateur;
use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\ReponseFormateur;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\SessionFormation;
use Symfony\Component\HttpFoundation\Response;

class EvaluationAssignationFormateurController extends AbstractController
{
// Assigner une évaluation à tous les formateurs d'une session
#[Route('/assigner-evaluation-session-formateur', name: 'assigner_evaluation_session_formateur', methods: ['POST'])]
public function assignerEvaluationSessionFormateur(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $idSession = $data['id_session'] ?? null;
    $idEvaluation = $data['id_evaluation'] ?? null;

    if (!$idSession || !$idEvaluation) {
        return new JsonResponse(['error' => 'Paramètres manquants : id_session ou id_evaluation'], 400);
    }

    $evaluation = $em->getRepository(Evaluation::class)->find($idEvaluation);
    if (!$evaluation) {
        return new JsonResponse(['error' => 'Évaluation non trouvée'], 404);
    }

    $session = $em->getRepository(SessionFormation::class)->find($idSession);
    if (!$session) {
        return new JsonResponse(['error' => 'Session non trouvée'], 404);
    }

    $formateur = $session->getFormateur();

    if (!$formateur) {
        return new JsonResponse(['error' => 'Aucun formateur assigné à cette session'], 404);
    }

    $existing = $em->getRepository(EvaluationFormateur::class)->findOneBy([
        'evaluation' => $evaluation,
        'formateur' => $formateur,
    ]);

    if ($existing) {
        return new JsonResponse(['message' => 'Cette évaluation est déjà assignée à ce formateur']);
    }

    $assignation = new EvaluationFormateur();
    $assignation->setEvaluation($evaluation);
    $assignation->setFormateur($formateur);
    $assignation->setDateAssignation(new \DateTime());
    $assignation->setStatut('non commencé');

    $em->persist($assignation);
    $em->flush();

    return new JsonResponse([
        'success' => 'Évaluation assignée au formateur de la session.'
    ]);
}


    // Récupérer les évaluations d'un formateur
    #[Route('/formateur/{id}/evaluations', name: 'formateur_evaluations', methods: ['GET'])]
    public function getEvaluationsByFormateur(int $id, EntityManagerInterface $em): JsonResponse
    {
        $formateur = $em->getRepository(Formateur::class)->find($id);
        if (!$formateur) {
            return new JsonResponse(['error' => 'Formateur not found'], 404);
        }

        $assignations = $em->getRepository(EvaluationFormateur::class)->findBy(['formateur' => $formateur]);
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

    // Récupérer les détails d'une évaluation par son ID
    #[Route('/evaluations-formateur/{id}', name: 'evaluation_formateur_detail', methods: ['GET'])]
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

    // Enregistrer les réponses d'un formateur à une évaluation
#[Route('/evaluation-formateur/repondre', name: 'enregistrer_reponses_formateur', methods: ['POST'])]
public function enregistrerReponses(Request $request, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $id_formateur = $data['formateur_id'] ?? null;
    $reponses = $data['reponses'] ?? [];

    if (!$id_formateur || empty($reponses)) {
        return new JsonResponse(['error' => 'Paramètres formateur_id ou reponses manquants'], 400);
    }

    $formateur = $em->getRepository(Formateur::class)->find($id_formateur);
    if (!$formateur) {
        return new JsonResponse(['error' => 'Formateur non trouvé'], 404);
    }

    foreach ($reponses as $reponseData) {
        $questionId = $reponseData['question_id'] ?? null;
        $contenuReponse = $reponseData['reponse'] ?? null;
        $idReponsePredefinie = $reponseData['id_reponse'] ?? null;

        if (!$questionId) {
            continue;
        }

        $question = $em->getRepository(Question::class)->find($questionId);
        if (!$question) {
            continue;
        }

        $reponseUtilisateur = new ReponseFormateur();
        $reponseUtilisateur->setFormateur($formateur);
        $reponseUtilisateur->setQuestion($question);
        $reponseUtilisateur->setDateReponse(new \DateTime());

        if ($idReponsePredefinie) {
            // Réponse prédéfinie (id_reponse dans la table)
            $reponsePredefinie = $em->getRepository(Reponse::class)->find($idReponsePredefinie);
            if ($reponsePredefinie) {
                $reponseUtilisateur->setReponsePredefinie($reponsePredefinie);
                $reponseUtilisateur->setReponse(null); // pas de texte libre
            } else {
                continue;
            }
        } elseif ($contenuReponse !== null) {
            // Réponse libre (champ reponse dans la table)
            $reponseUtilisateur->setReponsePredefinie(null);
            $reponseUtilisateur->setReponse($contenuReponse);
        } else {
            continue;
        }

        $em->persist($reponseUtilisateur);
    }

    $em->flush();

    return new JsonResponse(['success' => 'Réponses enregistrées avec succès']);
}



    // Mettre à jour le score d'une évaluation formateur
    #[Route('/mettre-a-jour-score-formateur', name: 'mettre_a_jour_score_formateur', methods: ['POST'])]
    public function mettreAJourScore(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $idAssignation = $data['id_assignation'] ?? null;
        $score = $data['score'] ?? null;

        if (!$idAssignation || $score === null) {
            return new JsonResponse(['error' => 'Paramètres id_assignation ou score manquants'], 400);
        }

        $assignation = $em->getRepository(EvaluationFormateur::class)->find($idAssignation);
        if (!$assignation) {
            return new JsonResponse(['error' => 'Assignation non trouvée'], 404);
        }

        $assignation->setScore($score);
        $assignation->setStatut('terminé');

        $em->flush();

        return new JsonResponse(['success' => 'Score mis à jour avec succès']);
    }
}
