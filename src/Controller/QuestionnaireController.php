<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuestionnaireController extends AbstractController
{
    #[Route('/questionnaire', name: 'api_creer_questionnaire', methods: ['POST'])]
    public function creerQuestionnaire(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }
        // Vérifier les champs obligatoires
        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $tauxReussite = $data['tauxReussite'] ?? null;
        $type = $data['type'] ?? null;
        $idFormation = $data['id_formation'] ?? null; // Récupérer l'ID de la formation
        $questionsData = $data['questions'] ?? null;

        // Vérifier que tous les champs nécessaires sont présents
        if (!$titre || !$description || !$tauxReussite || !$type || !$idFormation || !$questionsData) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Créer une instance de Evaluation (qui représente aussi un questionnaire ici)
        $questionnaire = new Evaluation();
        $questionnaire->setTitre($titre);
        $questionnaire->setDescription($description);
        $questionnaire->setTauxReussite((float) $tauxReussite);
        $questionnaire->setType($type); // Ex: 'questionnaire' au lieu de 'quiz'
        $questionnaire->setIdFormation($idFormation); // Enregistrer l'ID de la formation

        $entityManager->persist($questionnaire);
        $entityManager->flush();

        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($questionnaire);

            $entityManager->persist($question);
            $entityManager->flush();
            // Si des réponses sont fournies, les ajouter
            if (isset($questionData['reponses'])) {
                foreach ($questionData['reponses'] as $reponseData) {
                    $reponse = new Reponse();
                    $reponse->setContenu($reponseData['libelle']);
                    $reponse->setEstCorrect($reponseData['correct']);
                    $reponse->setNote($reponseData['note']);
                    $reponse->setQuestion($question);

                    $entityManager->persist($reponse);
                }
            }
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Questionnaire créé avec succès',
            'id' => $questionnaire->getId()
        ], JsonResponse::HTTP_CREATED);
    }

    // Route pour modifier un questionnaire existant
    #[Route('/questionnaire/{id}', name: 'api_modifier_questionnaire', methods: ['POST'])]
    public function modifierQuestionnaire(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Vérifier si les données sont valides
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $questionnaire = $entityManager->getRepository(Evaluation::class)->find($id);

        if (!$questionnaire) {
            return new JsonResponse(['message' => 'Questionnaire non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
        // Mettre à jour les champs du questionnaire
        $titre = $data['titre'] ?? $questionnaire->getTitre();
        $description = $data['description'] ?? $questionnaire->getDescription();
        $tauxReussite = $data['tauxReussite'] ?? $questionnaire->getTauxReussite();
        $type = $data['type'] ?? $questionnaire->getType();
        $idFormation = $data['id_formation'] ?? $questionnaire->getIdFormation();
        $questionsData = $data['questions'] ?? null;
        // Vérifier que les champs nécessaires sont présents
        $questionnaire->setTitre($titre);
        $questionnaire->setDescription($description);
        $questionnaire->setTauxReussite((float) $tauxReussite);
        $questionnaire->setType($type);
        $questionnaire->setIdFormation($idFormation);

        $entityManager->persist($questionnaire);

        // Supprimer les questions existantes avant d'ajouter les nouvelles
        foreach ($questionnaire->getQuestions() as $question) {
            $entityManager->remove($question);
        }
        // Supprimer les réponses existantes
        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($questionnaire);

            $entityManager->persist($question);
            // Enregistrer les nouvelles questions
            if (isset($questionData['reponses'])) {
                foreach ($questionData['reponses'] as $reponseData) {
                    $reponse = new Reponse();
                    $reponse->setContenu($reponseData['libelle']);
                    $reponse->setEstCorrect($reponseData['correct']);
                    $reponse->setNote($reponseData['note']);
                    $reponse->setQuestion($question);

                    $entityManager->persist($reponse);
                }
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Questionnaire mis à jour avec succès'], JsonResponse::HTTP_OK);
    }

    // Route pour lister tous les questionnaires
    #[Route('/questionnaire', name: 'api_lister_questionnaire', methods: ['GET'])]
    public function listerQuestionnaire(EntityManagerInterface $entityManager): JsonResponse
    {
        $questionnaireRepository = $entityManager->getRepository(Evaluation::class);
        $questionnaireList = $questionnaireRepository->findBy(['type' => 'questionnaire']);

        $data = [];
        // Parcourir chaque questionnaire et ses questions/réponses
        foreach ($questionnaireList as $questionnaire) {
            $questionsData = [];
            foreach ($questionnaire->getQuestions() as $question) {
                $reponsesData = [];
                foreach ($question->getReponses() as $reponse) {
                    $reponsesData[] = [
                        'id' => $reponse->getId(),
                        'libelle' => $reponse->getContenu(),
                        'correct' => $reponse->isEstCorrect(),
                        'note' => $reponse->getNote(),
                    ];
                }

                $questionsData[] = [
                    'id' => $question->getId(),
                    'contenu' => $question->getContenu(),
                    'type' => $question->getType(),
                    'reponses' => $reponsesData
                ];
            }
            // Préparer les données du questionnaire
            $data[] = [
                'id' => $questionnaire->getId(),
                'titre' => $questionnaire->getTitre(),
                'description' => $questionnaire->getDescription(),
                'tauxReussite' => $questionnaire->getTauxReussite(),
                'type' => $questionnaire->getType(),
                'id_formation' => $questionnaire->getIdFormation(), 
                'questions' => $questionsData
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
    // Route pour supprimer un questionnaire
    #[Route('/questionnaire/{id}', name: 'api_supprimer_questionnaire', methods: ['DELETE'])]
    public function supprimerQuestionnaire(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $questionnaire = $entityManager->getRepository(Evaluation::class)->find($id);

        if (!$questionnaire) {
            return new JsonResponse(['message' => 'Questionnaire non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Supprimer les questions et réponses associées
        foreach ($questionnaire->getQuestions() as $question) {
            foreach ($question->getReponses() as $reponse) {
                $entityManager->remove($reponse);
            }
            $entityManager->remove($question);
        }
        // Supprimer le questionnaire
        $entityManager->remove($questionnaire);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Questionnaire supprimé avec succès'], JsonResponse::HTTP_OK);
    }

}
