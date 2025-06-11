<?php

namespace App\Controller;

use App\Entity\Convention;
use App\Entity\SessionFormation;
use App\Entity\SessionCreneau;
use App\Entity\Inscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Template;


class ConventionController extends AbstractController
{
#[Route('/convention/all', name: 'get_all_conventions', methods: ['GET'])]
public function getAllConventions(EntityManagerInterface $entityManager): JsonResponse
{
    $conventions = $entityManager->getRepository(Convention::class)->findAll();

    $data = [];

    foreach ($conventions as $convention) {
        $idSession = $convention->getIdSession(); // ici, c'est un entier
        $session = $entityManager->getRepository(SessionFormation::class)->find($idSession);

        $data[] = [
            'id' => $convention->getId(),
            'idSession' => $idSession,
            'titreSession' => $session ? $session->getTitre() : null,
            'cheminFichier' => $convention->getCheminFichier(),
            'dateGeneration' => $convention->getDateGeneration()->format('Y-m-d H:i:s'),
        ];
    }

    return new JsonResponse($data);
}


    #[Route('/convention/prefill/{idSessionFormation}', name: 'prefill_convention', methods: ['GET'])]
    public function getPrefillData(int $idSessionFormation, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer la session de formation
        $sessionFormation = $entityManager->getRepository(SessionFormation::class)->find($idSessionFormation);
        if (!$sessionFormation) {
            return new JsonResponse(['error' => 'Session formation non trouvée'], 404);
        }

        $formation = $sessionFormation->getFormation();
        if (!$formation) {
            return new JsonResponse(['error' => 'Formation liée non trouvée'], 404);
        }

        // Récupérer le template
        $template = $entityManager->getRepository(Template::class)->findOneBy([], ['dateModification' => 'DESC']);
        $templateContent = $template ? $template->getContenu() : null;

        // --- Créneaux ---
        $creneaux = $entityManager->getRepository(SessionCreneau::class)->findBy([
            'sessionFormation' => $sessionFormation
        ]);

        $creneauxData = array_map(function ($creneau) {
            return [
                'jour' => $creneau->getJour()->format('Y-m-d'),
                'heureDebut' => $creneau->getHeureDebut()->format('H:i'),
                'heureFin' => $creneau->getHeureFin()->format('H:i'),
                'formateur' => $creneau->getFormateur() ? $creneau->getFormateur()->getNom() : null
            ];
        }, $creneaux);

        // --- Participants inscrits ---
        $inscriptions = $entityManager->getRepository(Inscription::class)->findBy([
            'sessionFormation' => $sessionFormation
        ]);

        $participantsData = [];
        foreach ($inscriptions as $inscription) {
            $stagiaire = $inscription->getStagiaire();
            if ($stagiaire) {
                $participantsData[] = [
                    'id' => $stagiaire->getIdStagiaire(),
                    'nom' => $stagiaire->getNomStagiaire(),
                    'prenom' => $stagiaire->getPrenomStagiaire(),
                    'email' => $stagiaire->getEmailStagiaire(),
                    'statut' => $inscription->getStatut()
                ];
            }
        }

        // --- Données à retourner ---
        return $this->json([
            'titreFormation' => $formation->getTitre(),
            'descriptionFormation' => $formation->getDescription(),
            'prixFormation' => $formation->getPrixUnitaireHt(),
            'nbParticipantsMax' => $formation->getNbParticipantsMax(),
            'typeFormation' => $formation->getTypeFormation(),
            'dureeHeures' => $formation->getDureeHeures(),
            'programmeFormation' => $formation->getProgramme(),
            'titreSession' => $sessionFormation->getTitre(),
            'descriptionSession' => $sessionFormation->getDescription(),
            'lieuSession' => $sessionFormation->getLieu(),
            'nbHeuresSession' => $sessionFormation->getNbHeures(),
            'statutSession' => $sessionFormation->getStatut(),
            'nbInscrits' => $sessionFormation->getNbInscrits(),
            'creneaux' => $creneauxData,
            'participants' => $participantsData,
            'template' => $templateContent
        ]);
    }

#[Route('/convention/upload', name: 'upload_convention_pdf', methods: ['POST'])]
public function uploadConventionPdf(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    // Vérifiez si la requête contient des fichiers
    if (!$request->files->count()) {
        return new JsonResponse(['error' => 'Aucun fichier envoyé'], Response::HTTP_BAD_REQUEST);
    }

    $uploadedFile = $request->files->get('file');
    $sessionId = $request->request->get('sessionId');

    // Log des données reçues
    error_log('File: ' . ($uploadedFile ? $uploadedFile->getClientOriginalName() : 'No file'));
    error_log('Session ID: ' . ($sessionId ?? 'No session ID'));

    if (!$uploadedFile) {
        return new JsonResponse(['error' => 'Aucun fichier envoyé'], Response::HTTP_BAD_REQUEST);
    }

    if (empty($sessionId)) {
        return new JsonResponse(['error' => 'ID de session manquant'], Response::HTTP_BAD_REQUEST);
    }

    $directory = $this->getParameter('kernel.project_dir') . '/public/uploads/conventions/';

    if (!file_exists($directory)) {
        mkdir($directory, 0777, true);
    }

    // Utilisez le nom de fichier original
    $originalFileName = $uploadedFile->getClientOriginalName();

    // Optionnel : Nettoyez le nom de fichier pour éviter les problèmes de sécurité
    $newFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalFileName);

    try {
        $uploadedFile->move($directory, $newFileName);

        $session = $entityManager->getRepository(SessionFormation::class)->find($sessionId);

        if (!$session) {
            return new JsonResponse(['error' => 'Session non trouvée'], Response::HTTP_BAD_REQUEST);
        }

        $convention = new Convention();
        $convention->setIdSession($session->getIdSession());
        $convention->setCheminFichier('uploads/conventions/' . $newFileName);
        $convention->setDateGeneration(new \DateTime());

        $entityManager->persist($convention);
        $entityManager->flush();

        return new JsonResponse([
            'file' => $newFileName
        ]);

    } catch (FileException $e) {
        return new JsonResponse(['error' => 'Échec de l\'upload du fichier: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
#[Route('/convention/delete/{id}', name: 'delete_convention', methods: ['DELETE'])]
public function deleteConvention(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    $convention = $entityManager->getRepository(Convention::class)->find($id);

    if (!$convention) {
        return new JsonResponse(['error' => 'Convention non trouvée'], Response::HTTP_NOT_FOUND);
    }

    // Optionnel : suppression du fichier physique s’il existe
    $filePath = $this->getParameter('kernel.project_dir') . '/public/' . $convention->getCheminFichier();
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    $entityManager->remove($convention);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Convention supprimée avec succès']);
}

}
