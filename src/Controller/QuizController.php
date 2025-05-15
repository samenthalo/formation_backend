<?php
// src/Controller/Api/QuizController.php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'api_lister_quiz', methods: ['GET'])]
    public function listerQuiz(EntityManagerInterface $entityManager): JsonResponse
    {
        $quizRepository = $entityManager->getRepository(Evaluation::class);
        $quizList = $quizRepository->findBy(['type' => 'quiz']);

        $data = [];

        foreach ($quizList as $quiz) {
            $questionsData = [];
            foreach ($quiz->getQuestions() as $question) {
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
                'id' => $quiz->getId(),
                'titre' => $quiz->getTitre(),
                'description' => $quiz->getDescription(),
                'tauxReussite' => $quiz->getTauxReussite(),
                'type' => $quiz->getType(),
                'questions' => $questionsData
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    #[Route('/quiz', name: 'api_creer_quiz', methods: ['POST'])]
    public function creerQuiz(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les données envoyées via JSON
        $data = json_decode($request->getContent(), true); // Décoder le JSON reçu

        // Vérifier que les données sont valides
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Extraire les données spécifiques
        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $tauxReussite = $data['tauxReussite'] ?? null;
        $type = $data['type'] ?? null;
        $questionsData = $data['questions'] ?? null; // Tableau des questions envoyées en JSON

        // Vérifier que toutes les données nécessaires sont présentes
        if (!$titre || !$description || !$tauxReussite || !$type || !$questionsData) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier que le type est bien "quiz"
        if ($type !== 'quiz') {
            return new JsonResponse(['message' => 'Type doit être "quiz"'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Créer une nouvelle instance de Evaluation (quiz)
        $quiz = new Evaluation();
        $quiz->setTitre($titre);
        $quiz->setDescription($description);
        $quiz->setTauxReussite((float) $tauxReussite);
        $quiz->setType($type);

        // Enregistrer l'entité Evaluation dans la base de données
        $entityManager->persist($quiz);
        $entityManager->flush();

        // Ajouter les questions et réponses
        foreach ($questionsData as $index => $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($quiz); // Lier la question à l'évaluation (quiz)

            // Enregistrer la question
            $entityManager->persist($question);
            $entityManager->flush();  // Persist après chaque question pour avoir un ID pour les réponses

            // Ajouter les réponses associées à cette question
            if (isset($questionData['reponses'])) {
                foreach ($questionData['reponses'] as $reponseData) {
                    $reponse = new Reponse();
                    $reponse->setContenu($reponseData['libelle']);
                    $reponse->setEstCorrect($reponseData['correct']);
                    $reponse->setNote($reponseData['note']);
                    $reponse->setQuestion($question); // Lier la réponse à la question

                    // Enregistrer la réponse
                    $entityManager->persist($reponse);
                }
            }
        }

        // Enregistrer les réponses dans la base de données
        $entityManager->flush();

        // Retourner une réponse JSON indiquant le succès de l'enregistrement
        return new JsonResponse(['message' => 'Quiz créé avec succès', 'id' => $quiz->getId()], JsonResponse::HTTP_CREATED);
    }
}
