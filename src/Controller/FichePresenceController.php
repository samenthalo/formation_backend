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
#[Route('/fichepresence/all', name: 'api_fiche_presence_all', methods: ['GET'])]
public function getAllFichesPresence(FichePresenceRepository $fichePresenceRepo, EntityManagerInterface $entityManager): JsonResponse
{
    $fichesPresence = $fichePresenceRepo->findAll();

    $data = [];
    foreach ($fichesPresence as $fichePresence) {
        $sessionId = $fichePresence->getIdSession();
        $session = $entityManager->getRepository(SessionFormation::class)->find($sessionId);

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
        #[Route('/fichepresence/{id}', name: 'api_fiche_presence_by_id', methods: ['GET'])]
    public function getFicheBySessionId(int $id, SessionFormationRepository $sessionRepo): JsonResponse
    {
        $session = $sessionRepo->find($id);

        if (!$session) {
            throw new NotFoundHttpException("Session non trouvée");
        }

        $formation = $session->getFormation();
        $formateur = $session->getFormateur();

        $dates = [];
        foreach ($session->getCreneaux() as $creneau) {
            $dates[] = [
                'jour' => $creneau->getJour()->format('Y-m-d'),
                'heureDebut' => $creneau->getHeureDebut()->format('H:i:s'),
                'heureFin' => $creneau->getHeureFin()->format('H:i:s')
            ];
        }


        $participants = [];
        foreach ($session->getInscriptions() as $inscription) {
            $participant = $inscription->getStagiaire();
            $participants[] = [
                'id' => $participant->getIdStagiaire(),
                'nom' => $participant->getNomStagiaire(),
                'prenom' => $participant->getPrenomStagiaire(),
            ];
        }

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

        $originalFileName = $uploadedFile->getClientOriginalName();
        $newFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalFileName);

        try {
            $uploadedFile->move($directory, $newFileName);

            $session = $entityManager->getRepository(SessionFormation::class)->find($sessionId);

            if (!$session) {
                return new JsonResponse(['error' => 'Session non trouvée'], Response::HTTP_BAD_REQUEST);
            }

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

    #[Route('/fichepresence/{id}', name: 'api_delete_fiche_presence', methods: ['DELETE'])]
    public function deleteFichePresence(int $id, FichePresenceRepository $fichePresenceRepo, EntityManagerInterface $entityManager): JsonResponse
    {
        $fichePresence = $fichePresenceRepo->find($id);

        if (!$fichePresence) {
            throw new NotFoundHttpException("Fiche de présence non trouvée");
        }

        $entityManager->remove($fichePresence);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Fiche de présence supprimée avec succès']);
    }
}

