<?php

namespace App\Controller;

use App\Repository\SessionFormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\FichePresence;
use App\Entity\SessionFormation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\FichePresenceRepository;

class FichePresenceController extends AbstractController
{   
    // Route pour récupérer toutes les fiches de présence
    #[Route('/fichepresence/all', name: 'api_fiche_presence_all', methods: ['GET'])]
    public function getAllFichesPresence(FichePresenceRepository $fichePresenceRepo, EntityManagerInterface $entityManager): JsonResponse
    {
        $fichesPresence = $fichePresenceRepo->findAll();

        $data = [];
        foreach ($fichesPresence as $fichePresence) {
            $sessionId = $fichePresence->getIdSession();
            $session = $entityManager->getRepository(SessionFormation::class)->find($sessionId);
            // Vérifiez si la session existe avant d'accéder à ses propriétés
            $data[] = [
                'id' => $fichePresence->getId(),
                'id_session' => $fichePresence->getIdSession(),
                'titreSession' => $session ? $session->getTitre() : null,
                'chemin_fichier' => $fichePresence->getCheminFichier(),
                'date_generation' => $fichePresence->getDateGeneration()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }
    // Route pour récupérer une fiche de présence par ID de session
    #[Route('/fichepresence/{id}', name: 'api_fiche_presence_by_id', methods: ['GET'])]
    public function getFicheBySessionId(int $id, SessionFormationRepository $sessionRepo): JsonResponse
    {
        $session = $sessionRepo->find($id);
        // Vérifiez si la session existe
        if (!$session) {
            throw new NotFoundHttpException("Session non trouvée");
        }

        $formation = $session->getFormation();
        $formateur = $session->getFormateur();
        // Récupérer les dates des créneaux de la session
        $dates = [];
        foreach ($session->getCreneaux() as $creneau) {
            $dates[] = [
                'jour' => $creneau->getJour()->format('Y-m-d'),
                'heureDebut' => $creneau->getHeureDebut()->format('H:i:s'),
                'heureFin' => $creneau->getHeureFin()->format('H:i:s')
            ];
        }

        // Récupérer les participants de la session
        $participants = [];
        foreach ($session->getInscriptions() as $inscription) {
            $participant = $inscription->getStagiaire();
            $participants[] = [
                'id' => $participant->getIdStagiaire(),
                'nom' => $participant->getNomStagiaire(),
                'prenom' => $participant->getPrenomStagiaire(),
            ];
        }
        // Préparer les données à retourner
        $data = [
            'id_session' => $session->getIdSession(),
            'titre' => $session->getTitre(),
            'duree_heures' => $session->getNbHeures(),
            'formation' => [
                'id_formation' => $formation ? $formation->getIdFormation() : null,
                'nom' => $formation ? $formation->getTitre() : null,
            ],
            'formateur' => $formateur ? [
                'id_formateur' => $formateur->getIdFormateur(),
                'nom' => $formateur->getNom(),
                'prenom' => $formateur->getPrenom(),
            ] : null,
            'dates_sessions' => $dates,
            'participants' => $participants,
        ];

        return $this->json($data);
    }
    // Route pour uploader une fiche de présence PDF
    #[Route('/fichepresence/upload', name: 'upload_fiche_presence_pdf', methods: ['POST'])]
    public function uploadFichePresencePdf(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Vérifiez si la requête contient des fichiers
        if (!$request->files->count()) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé'], Response::HTTP_BAD_REQUEST);
        }

        $uploadedFile = $request->files->get('file');
        $sessionId = $request->request->get('sessionId');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'Aucun fichier envoyé'], Response::HTTP_BAD_REQUEST);
        }

        if (empty($sessionId)) {
            return new JsonResponse(['error' => 'ID de session manquant'], Response::HTTP_BAD_REQUEST);
        }

        $directory = $this->getParameter('kernel.project_dir') . '/public/uploads/fiches_presence/';

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        // Assurez-vous que le nom de fichier est unique
        $originalFileName = $uploadedFile->getClientOriginalName();
        $newFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalFileName);
        // Générer un nom de fichier unique
        try {
            $uploadedFile->move($directory, $newFileName);

            $session = $entityManager->getRepository(SessionFormation::class)->find($sessionId);

            if (!$session) {
                return new JsonResponse(['error' => 'Session non trouvée'], Response::HTTP_BAD_REQUEST);
            }
            // Créer une nouvelle fiche de présence
            $fichePresence = new FichePresence();
            $fichePresence->setIdSession($session->getIdSession());
            $fichePresence->setCheminFichier('uploads/fiches_presence/' . $newFileName);
            $fichePresence->setDateGeneration(new \DateTime());

            $entityManager->persist($fichePresence);
            $entityManager->flush();

            return new JsonResponse([
                'file' => $newFileName
            ]);

        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Échec de l\'upload du fichier: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // Route pour supprimer une fiche de présence
    #[Route('/fichepresence/{id}', name: 'api_delete_fiche_presence', methods: ['DELETE'])]
    public function deleteFichePresence(int $id, FichePresenceRepository $fichePresenceRepo, EntityManagerInterface $entityManager): JsonResponse
    {
        $fichePresence = $fichePresenceRepo->find($id);
        // Vérifiez si la fiche de présence existe
        if (!$fichePresence) {
            throw new NotFoundHttpException("Fiche de présence non trouvée");
        }
        // Supprimer la fiche de présence
        $entityManager->remove($fichePresence);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Fiche de présence supprimée avec succès']);
    }
}

