<?php
namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\Reponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ReponseUtilisateurRepository;
use App\Repository\StagiaireRepository;
use App\Repository\QuestionRepository;
use App\Repository\EvaluationRepository;

class QuizController extends AbstractController
{
    // Route pour lister tous les quiz
    #[Route('/quiz', name: 'api_lister_quiz', methods: ['GET'])]
    public function listerQuiz(EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer tous les quiz
        $quizRepository = $entityManager->getRepository(Evaluation::class);
        $quizList = $quizRepository->findBy(['type' => 'quiz']);
        $data = [];

        // Parcourir chaque quiz et ses questions/réponses
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

            // Préparer les données du quiz
            $data[] = [
                'id' => $quiz->getId(),
                'titre' => $quiz->getTitre(),
                'description' => $quiz->getDescription(),
                'tauxReussite' => $quiz->getTauxReussite(),
                'type' => $quiz->getType(),
                'id_formation' => $quiz->getIdFormation(),
                'questions' => $questionsData
            ];
        }

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    // Route pour créer un nouveau quiz
    #[Route('/quiz', name: 'api_creer_quiz', methods: ['POST'])]
    public function creerQuiz(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer et décoder les données JSON envoyées
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Extraire les données spécifiques
        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $tauxReussite = $data['tauxReussite'] ?? null;
        $type = $data['type'] ?? null;
        $idFormation = $data['id_formation'] ?? null;
        $questionsData = $data['questions'] ?? null;

        // Vérifier que toutes les données nécessaires sont présentes
        if (!$titre || !$description || !$tauxReussite || !$type || !$idFormation || !$questionsData) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier que le type est bien "quiz"
        if ($type !== 'quiz') {
            return new JsonResponse(['message' => 'Type doit être "quiz"'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Créer une nouvelle instance de Quiz
        $quiz = new Evaluation();
        $quiz->setTitre($titre);
        $quiz->setDescription($description);
        $quiz->setTauxReussite((float) $tauxReussite);
        $quiz->setType($type);
        $quiz->setIdFormation($idFormation);

        // Enregistrer l'entité Quiz dans la base de données
        $entityManager->persist($quiz);
        $entityManager->flush();

        // Ajouter les questions et réponses
        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($quiz);

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

        return new JsonResponse(['message' => 'Quiz créé avec succès', 'id' => $quiz->getId()], JsonResponse::HTTP_CREATED);
    }

    // Route pour mettre à jour un quiz
    #[Route('/quiz/{id}', name: 'api_mettre_a_jour_quiz', methods: ['POST'])]
    public function mettreAJourQuiz(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer le quiz par son ID
        $quiz = $entityManager->getRepository(Evaluation::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['message' => 'Quiz non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Récupérer et décoder les données JSON envoyées
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Extraire les données spécifiques
        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $tauxReussite = $data['tauxReussite'] ?? null;
        $type = $data['type'] ?? null;
        $idFormation = $data['id_formation'] ?? null;
        $questionsData = $data['questions'] ?? null;

        // Vérifier que toutes les données nécessaires sont présentes
        if (!$titre || !$description || !$tauxReussite || !$type || !$idFormation || !$questionsData) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérifier que le type est bien "quiz"
        if ($type !== 'quiz') {
            return new JsonResponse(['message' => 'Type doit être "quiz"'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Mettre à jour les champs du quiz
        $quiz->setTitre($titre);
        $quiz->setDescription($description);
        $quiz->setTauxReussite((float) $tauxReussite);
        $quiz->setType($type);
        $quiz->setIdFormation($idFormation);

        // Supprimer les questions et réponses existantes
        foreach ($quiz->getQuestions() as $question) {
            foreach ($question->getReponses() as $reponse) {
                $entityManager->remove($reponse);
            }
            $entityManager->remove($question);
        }

        // Ajouter les nouvelles questions et réponses
        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($quiz);

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

        return new JsonResponse(['message' => 'Quiz mis à jour avec succès', 'id' => $quiz->getId()], JsonResponse::HTTP_OK);
    }

    // Route pour supprimer un quiz
    #[Route('/quiz/{id}', name: 'api_supprimer_quiz', methods: ['DELETE'])]
    public function supprimerQuiz(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer le quiz par son ID
        $quiz = $entityManager->getRepository(Evaluation::class)->find($id);
        if (!$quiz) {
            return new JsonResponse(['message' => 'Quiz non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($quiz);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz supprimé avec succès'], JsonResponse::HTTP_OK);
    }

    // Route pour dupliquer un quiz
    #[Route('/quiz/duplicate/{id}', name: 'api_dupliquer_quiz', methods: ['POST'])]
    public function dupliquerQuiz(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer le quiz original par son ID
        $quizOriginal = $entityManager->getRepository(Evaluation::class)->find($id);
        if (!$quizOriginal) {
            return new JsonResponse(['message' => 'Quiz non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Créer une nouvelle instance de Quiz pour la copie
        $quizCopie = new Evaluation();
        $quizCopie->setTitre($quizOriginal->getTitre() . ' (Copie)');
        $quizCopie->setDescription($quizOriginal->getDescription());
        $quizCopie->setTauxReussite($quizOriginal->getTauxReussite());
        $quizCopie->setType($quizOriginal->getType());
        $quizCopie->setIdFormation($quizOriginal->getIdFormation());

        $entityManager->persist($quizCopie);

        // Dupliquer les questions et réponses associées
        foreach ($quizOriginal->getQuestions() as $questionOriginal) {
            $questionCopie = new Question();
            $questionCopie->setContenu($questionOriginal->getContenu());
            $questionCopie->setType($questionOriginal->getType());
            $questionCopie->setEvaluation($quizCopie);

            $entityManager->persist($questionCopie);

            foreach ($questionOriginal->getReponses() as $reponseOriginal) {
                $reponseCopie = new Reponse();
                $reponseCopie->setContenu($reponseOriginal->getContenu());
                $reponseCopie->setEstCorrect($reponseOriginal->isEstCorrect());
                $reponseCopie->setNote($reponseOriginal->getNote());
                $reponseCopie->setQuestion($questionCopie);

                $entityManager->persist($reponseCopie);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz dupliqué avec succès', 'id' => $quizCopie->getId()], JsonResponse::HTTP_CREATED);
    }

    // Route pour voir les réponses d'un quiz
    #[Route('/quiz/{id}/reponses', name: 'api_quiz_reponses', methods: ['GET'])]
    public function voirReponsesQuiz(
        int $id,
        ReponseUtilisateurRepository $repo,
        StagiaireRepository $stagiaireRepo,
        QuestionRepository $questionRepo
    ): JsonResponse {
        $reponses = $repo->findByQuiz($id);
        $result = [];

        foreach ($reponses as $reponseData) {
            $stagiaireId = $reponseData['stagiaireId'] ?? null;
            $questionId = $reponseData['questionId'] ?? null;

            if ($stagiaireId === null || $questionId === null) {
                continue;
            }

            $stagiaire = $stagiaireRepo->find($stagiaireId);
            $question = $questionRepo->find($questionId);

            $dateReponse = null;
            if (!empty($reponseData['dateReponse'])) {
                try {
                    $dateReponse = $reponseData['dateReponse'] instanceof \DateTimeInterface
                        ? $reponseData['dateReponse']
                        : new \DateTime($reponseData['dateReponse']);
                } catch (\Exception $e) {
                    $dateReponse = null;
                }
            }

            $reponseEntry = [
                'stagiaire' => $stagiaire ? ($stagiaire->getPrenomStagiaire() . ' ' . $stagiaire->getNomStagiaire()) : null,
                'question' => $question ? $question->getContenu() : null,
                'reponse' => null,
                'id_reponse' => $reponseData['id_reponse_utilisateur'] ?? null,
                'date' => $dateReponse ? $dateReponse->format('Y-m-d H:i:s') : null,
                'score' => $reponseData['score'] ?? null,
                'estCorrecte' => false,
                'reponsesPossibles' => [],
            ];

            foreach ($reponseData['reponsesPossibles'] ?? [] as $reponsePossible) {
                $estChoisie = $reponsePossible['estChoisie'] ?? false;
                $reponseEntry['reponsesPossibles'][] = [
                    'contenu' => $reponsePossible['reponseContenu'] ?? null,
                    'estChoisie' => $estChoisie,
                    'estCorrecte' => $reponsePossible['estCorrecte'] ?? false,
                    'note' => $reponsePossible['note'] ?? null,
                ];

                if ($estChoisie) {
                    $reponseEntry['reponse'] = $reponsePossible['reponseContenu'];
                    $reponseEntry['estCorrecte'] = $reponsePossible['estCorrecte'] ?? false;
                }
            }

            $result[] = $reponseEntry;
        }

        return new JsonResponse($result, 200);
    }
}
