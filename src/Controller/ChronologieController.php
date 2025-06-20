<?php

namespace App\Controller;

use App\Entity\Chronologie;
use App\Repository\ChronologieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/chronologie')]
class ChronologieController extends AbstractController
{
    #[Route('/', name: 'chronologie_index', methods: ['GET'])]
    public function index(Request $request, ChronologieRepository $chronologieRepository): Response
    {
        // Récupérer le paramètre de requête id_session
        $idSession = $request->query->get('id_session');
    
        // Récupérer les chronologies en fonction de l'id_session
        if ($idSession) {
            $chronologies = $chronologieRepository->findBy(['id_session' => $idSession]); // Utilisez id_session au lieu de idSession
        } else {
            $chronologies = $chronologieRepository->findAll();
        }
    
        // Créer un tableau pour stocker les données des chronologies
        $data = [];
    
        // Parcourir les chronologies et extraire les données avec les getters
        foreach ($chronologies as $chronologie) {
            $data[] = [
                'id' => $chronologie->getId(),
                'idSession' => $chronologie->getIdSession(),
                'dateEvenement' => $chronologie->getDateEvenement()->format('Y-m-d H:i:s'),
                'typeEvenement' => $chronologie->getTypeEvenement(),
                'description' => $chronologie->getDescription(),
            ];
        }
    
        // Retourner les chronologies sous forme de JSON
        return new JsonResponse($data, Response::HTTP_OK);
    }
    

    #[Route('/', name: 'chronologie_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier les champs obligatoires
            if (!isset($data['idSession'], $data['dateEvenement'], $data['typeEvenement'])) {
                return new JsonResponse(['error' => 'Champs obligatoires manquants'], Response::HTTP_BAD_REQUEST);
            }

            $chronologie = new Chronologie();
            $chronologie->setIdSession($data['idSession']);

            try {
                $chronologie->setDateEvenement(new \DateTime($data['dateEvenement']));
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Format de date invalide'], Response::HTTP_BAD_REQUEST);
            }

            $chronologie->setTypeEvenement($data['typeEvenement']);
            $chronologie->setDescription($data['description'] ?? null);

            $entityManager->persist($chronologie);
            $entityManager->flush();

            return new JsonResponse(['message' => 'Chronologie créée', 'id' => $chronologie->getId()], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Log pour debug
            error_log('Erreur création chronologie: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    // Afficher une chronologie spécifique
    #[Route('/{id}', name: 'chronologie_show', methods: ['GET'])]
    public function show(int $id, ChronologieRepository $chronologieRepository): Response
    {
        // Récupérer la chronologie par son ID
        $chronologie = $chronologieRepository->find($id);
        if (!$chronologie) {
            return new JsonResponse(['message' => 'Chronologie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Retourner la chronologie en JSON
        return new JsonResponse($chronologie, Response::HTTP_OK);
    }

    // Mettre à jour une chronologie existante
    #[Route('/{id}', name: 'chronologie_update', methods: ['PUT'])]
    public function update(int $id, Request $request, ChronologieRepository $chronologieRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la chronologie par son ID
        $chronologie = $chronologieRepository->find($id);
        if (!$chronologie) {
            return new JsonResponse(['message' => 'Chronologie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Récupérer les données de la requête
        $data = json_decode($request->getContent(), true);

        // Mettre à jour les champs de la chronologie
        $chronologie->setIdSession($data['idSession']);
        $chronologie->setDateEvenement(new \DateTime($data['dateEvenement']));
        $chronologie->setTypeEvenement($data['typeEvenement']);
        $chronologie->setDescription($data['description'] ?? null);

        // Sauvegarder les modifications dans la base de données
        $entityManager->flush();

        // Retourner une réponse de succès
        return new JsonResponse(['message' => 'Chronologie mise à jour'], Response::HTTP_OK);
    }

    // Supprimer une chronologie
    #[Route('/{id}', name: 'chronologie_delete', methods: ['DELETE'])]
    public function delete(int $id, ChronologieRepository $chronologieRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la chronologie par son ID
        $chronologie = $chronologieRepository->find($id);
        if (!$chronologie) {
            return new JsonResponse(['message' => 'Chronologie non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer la chronologie de la base de données
        $entityManager->remove($chronologie);
        $entityManager->flush();

        // Retourner une réponse de succès
        return new JsonResponse(['message' => 'Chronologie supprimée'], Response::HTTP_OK);
    }
}
