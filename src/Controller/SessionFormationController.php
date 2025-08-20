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
use App\Entity\Formateur;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Service\SessionStatusUpdater;

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
        $sessions = $sessionFormationRepository->findByFormationId($formationId);

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

    // Route pour créer une nouvelle session de formation
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
        $session->setStatut($data['statut'] ?? 'créée');
        $session->setNbInscrits($data['nb_inscrits'] ?? 0);
        $session->setMode($data['mode'] ?? null);
        $session->setLien($data['lien'] ?? null);
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

        // Lier formateur si fourni pour la session
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

    // Route pour mettre à jour une session de formation
 #[Route('/sessionformation/{id}', name: 'update_sessionformation', methods: ['POST', 'PUT'])]
public function update(
    Request $request,
    SessionFormationRepository $sessionFormationRepository,
    int $id,
    EntityManagerInterface $entityManager,
    FormateurRepository $formateurRepository,
    LoggerInterface $logger
): JsonResponse {
    $data = $request->request->all();
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
    $session->setResponsableNom($data['responsable_nom'] ?? $session->getResponsableNom());
    $session->setResponsablePrenom($data['responsable_prenom'] ?? $session->getResponsablePrenom());
    $session->setResponsableTelephone($data['responsable_telephone'] ?? $session->getResponsableTelephone());
    $session->setResponsableEmail($data['responsable_email'] ?? $session->getResponsableEmail());

    // ✅ Mettre à jour le formateur par défaut si fourni
    if (!empty($data['id_formateur'])) {
        $formateur = $formateurRepository->find($data['id_formateur']);
        if ($formateur) {
            $session->setFormateur($formateur);
        } else {
            $logger->error('Formateur par défaut non trouvé pour ID: ' . $data['id_formateur']);
        }
    }

    // Gérer les créneaux
    if (!empty($data['creneaux'])) {
        // Supprimer les anciens créneaux
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

    // Sauvegarder les modifications
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

    // Route pour récupérer les sessions avec créneaux pour un formateur
    #[Route('/formateur/{id}/sessions', name: 'formateur_sessions', methods: ['GET'])]
    public function getSessionsWithCreneaux(int $id, SessionFormationRepository $repo): JsonResponse
    {
        $sessions = $repo->findBy(['formateur' => $id]);
        $result = [];

        foreach ($sessions as $session) {
            $creneauxData = [];
            foreach ($session->getCreneaux() as $creneau) {
                $creneauxData[] = [
                    'jour' => $creneau->getJour()?->format('Y-m-d'),
                    'heure_debut' => $creneau->getHeureDebut()?->format('H:i'),
                    'heure_fin' => $creneau->getHeureFin()?->format('H:i'),
                ];
            }

            $result[] = [
                'id' => $session->getIdSession(),
                'titre' => $session->getTitre(),
                'statut' => $session->getStatut(),
                'lieu' => $session->getLieu(),
                'mode' => $session->getMode(),
                'nb_heures' => $session->getNbHeures(),
                'nb_inscrits' => $session->getNbInscrits(),
                'responsable' => [
                    'nom' => $session->getResponsableNom(),
                    'prenom' => $session->getResponsablePrenom(),
                    'telephone' => $session->getResponsableTelephone(),
                    'email' => $session->getResponsableEmail(),
                ],
                'creneaux' => $creneauxData,
            ];
        }

        return new JsonResponse($result);
    }

    // Route pour récupérer l'historique des sessions pour un formateur
    #[Route('/formateur/{id}/sessions/historique', name: 'formateur_sessions_historique', methods: ['GET'])]
    public function getHistoriqueSessions(int $id, SessionFormationRepository $repo): JsonResponse
    {
        $sessions = $repo->findBy([
            'formateur' => $id,
            'statut' => 'terminée'
        ]);

        $result = [];
        foreach ($sessions as $session) {
            // Créneaux
            $creneauxData = [];
            foreach ($session->getCreneaux() as $creneau) {
                $creneauxData[] = [
                    'jour' => $creneau->getJour()?->format('Y-m-d'),
                    'heure_debut' => $creneau->getHeureDebut()?->format('H:i'),
                    'heure_fin' => $creneau->getHeureFin()?->format('H:i'),
                ];
            }

            // Participants (stagiaires)
            $participantsData = [];
            foreach ($session->getInscriptions() as $inscription) {
                $stagiaire = $inscription->getStagiaire();
                $participantsData[] = [
                    'id' => $stagiaire->getIdStagiaire(),
                    'nom' => $stagiaire->getNomStagiaire(),
                    'prenom' => $stagiaire->getPrenomStagiaire(),
                    'email' => $stagiaire->getEmailStagiaire(),
                    'telephone' => $stagiaire->getTelephoneStagiaire(),
                    'entreprise' => $stagiaire->getEntrepriseStagiaire(),
                    'fonction' => $stagiaire->getFonctionStagiaire(),
                ];
            }

            $result[] = [
                'id' => $session->getIdSession(),
                'titre' => $session->getTitre(),
                'statut' => $session->getStatut(),
                'lieu' => $session->getLieu(),
                'mode' => $session->getMode(),
                'nb_heures' => $session->getNbHeures(),
                'nb_inscrits' => $session->getNbInscrits(),
                'responsable' => [
                    'nom' => $session->getResponsableNom(),
                    'prenom' => $session->getResponsablePrenom(),
                    'telephone' => $session->getResponsableTelephone(),
                    'email' => $session->getResponsableEmail(),
                ],
                'creneaux' => $creneauxData,
                'participants' => $participantsData,
            ];
        }

        return new JsonResponse($result);
    }

    // Route pour récupérer les sessions à venir pour un formateur
    #[Route('/formateur/{id}/sessions/a-venir', name: 'formateur_sessions_a_venir', methods: ['GET'])]
    public function getSessionsAVenir(int $id, SessionFormationRepository $repo): JsonResponse
    {
        $sessions = $repo->createQueryBuilder('s')
            ->where('s.formateur = :id')
            ->andWhere('s.statut != :statut')
            ->setParameter('id', $id)
            ->setParameter('statut', 'terminée')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($sessions as $session) {
            // Créneaux
            $creneauxData = [];
            foreach ($session->getCreneaux() as $creneau) {
                $creneauxData[] = [
                    'jour' => $creneau->getJour()?->format('Y-m-d'),
                    'heure_debut' => $creneau->getHeureDebut()?->format('H:i'),
                    'heure_fin' => $creneau->getHeureFin()?->format('H:i'),
                ];
            }

            // Participants (stagiaires)
            $participantsData = [];
            foreach ($session->getInscriptions() as $inscription) {
                $stagiaire = $inscription->getStagiaire();
                $participantsData[] = [
                    'id' => $stagiaire->getIdStagiaire(),
                    'nom' => $stagiaire->getNomStagiaire(),
                    'prenom' => $stagiaire->getPrenomStagiaire(),
                    'email' => $stagiaire->getEmailStagiaire(),
                    'telephone' => $stagiaire->getTelephoneStagiaire(),
                    'entreprise' => $stagiaire->getEntrepriseStagiaire(),
                    'fonction' => $stagiaire->getFonctionStagiaire(),
                ];
            }

            $result[] = [
                'id' => $session->getIdSession(),
                'titre' => $session->getTitre(),
                'statut' => $session->getStatut(),
                'lieu' => $session->getLieu(),
                'mode' => $session->getMode(),
                'nb_heures' => $session->getNbHeures(),
                'nb_inscrits' => $session->getNbInscrits(),
                'responsable' => [
                    'nom' => $session->getResponsableNom(),
                    'prenom' => $session->getResponsablePrenom(),
                    'telephone' => $session->getResponsableTelephone(),
                    'email' => $session->getResponsableEmail(),
                ],
                'creneaux' => $creneauxData,
                'participants' => $participantsData,
            ];
        }

        return new JsonResponse($result);
    }

    // Route pour mettre à jour le statut de toutes les sessions
    #[Route('/sessions/update-all-status', name: 'sessions_update_all_status')]
    public function updateAllStatus(
        SessionFormationRepository $repo,
        EntityManagerInterface $em,
        SessionStatusUpdater $statusUpdater
    ): Response {
        $sessions = $repo->findAll();
        foreach ($sessions as $session) {
            $statusUpdater->updateStatus($session);
            $em->persist($session);
        }

        $em->flush();

        return new Response('Tous les statuts des sessions ont été mis à jour.');
    }

    // Route pour valider une session OPCO
    #[Route('/session/{id}/valider-opco', name: 'session_valider_opco', methods: ['POST'])]
    public function validerOPCO(
        int $id,
        SessionFormationRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $session = $repo->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Session non trouvée'], 404);
        }

        // On empêche de valider une session déjà annulée
        if ($session->getStatut() === 'annulée') {
            return new JsonResponse(['error' => 'Impossible de valider une session annulée'], 400);
        }

        $session->setStatut('validé OPCO');
        $em->flush();

        return new JsonResponse([
            'message' => 'Statut mis à jour à "validé OPCO"',
            'id' => $session->getIdSession(),
            'nouveauStatut' => $session->getStatut()
        ]);
    }
}
