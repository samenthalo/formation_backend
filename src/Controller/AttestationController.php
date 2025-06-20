<?php
// src/Controller/AttestationController.php
namespace App\Controller;

use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Attestation;
use App\Entity\SessionFormation;    
use App\Entity\Inscription;
use App\Repository\AttestationRepository;
use App\Repository\SessionFormationRepository;
use App\Repository\FormationRepository;
use App\Repository\SessionCreneauRepository;
use App\Entity\Formation;
use App\Entity\SessionCreneau;
use Psr\Log\LoggerInterface;


class AttestationController extends AbstractController
{   
    //Route pour récupérer toutes les attestations
    #[Route('/attestations/all', name: 'attestation_list', methods: ['GET'])]
    public function getAllAttestations(AttestationRepository $attestationRepository): JsonResponse
    {
        $attestations = $attestationRepository->findAll();

        $data = [];
        // Parcourir les attestations et préparer les données pour la réponse
        foreach ($attestations as $attestation) {
            $session = $attestation->getSession();
            $data[] = [
                'id' => $attestation->getId(),
                'chemin_fichier' => $attestation->getCheminFichier(),
                'date_generation' => $attestation->getDateGeneration()->format('Y-m-d H:i:s'),
                'session_id' => $session ? $session->getIdSession() : null,
                'session_description' => $session ? $session->getTitre() : null,
            ];
        }

        return new JsonResponse($data);
    }
    // Route pour télécharger un fichier d'attestation
    #[Route('/download/{filePath}', name: 'download_file', methods: ['GET'])]
    public function downloadFile(string $filePath): BinaryFileResponse
    {
        // Chemin complet du fichier
        $projectDir = $this->getParameter('kernel.project_dir');
        $fullPath = $projectDir . '/public/' . $filePath;

        // Vérifiez si le fichier existe
        if (!file_exists($fullPath)) {
            throw $this->createNotFoundException('The file does not exist');
        }

        // Créez une réponse pour renvoyer le fichier
        $response = new BinaryFileResponse($fullPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            basename($fullPath)
        );

        return $response;
    }
    // Route pour générer une attestation pour une session de formation
    #[Route('/attestation/generer/{id}', name: 'attestation_generer')]
    public function generer(int $id, InscriptionRepository $inscriptionRepository): JsonResponse
    {
        $inscriptions = $inscriptionRepository->findBy(['sessionFormation' => $id]);
        // Vérifier si des inscriptions existent pour cette session
        if (!$inscriptions) {
            throw $this->createNotFoundException('Aucune inscription trouvée pour cette session.');
        }

        $sessionFormation = $inscriptions[0]->getSessionFormation();
        $creneaux = $sessionFormation->getCreneaux();

        // Récupérer la formation liée à la session
        $formation = $sessionFormation->getFormation();
        $dureeHeures = $formation ? $formation->getDureeHeures() : null;

        // Préparer la liste des participants
        $participants = [];
        foreach ($inscriptions as $inscription) {
            $stagiaire = $inscription->getStagiaire();
            $participants[] = [
                'nom' => $stagiaire->getNomStagiaire(),
                'prenom' => $stagiaire->getPrenomStagiaire(),
                'entreprise' => $stagiaire->getEntrepriseStagiaire(),
                'email' => $stagiaire->getEmailStagiaire(),
            ];
        }
        // Préparer la liste des créneaux
        $listeCreneaux = [];
        foreach ($creneaux as $creneau) {
            $listeCreneaux[] = [
                'jour' => $creneau->getJour()->format('Y-m-d'),
                'heureDebut' => $creneau->getHeureDebut()->format('H:i'),
                'heureFin' => $creneau->getHeureFin()->format('H:i'),
            ];
        }

        $descriptionSession = $sessionFormation->getDescription();
        // Préparer la réponse
        return new JsonResponse([
            'session' => $descriptionSession,
            'duree_heures' => $dureeHeures,
            'participants' => $participants,
            'creneaux' => $listeCreneaux,
        ]);
    }
    // Route pour télécharger un fichier PDF
    #[Route('/upload', name: 'upload_pdf', methods: ['POST'])]
    public function uploadPdf(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $uploadedFile = $request->files->get('file');
        // Vérifier si un fichier a été téléchargé
        if (!$uploadedFile instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded'], Response::HTTP_BAD_REQUEST);
        }

        $sessionId = $request->request->get('sessionId');
        // Vérifier si l'ID de session est fourni
        if (empty($sessionId)) {
            return new JsonResponse(['error' => 'Session ID is missing'], Response::HTTP_BAD_REQUEST);
        }

        $directory = $this->getParameter('kernel.project_dir') . '/public/uploads/attestations/';

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        // Générer un nom de fichier unique
        $fileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $newFileName = $fileName . '-' . uniqid() . '.pdf';

        try {
            $uploadedFile->move($directory, $newFileName);

            // Récupérer l'entité SessionFormation
            $session = $entityManager->getRepository(SessionFormation::class)->find($sessionId);

            if (!$session) {
                return new JsonResponse(['error' => 'Session not found'], Response::HTTP_BAD_REQUEST);
            }

            // Enregistrer le chemin du fichier dans la base de données
            $attestation = new Attestation();
            $attestation->setSession($session);
            $attestation->setCheminFichier('uploads/attestations/' . $newFileName);
            $attestation->setDateGeneration(new \DateTime());

            $entityManager->persist($attestation);
            $entityManager->flush();

            return new JsonResponse(['message' => 'File uploaded successfully', 'file' => $newFileName]);
        } catch (FileException $e) {
            return new JsonResponse(['error' => 'Failed to upload file'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // Route pour supprimer une attestation
    #[Route('/attestation/delete/{id}', name: 'attestation_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager, AttestationRepository $attestationRepository): JsonResponse
    {
        $attestation = $attestationRepository->find($id);

        if (!$attestation) {
            return new JsonResponse(['error' => 'Attestation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer le fichier associé si existant
        $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $attestation->getCheminFichier();
        if (file_exists($filePath)) {
            unlink($filePath); // supprime le fichier physique
        }

        // Supprimer l'entité attestation de la base
        $entityManager->remove($attestation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Attestation supprimée avec succès'], Response::HTTP_OK);
    }
}

