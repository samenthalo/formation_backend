<?php
// src/Controller/InscriptionController.php
namespace App\Controller;

use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InscriptionController extends AbstractController
{
    // Route pour récupérer toutes les inscriptions
    #[Route('/inscriptions', name: 'get_inscriptions', methods: ['GET'])]
    public function getInscriptions(InscriptionRepository $inscriptionRepository): JsonResponse
    {
        // Récupérer toutes les inscriptions
        $inscriptions = $inscriptionRepository->findAll();
        $data = [];
        foreach ($inscriptions as $inscription) {
            $data[] = [
                'id_inscription' => $inscription->getIdInscription(),
                'stagiaire' => [
                    'id_stagiaire' => $inscription->getStagiaire()->getIdStagiaire(),
                    'nom_stagiaire' => $inscription->getStagiaire()->getNomStagiaire(),
                    'prenom_stagiaire' => $inscription->getStagiaire()->getPrenomStagiaire(),
                ],
                'session' => [
                    'id_session' => $inscription->getSessionFormation()->getIdSession(),
                    'nom_session' => $inscription->getSessionFormation()->getTitre(),
                ],
                'statut' => $inscription->getStatut(),
            ];
        }
        return new JsonResponse($data);
    }
    // Route pour récupérer les inscriptions par ID de session
    #[Route('/inscriptions/{id_session}', name: 'get_inscriptions_by_session', methods: ['GET'])]
    public function getInscriptionsBySession(int $id_session, InscriptionRepository $inscriptionRepository): JsonResponse
    {
        // Récupérer les inscriptions pour une session spécifique
        $inscriptions = $inscriptionRepository->findBySession($id_session);
        $data = [];
        foreach ($inscriptions as $inscription) {
            $data[] = [
                'id_inscription' => $inscription->getIdInscription(),
                'stagiaire' => [
                    'id_stagiaire' => $inscription->getStagiaire()->getIdStagiaire(),
                    'nom_stagiaire' => $inscription->getStagiaire()->getNomStagiaire(),
                    'prenom_stagiaire' => $inscription->getStagiaire()->getPrenomStagiaire(),
                ],
                'session' => [
                    'id_session' => $inscription->getSessionFormation()->getIdSession(),
                    'nom_session' => $inscription->getSessionFormation()->getTitre(),
                ],
                'statut' => $inscription->getStatut(),
            ];
        }
        return new JsonResponse($data);
    }

    // Route pour ajouter des inscriptions
    #[Route('/inscriptions', name: 'post_inscriptions', methods: ['POST'])]
    public function postInscriptions(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Vérifier si les données sont valides
        if (empty($data)) {
            return new JsonResponse(['error' => 'Données invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }
        // Vérifier si les champs requis sont présents
        foreach ($data as $item) {
            $inscription = new Inscription();
            $stagiaire = $entityManager->getReference('App\Entity\Stagiaire', $item['id']);
            $session = $entityManager->getReference('App\Entity\SessionFormation', $item['id_session']);

            $inscription->setStagiaire($stagiaire);
            $inscription->setSessionFormation($session);
            $inscription->setStatut('inscrit'); // ou tout autre statut par défaut

            $errors = $validator->validate($inscription);
            if (count($errors) > 0) {
                return new JsonResponse(['error' => (string) $errors], JsonResponse::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($inscription);
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'Inscriptions enregistrées avec succès'], JsonResponse::HTTP_CREATED);
    }
    // Route pour supprimer des inscriptions
    #[Route('/inscriptions', name: 'delete_inscriptions', methods: ['DELETE'])]
    public function deleteInscriptions(Request $request, EntityManagerInterface $entityManager, InscriptionRepository $inscriptionRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Vérifier si les données sont valides
        if (empty($data) || !isset($data['ids']) || !isset($data['id_session'])) {
            return new JsonResponse(['error' => 'Données invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }
        // Vérifier si les IDs sont présents
        $ids = $data['ids'];
        $id_session = $data['id_session'];
        // Vérifier si l'ID de session est valide
        foreach ($ids as $id) {
            $inscription = $inscriptionRepository->findOneBy([
                'stagiaire' => $id,
                'sessionFormation' => $id_session
            ]);

            if ($inscription) {
                $entityManager->remove($inscription);
            }
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'Inscriptions supprimées avec succès'], JsonResponse::HTTP_OK);
    }
    // Route pour ajouter une inscription
    #[Route('/inscriptions/add', name: 'add_inscription', methods: ['POST'])]
    public function addInscription(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $id_stagiaire = $request->request->get('id_stagiaire');
        $id_session = $request->request->get('id_session');
    
        if (empty($id_stagiaire) || empty($id_session)) {
            return new JsonResponse(['error' => 'Données invalides'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Vérifiez si l'inscription existe déjà
        $existingInscription = $entityManager->getRepository(Inscription::class)->findOneBy([
            'stagiaire' => $id_stagiaire,
            'sessionFormation' => $id_session
        ]);
    
        if ($existingInscription) {
            return new JsonResponse(['error' => 'Ce stagiaire est déjà inscrit à cette session.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Créez une nouvelle inscription
        $inscription = new Inscription();
        $stagiaire = $entityManager->getReference('App\Entity\Stagiaire', $id_stagiaire);
        $session = $entityManager->getReference('App\Entity\SessionFormation', $id_session);
    
        $inscription->setStagiaire($stagiaire);
        $inscription->setSessionFormation($session); // Utilisez setSessionFormation
        $inscription->setStatut('inscrit'); // ou tout autre statut par défaut
    
        $errors = $validator->validate($inscription);
        if (count($errors) > 0) {
            return new JsonResponse(['error' => (string) $errors], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $entityManager->persist($inscription);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Inscription enregistrée avec succès'], JsonResponse::HTTP_CREATED);
    }
    
    
}
