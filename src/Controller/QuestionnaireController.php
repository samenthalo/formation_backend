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
use App\Repository\ReponseUtilisateurRepository;
use App\Repository\StagiaireRepository;
use App\Repository\QuestionRepository;
use App\Entity\EvaluationStagiaire;

class QuestionnaireController extends AbstractController
{
    // Route pour créer un nouveau questionnaire
    #[Route('/questionnaire', name: 'api_creer_questionnaire', methods: ['POST'])]
    public function creerQuestionnaire(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Vérification des champs obligatoires
        $titre = $data['titre'] ?? null;
        $description = $data['description'] ?? null;
        $tauxReussite = $data['tauxReussite'] ?? null;
        $type = $data['type'] ?? null;
        $idFormation = $data['id_formation'] ?? null;
        $questionsData = $data['questions'] ?? null;

        if (!$titre || !$description || !$tauxReussite || !$type || !$idFormation || !$questionsData) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Création d'une nouvelle instance de Evaluation
        $questionnaire = new Evaluation();
        $questionnaire->setTitre($titre);
        $questionnaire->setDescription($description);
        $questionnaire->setTauxReussite((float) $tauxReussite);
        $questionnaire->setType($type);
        $questionnaire->setIdFormation($idFormation);

        $entityManager->persist($questionnaire);
        $entityManager->flush();

        // Ajout des questions et réponses associées
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

    // Route pour modifier un questionnaire existant
    #[Route('/questionnaire/{id}', name: 'api_modifier_questionnaire', methods: ['POST'])]
    public function modifierQuestionnaire(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['message' => 'Données JSON invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }

        error_log('Données reçues: ' . print_r($data, true));

        $questionnaire = $entityManager->getRepository(Evaluation::class)->find($id);
        if (!$questionnaire) {
            return new JsonResponse(['message' => 'Questionnaire non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Mise à jour des champs du questionnaire
        $titre = $data['titre'] ?? $questionnaire->getTitre();
        $description = $data['description'] ?? $questionnaire->getDescription();
        $tauxReussite = $data['tauxReussite'] ?? $questionnaire->getTauxReussite();
        $type = $data['type'] ?? $questionnaire->getType();
        $idFormation = $data['id_formation'] ?? $questionnaire->getIdFormation();

        error_log('Type reçu: ' . $type);

        $questionnaire->setTitre($titre);
        $questionnaire->setDescription($description);
        $questionnaire->setTauxReussite((float) $tauxReussite);
        $questionnaire->setType($type);
        $questionnaire->setIdFormation($idFormation);

        $entityManager->persist($questionnaire);

        // Suppression des questions existantes avant d'ajouter les nouvelles
        foreach ($questionnaire->getQuestions() as $question) {
            $entityManager->remove($question);
        }

        // Ajout des nouvelles questions et réponses
        $questionsData = $data['questions'] ?? [];
        foreach ($questionsData as $questionData) {
            $question = new Question();
            $question->setContenu($questionData['contenu']);
            $question->setType($questionData['type']);
            $question->setEvaluation($questionnaire);
            $entityManager->persist($question);

            if (isset($questionData['reponses'])) {
                foreach ($questionData['reponses'] as $reponseData) {
                    $reponse = new Reponse();
                    $reponse->setContenu($reponseData['libelle']);
                    $reponse->setEstCorrect($reponseData['correct'] ?? false);
                    $reponse->setNote($reponseData['note'] ?? 0);
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

        // Suppression des questions et réponses associées
        foreach ($questionnaire->getQuestions() as $question) {
            foreach ($question->getReponses() as $reponse) {
                $entityManager->remove($reponse);
            }
            $entityManager->remove($question);
        }

        // Suppression du questionnaire
        $entityManager->remove($questionnaire);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Questionnaire supprimé avec succès'], JsonResponse::HTTP_OK);
    }

    // Route pour dupliquer un questionnaire
    #[Route('/questionnaire/duplicate/{id}', name: 'api_dupliquer_questionnaire', methods: ['POST'])]
    public function dupliquerQuestionnaire(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $questionnaireOriginal = $entityManager->getRepository(Evaluation::class)->find($id);
        if (!$questionnaireOriginal) {
            return new JsonResponse(['message' => 'Questionnaire non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Création d'une copie du questionnaire
        $questionnaireCopie = new Evaluation();
        $questionnaireCopie->setTitre($questionnaireOriginal->getTitre() . ' (Copie)');
        $questionnaireCopie->setDescription($questionnaireOriginal->getDescription());
        $questionnaireCopie->setTauxReussite($questionnaireOriginal->getTauxReussite());
        $questionnaireCopie->setType($questionnaireOriginal->getType());
        $questionnaireCopie->setIdFormation($questionnaireOriginal->getIdFormation());

        $entityManager->persist($questionnaireCopie);

        // Duplication des questions et réponses associées
        foreach ($questionnaireOriginal->getQuestions() as $questionOriginal) {
            $questionCopie = new Question();
            $questionCopie->setContenu($questionOriginal->getContenu());
            $questionCopie->setType($questionOriginal->getType());
            $questionCopie->setEvaluation($questionnaireCopie);
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

        return new JsonResponse(['message' => 'Questionnaire dupliqué avec succès', 'id' => $questionnaireCopie->getId()], JsonResponse::HTTP_CREATED);
    }

    // Route pour voir les réponses d'un questionnaire
    #[Route('/questionnaire/{id}/reponses', name: 'api_questionnaire_reponses', methods: ['GET'])]
    public function voirReponsesQuestionnaire(
        int $id,
        ReponseUtilisateurRepository $repo,
        StagiaireRepository $stagiaireRepo,
        QuestionRepository $questionRepo
    ): JsonResponse {
        $reponses = $repo->findByQuestionnaire($id);
        $data = [];

        foreach ($reponses as $questionData) {
            $stagiaire = $stagiaireRepo->find($questionData['stagiaireId']);
            $question = $questionRepo->find($questionData['questionId']);

            $dateReponse = null;
            if (!empty($questionData['dateReponse'])) {
                if ($questionData['dateReponse'] instanceof \DateTimeInterface) {
                    $dateReponse = $questionData['dateReponse'];
                } else {
                    $dateReponse = new \DateTime($questionData['dateReponse']);
                }
            }

            $reponseChoisie = null;
            $idReponseChoisie = null;
            $reponsesPossibles = [];

            if (!empty($questionData['reponseLibre'])) {
                $reponseChoisie = $questionData['reponseLibre'];
            } else {
                $uniqueResponses = [];
                foreach ($questionData['reponsesPossibles'] as $reponsePossible) {
                    $responseId = $reponsePossible['reponsePossibleId'];
                    if (!isset($uniqueResponses[$responseId])) {
                        $uniqueResponses[$responseId] = [
                            'id' => $reponsePossible['reponsePossibleId'],
                            'contenu' => $reponsePossible['reponsePossibleContenu'],
                        ];
                    }
                    if (!empty($reponsePossible['estChoisie']) && $reponsePossible['estChoisie']) {
                        $reponseChoisie = $reponsePossible['reponsePossibleContenu'];
                        $idReponseChoisie = $reponsePossible['reponsePossibleId'];
                    }
                }
                $reponsesPossibles = array_values($uniqueResponses);
            }

            $reponseData = [
                'stagiaire' => $stagiaire ? ($stagiaire->getPrenomStagiaire() . ' ' . $stagiaire->getNomStagiaire()) : null,
                'question' => $question ? $question->getContenu() : null,
                'reponseChoisie' => $reponseChoisie,
                'idReponseChoisie' => $idReponseChoisie,
                'date' => $dateReponse ? $dateReponse->format('Y-m-d H:i:s') : null,
                'reponsesPossibles' => $reponsesPossibles,
            ];

            $data[] = $reponseData;
        }

        return new JsonResponse($data, 200);
    }
}
