<?php

// src/Controller/SessionFormationController.php

namespace App\Controller;

use App\Entity\SessionFormation;
use App\Repository\SessionFormationRepository;
use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\FormateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\SessionCreneau;
use Psr\Log\LoggerInterface;

class SessionFormationController extends AbstractController
{
    // Route pour afficher toutes les sessions en JSON
    #[Route('/sessionformation', name: 'test_sessionformation_all_json')]
    public function index(SessionFormationRepository $sessionFormationRepository): JsonResponse
    {
        $sessions = $sessionFormationRepository->findAllSessions();
        $sessionsData = [];
        
        foreach ($sessions as $session) {
            $formateur = $session->getFormateur();
    
            // Récupérer les créneaux de la session
            $creneauxData = [];
            foreach ($session->getCreneaux() as $creneau) {
                $creneauxData[] = [
                    'id' => $creneau->getId(),
                    'jour' => $creneau->getJour()->format('Y-m-d'),
                    'heure_debut' => $creneau->getHeureDebut()->format('H:i'),
                    'heure_fin' => $creneau->getHeureFin()->format('H:i'),
                    'id_formateur' => $creneau->getFormateur() ? $creneau->getFormateur()->getIdFormateur() : null,
                ];
            }
    
            // Préparer les données de la session
            $sessionsData[] = [
                'id_session' => $session->getIdSession(),
                'titre' => $session->getTitre(),
                'description' => $session->getDescription(),
                'lieu' => $session->getLieu(),
                'nb_heures' => $session->getNbHeures(),
                'statut' => $session->getStatut(),
                'nb_inscrits' => $session->getNbInscrits(),
                'formation' => $session->getFormation()->getIdFormation(),
                'formateur' => $formateur ? [
                    'id_formateur' => $formateur->getIdFormateur(),
                    'nom' => $formateur->getNom(),
                    'prenom' => $formateur->getPrenom(),
                    'email' => $formateur->getEmail(),
                    'telephone' => $formateur->getTelephone(),
                ] : null,
                'responsable_nom' => $session->getResponsableNom(),
                'responsable_prenom' => $session->getResponsablePrenom(),
                'responsable_telephone' => $session->getResponsableTelephone(),
                'responsable_email' => $session->getResponsableEmail(),
                'mode' => $session->getMode(),
                'lien' => $session->getLien(),
                'creneaux' => $creneauxData,
            ];
        }
    
        return new JsonResponse($sessionsData);
    }
    
    
    // Route pour récupérer les sessions par ID de formation
        #[Route('/sessionformation/formation/{formationId}', name: 'get_sessions_by_formation', methods: ['GET'])]
        public function getSessionsByFormation(
            int $formationId,
            SessionFormationRepository $sessionFormationRepository
        ): JsonResponse {
            $sessions = $sessionFormationRepository->findByFormationId($formationId); // Appeler ta méthode findByFormationId
            
            if (empty($sessions)) {
                return new JsonResponse(['error' => 'No sessions found for this formation'], Response::HTTP_NOT_FOUND);
            }

            $sessionsData = [];
            // Parcourir les sessions et préparer les données
            foreach ($sessions as $session) {
                $formateur = $session->getFormateur();

                $sessionsData[] = [
                    'id_session' => $session->getIdSession(),
                    'titre' => $session->getTitre(),
                    'description' => $session->getDescription(),
                    'lieu' => $session->getLieu(),
                    'nb_heures' => $session->getNbHeures(),
                    'statut' => $session->getStatut(),
                    'nb_inscrits' => $session->getNbInscrits(),
                    'formation' => $session->getFormation()->getIdFormation(),
                    'formateur' => $formateur ? [
                        'id_formateur' => $formateur->getIdFormateur(),
                        'nom' => $formateur->getNom(),
                        'prenom' => $formateur->getPrenom(),
                    ] : null,
                ];
            }

            return new JsonResponse($sessionsData);
        }
    
