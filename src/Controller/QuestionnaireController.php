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

        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $tauxReussite = $data['tauxReussite'] ?? null;
        $type = $data['type'] ?? null;
        $questionsData = $data['questions'] ?? null;

        if (!$titre || !$description || !$tauxReussite || !$type || !$questionsData) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Créer une instance de Evaluation (qui représente aussi un questionnaire ici)
        $questionnaire = new Evaluation();
        $questionnaire->setTitre($titre);
        $questionnaire->setDescription($description);
        $questionnaire->setTauxReussite((float) $tauxReussite);
        $questionnaire->setType($type); // Ex: 'questionnaire' au lieu de 'quiz'

        $entityManager->persist($questionnaire);
        $entityManager->flush();

        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($questionnaire);

            $entityManager->persist($question);
            $entityManager->flush();

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

    #[Route('/questionnaire', name: 'api_lister_questionnaire', methods: ['GET'])]
    public function listerQuestionnaire(EntityManagerInterface $entityManager): JsonResponse
    {
        $questionnaireRepository = $entityManager->getRepository(Evaluation::class);
        $questionnaireList = $questionnaireRepository->findBy(['type' => 'questionnaire']);

        $data = [];

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

            $data[] = [
                'id' => $questionnaire->getId(),
                'titre' => $questionnaire->getTitre(),
                'description' => $questionnaire->getDescription(),
                'tauxReussite' => $questionnaire->getTauxReussite(),
                'type' => $questionnaire->getType(),
                'questions' => $questionsData
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
