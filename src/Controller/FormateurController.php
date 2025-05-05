<?php
// src/Controller/FormateurController.php

namespace App\Controller;

use App\Repository\FormateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Formateur;
use App\Repository\SessionCreneauRepository;

class FormateurController extends AbstractController
{
    #[Route('/formateur', name: 'get_all_formateurs', methods: ['GET'])]
    public function getAll(FormateurRepository $formateurRepository, SessionCreneauRepository $sessionCreneauRepository): JsonResponse
    {
        $formateurs = $formateurRepository->findAll();
        
        $data = [];
        
        foreach ($formateurs as $formateur) {
            $sessionsData = [];
            
            foreach ($formateur->getSessions() as $session) {
                // Récupérer les créneaux associés à cette session
                $creneaux = $sessionCreneauRepository->findBy(['sessionFormation' => $session]);
    
                $creneauxData = [];
                foreach ($creneaux as $creneau) {
                    $creneauxData[] = [
                        'jour' => $creneau->getJour()->format('Y-m-d'),
                        'heure_debut' => $creneau->getHeureDebut()->format('H:i'),
                        'heure_fin' => $creneau->getHeureFin()->format('H:i'),
                    ];
                }
                
                $sessionsData[] = [
                    'id_session' => $session->getIdSession(),
                    'titre' => $session->getTitre(),
                    'description' => $session->getDescription(),
                    'lieu' => $session->getLieu(),
                    'nb_heures' => $session->getNbHeures(),
                    'nb_inscrits' => $session->getNbInscrits(),
                    'creneaux' => $creneauxData, // Ajouter les créneaux ici
                ];
            }
            
            $data[] = [
                'id_formateur' => $formateur->getIdFormateur(),
                'nom' => $formateur->getNom(),
                'prenom' => $formateur->getPrenom(),
                'email' => $formateur->getEmail(),
                'telephone' => $formateur->getTelephone(),
                'specialites' => $formateur->getSpecialites(),
                'bio' => $formateur->getBio(),
                'linkedin' => $formateur->getLinkedin(),
                'cv' => $formateur->getCvPath(),
                'est_actif' => $formateur->getEstActif(),
                'cree_le' => $formateur->getCreeLe()?->format('Y-m-d H:i:s'),
                'mis_a_jour' => $formateur->getMisAJour()?->format('Y-m-d H:i:s'),
                'sessions' => $sessionsData,
            ];
        }
        
        return $this->json($data);
    }
    
    
    
    #[Route('/formateur', name: 'add_formateur', methods: ['POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $email = $request->request->get('email');
        $telephone = $request->request->get('telephone');
        $specialites = $request->request->get('specialites');
        $bio = $request->request->get('bio');
        $linkedin = $request->request->get('linkedin'); // Nouveau champ texte
        $cvFile = $request->files->get('cv'); // Champ fichier (input type="file")
    