    #[Route('/sessionformation/create', name: 'create_sessionformation', methods: ['POST'])]
    public function create(
        Request $request,
        FormationRepository $formationRepository,
        FormateurRepository $formateurRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = $request->request->all();

        // Créer une nouvelle session
        $session = new SessionFormation();
        $session->setTitre($data['titre'] ?? null);
        $session->setDescription($data['description'] ?? null);
        $session->setLieu($data['lieu'] ?? null);
        $session->setNbHeures($data['nb_heures'] ?? null);

        // Statut par défaut à "créée" si non fourni
        $session->setStatut($data['statut'] ?? 'créée');
        $session->setNbInscrits($data['nb_inscrits'] ?? 0);

        // Nouveau : Mode et Lien
        $session->setMode($data['mode'] ?? null); // "présentiel" ou "distanciel"
        $session->setLien($data['lien'] ?? null); // lien visio si distanciel

        // Champs pour le responsable de formation
        $session->setResponsableNom($data['responsable_nom'] ?? null);
        $session->setResponsablePrenom($data['responsable_prenom'] ?? null);
        $session->setResponsableTelephone($data['responsable_telephone'] ?? null);
        $session->setResponsableEmail($data['responsable_email'] ?? null);

        // Lier formation si fournie
        if (!empty($data['formation'])) {
            $formation = $formationRepository->find($data['formation']);
            if ($formation) {
                $session->setFormation($formation);
            }
        }

        // Lier formateur si fourni pour la session (formateur par défaut)
        if (!empty($data['formateur'])) {
            $formateur = $formateurRepository->find($data['formateur']);
            if ($formateur) {
                $session->setFormateur($formateur);
            }
        }

        $entityManager->persist($session);
        $entityManager->flush();

        // Ajouter les créneaux
        if (!empty($data['creneaux'])) {
            foreach ($data['creneaux'] as $creneauData) {
                $creneau = new SessionCreneau();
                $creneau->setSessionFormation($session);
                $creneau->setJour(new \DateTime($creneauData['jour']));
                $creneau->setHeureDebut(new \DateTime($creneauData['heure_debut']));
                $creneau->setHeureFin(new \DateTime($creneauData['heure_fin']));

                // Si un formateur spécifique est fourni pour ce créneau, on l'ajoute
                if (!empty($creneauData['id_formateur'])) {
                    $formateurCreneau = $formateurRepository->find($creneauData['id_formateur']);
                    if ($formateurCreneau) {
                        $creneau->setFormateur($formateurCreneau);
                    }
                } else {
                    // Sinon, utiliser le formateur par défaut de la session
                    if ($session->getFormateur()) {
                        $creneau->setFormateur($session->getFormateur());
                    }
                }

                $entityManager->persist($creneau);
            }
            $entityManager->flush();
        }

        return new JsonResponse([
            'status' => 'Session créée',
            'id' => $session->getIdSession()
        ], Response::HTTP_CREATED);
    }

    
    // Route pour mettre à jour une session de formation (utilise POST pour la mise à jour)
    #[Route('/sessionformation/{id}', name: 'update_sessionformation', methods: ['POST', 'PUT'])]
    public function update(Request $request, SessionFormationRepository $sessionFormationRepository, int $id, EntityManagerInterface $entityManager, FormateurRepository $formateurRepository, LoggerInterface $logger): JsonResponse
    {
        $data = $request->request->all(); // Récupérer les données en form-data
        // Log des données reçues
        $logger->info('Data received:', $data);

        $session = $sessionFormationRepository->find($id);

        if (!$session) {
            $logger->error('Session not found for ID: ' . $id);
            return new JsonResponse(['error' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }

        // Mettre à jour les propriétés de la session
        $session->setTitre($data['titre'] ?? $session->getTitre());
        $session->setDescription($data['description'] ?? $session->getDescription());
        $session->setLieu($data['lieu'] ?? $session->getLieu());
        $session->setNbHeures($data['nb_heures'] ?? $session->getNbHeures());
        $session->setStatut($data['statut'] ?? $session->getStatut());
        $session->setNbInscrits($data['nb_inscrits'] ?? $session->getNbInscrits());
        $session->setMode($data['mode'] ?? $session->getMode());
        $session->setLien($data['lien'] ?? $session->getLien());

        // Responsable (nom, prénom, téléphone, email)
        $session->setResponsableNom($data['responsable_nom'] ?? $session->getResponsableNom());
        $session->setResponsablePrenom($data['responsable_prenom'] ?? $session->getResponsablePrenom());
        $session->setResponsableTelephone($data['responsable_telephone'] ?? $session->getResponsableTelephone());
        $session->setResponsableEmail($data['responsable_email'] ?? $session->getResponsableEmail());

        // Gérer les créneaux
        if (!empty($data['creneaux'])) {
            // Supprimer les anciens créneaux (si nécessaire)
            foreach ($session->getCreneaux() as $creneau) {
                $entityManager->remove($creneau);
            }
            // Ajouter les nouveaux créneaux
            foreach ($data['creneaux'] as $creneauData) {
                $creneau = new SessionCreneau();
                $creneau->setSessionFormation($session);
                $creneau->setJour(new \DateTime($creneauData['jour']));
                $creneau->setHeureDebut(new \DateTime($creneauData['heure_debut']));
                $creneau->setHeureFin(new \DateTime($creneauData['heure_fin']));

                // Si un formateur spécifique est fourni pour ce créneau, on l'ajoute
                if (!empty($creneauData['id_formateur'])) {
                    $formateurCreneau = $formateurRepository->find($creneauData['id_formateur']);
                    if ($formateurCreneau) {
                        $creneau->setFormateur($formateurCreneau);
                    } else {
                        $logger->error('Formateur not found for ID: ' . $creneauData['id_formateur']);
                    }
                } else {
                    // Sinon, utiliser le formateur par défaut de la session
                    if ($session->getFormateur()) {
                        $creneau->setFormateur($session->getFormateur());
                    }
                }

                $entityManager->persist($creneau);
            }
        }

        // Sauvegarder les modifications de la session et des créneaux
        $entityManager->flush();

        return new JsonResponse(['status' => 'Session updated']);
    }

    // Route pour supprimer une session de formation
    #[Route('/sessionformation/delete/{id}', name: 'delete_sessionformation', methods: ['DELETE'])]
    public function delete(
        int $id,
        SessionFormationRepository $sessionFormationRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $session = $sessionFormationRepository->find($id);
    
        if (!$session) {
            return new JsonResponse(['error' => 'Session not found'], Response::HTTP_NOT_FOUND);
        }
    
        // Supprimer les créneaux associés à la session
        foreach ($session->getCreneaux() as $creneau) {
            $entityManager->remove($creneau);
        }
    
        // Supprimer la session
        $entityManager->remove($session);
        $entityManager->flush();
    
        return new JsonResponse(['status' => 'Session supprimée avec succès'], Response::HTTP_OK);
    }
    
}