        if (!$nom || !$prenom || !$email || !$telephone || !$specialites || !$bio) {
            return new JsonResponse(['message' => 'Données manquantes'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Gérer l’upload du fichier CV
        $cvFilename = null;
        if ($cvFile) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $cvFilename = uniqid() . '_' . $cvFile->getClientOriginalName();
            $cvFile->move($uploadsDir, $cvFilename);
        }
    
        $formateur = new Formateur();
        $formateur->setNom($nom);
        $formateur->setPrenom($prenom);
        $formateur->setEmail($email);
        $formateur->setTelephone($telephone);
        $formateur->setSpecialites($specialites);
        $formateur->setBio($bio);
        $formateur->setEstActif(true);
        $formateur->setCreeLe(new \DateTime());
        $formateur->setMisAJour(new \DateTime());
        $formateur->setLinkedin($linkedin);
        $formateur->setCvPath($cvFilename); // Enregistre juste le nom du fichier
    
        $violations = $validator->validate($formateur);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $entityManager->persist($formateur);
        $entityManager->flush();
    
        return new JsonResponse([
            'message' => 'Formateur ajouté avec succès',
            'formateur' => [
                'id_formateur' => $formateur->getIdFormateur(),
                'nom' => $formateur->getNom(),
                'prenom' => $formateur->getPrenom(),
                'email' => $formateur->getEmail(),
                'telephone' => $formateur->getTelephone(),
                'specialites' => $formateur->getSpecialites(),
                'bio' => $formateur->getBio(),
                'linkedin' => $formateur->getLinkedin(),
                'cv_path' => $formateur->getCvPath(),
                'est_actif' => $formateur->getEstActif(),
                'cree_le' => $formateur->getCreeLe()->format('Y-m-d H:i:s'),
                'mis_a_jour' => $formateur->getMisAJour()->format('Y-m-d H:i:s'),
            ]
        ], JsonResponse::HTTP_CREATED);
    }
    

    #[Route('/formateur/{id}', name: 'update_formateur', methods: ['POST'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, FormateurRepository $formateurRepository): JsonResponse
    {
        $formateur = $formateurRepository->find($id);
    
        if (!$formateur) {
            return new JsonResponse(['message' => 'Formateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Récupération des données (si elles sont présentes)
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $email = $request->request->get('email');
        $telephone = $request->request->get('telephone');
        $specialites = $request->request->get('specialites');
        $bio = $request->request->get('bio');
        $estActif = $request->request->get('est_actif');
        $linkedin = $request->request->get('linkedin'); // Nouveau champ
        $cvFile = $request->files->get('cv'); // Nouveau champ fichier
    
        // Mise à jour des champs si des valeurs sont fournies
        if ($nom) $formateur->setNom($nom);
        if ($prenom) $formateur->setPrenom($prenom);
        if ($email) $formateur->setEmail($email);
        if ($telephone) $formateur->setTelephone($telephone);
        if ($specialites) $formateur->setSpecialites($specialites);
        if ($bio) $formateur->setBio($bio);
        if ($estActif !== null) $formateur->setEstActif(filter_var($estActif, FILTER_VALIDATE_BOOLEAN));
        if ($linkedin) $formateur->setLinkedin($linkedin); // Mise à jour du champ linkedin
    
        // Gérer l’upload du fichier CV si fourni
        if ($cvFile) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $cvFilename = uniqid() . '_' . $cvFile->getClientOriginalName();
            $cvFile->move($uploadsDir, $cvFilename);
            $formateur->setCvPath($cvFilename); // Mise à jour du fichier CV
        }
    
        $formateur->setMisAJour(new \DateTime());
    
        // Validation
        $violations = $validator->validate($formateur);
    
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        // Enregistrement
        $entityManager->flush();
    
        return new JsonResponse([
            'message' => 'Formateur mis à jour avec succès',
            'formateur' => [
                'id_formateur' => $formateur->getIdFormateur(),
                'nom' => $formateur->getNom(),
                'prenom' => $formateur->getPrenom(),
                'email' => $formateur->getEmail(),
                'telephone' => $formateur->getTelephone(),
                'specialites' => $formateur->getSpecialites(),
                'bio' => $formateur->getBio(),
                'linkedin' => $formateur->getLinkedin(),
                'cv_path' => $formateur->getCvPath(),
                'est_actif' => $formateur->getEstActif(),
                'cree_le' => $formateur->getCreeLe()?->format('Y-m-d H:i:s'),
                'mis_a_jour' => $formateur->getMisAJour()?->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    

#[Route('/formateur/{id}', name: 'delete_formateur', methods: ['DELETE'])]
public function delete(int $id, EntityManagerInterface $entityManager, FormateurRepository $formateurRepository): JsonResponse
{
    $formateur = $formateurRepository->find($id);

    if (!$formateur) {
        return new JsonResponse(['message' => 'Formateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
    }

    $entityManager->remove($formateur);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Formateur supprimé avec succès'], JsonResponse::HTTP_OK);
}


}

//
