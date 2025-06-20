<?php
// src/Controller/FormationController.php

// src/Controller/FormationController.php

namespace App\Controller;

use App\Entity\Formation;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('formations', name: 'formation_')]
class FormationController extends AbstractController
{
    private $entityManager;
    private $serializer;
    private $validator;
    private $formationRepository;
    private $slugger;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        FormationRepository $formationRepository,
        SluggerInterface $slugger
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->formationRepository = $formationRepository;
        $this->slugger = $slugger;
    }

    // Route pour lister toutes les formations
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $jsonFormations = $this->serializer->serialize($formations, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormations, 200, ['Content-Type' => 'application/json']);
    }

    // Route pour afficher une formation spécifique
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        $jsonFormation = $this->serializer->serialize($formation, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormation, 200, ['Content-Type' => 'application/json']);
    }

    // Route pour créer une nouvelle formation
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $formation = new Formation();

        // Récupération des données classiques
        $formation->setTitre($request->request->get('titre'));
        $formation->setDescription($request->request->get('description'));
        $formation->setPrixUnitaireHt($request->request->get('prix_unitaire_ht'));
        $formation->setNbParticipantsMax($request->request->get('nb_participants_max'));
        $formation->setEstActive($request->request->getBoolean('est_active'));
        $formation->setTypeFormation($request->request->get('type_formation'));
        $formation->setDureeHeures($request->request->get('duree_heures'));
        $formation->setCategorie($request->request->get('categorie'));
        $formation->setProgramme($request->request->get('programme'));
        $formation->setMultiJour($request->request->getBoolean('multi_jour'));
        $formation->setCible($request->request->get('cible'));
        $formation->setMoyensPedagogiques($request->request->get('moyens_pedagogiques'));
        $formation->setPreRequis($request->request->get('pre_requis'));
        $formation->setDelaiAcces($request->request->get('delai_acces'));
        $formation->setSupportsPedagogiques($request->request->get('supports_pedagogiques'));
        $formation->setMethodesEvaluation($request->request->get('methodes_evaluation'));
        $formation->setAccessible($request->request->getBoolean('accessible'));
        $formation->setTauxTva($request->request->get('taux_tva'));

        // Gestion du fichier welcomeBooklet
        $file = $request->files->get('welcomeBooklet');
        if ($file) {
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

            try {
                $file->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                return new JsonResponse(['error' => 'Error uploading file'], 500);
            }

            $formation->setWelcomeBooklet($newFilename);
        }

        // Validation
        $errors = $this->validator->validate($formation);
        if (count($errors) > 0) {
            $errorsArray = [];
            foreach ($errors as $error) {
                $errorsArray[$error->getPropertyPath()] = $error->getMessage();
            }
            return new JsonResponse($errorsArray, 400);
        }

        // Sauvegarde
        $this->entityManager->persist($formation);
        $this->entityManager->flush();

        $jsonFormation = $this->serializer->serialize($formation, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormation, 201, ['Content-Type' => 'application/json']);
    }

    // Route pour mettre à jour une formation
    #[Route('/{id}', name: 'update', methods: ['PUT', 'POST'])]
            public function update(Request $request, Formation $formation): Response
        {
            // Vérifie si la requête est en format form-data
            $contentType = $request->headers->get('Content-Type');
            error_log('Content-Type reçu: ' . $contentType);  // Log pour le type de contenu de la requête

            // Si ce n'est pas du form-data, retourne une erreur 415
            if (!str_contains($contentType, 'multipart/form-data')) {
                error_log('Erreur: Content-Type non pris en charge');  // Log si type non supporté
                return new Response('Unsupported Content-Type', 415);
            }

            // Récupère toutes les données du formulaire
            $data = $request->request->all(); // pour form-data
            error_log('Form Data reçu: ' . json_encode($data));  // Log pour les données du formulaire

            // Débogue avant la mise à jour des données
            error_log('Formation avant mise à jour: ' . json_encode($formation));  // Log avant la mise à jour

            // Mise à jour des données de la formation
            $formation->setTitre($data['titre'] ?? $formation->getTitre());
            $formation->setDescription($data['description'] ?? $formation->getDescription());
            $formation->setPrixUnitaireHt($data['prix_unitaire_ht'] ?? $formation->getPrixUnitaireHt());
            $formation->setNbParticipantsMax($data['nb_participants_max'] ?? $formation->getNbParticipantsMax());
            $formation->setEstActive($data['est_active'] ?? $formation->getEstActive());
            $formation->setTypeFormation($data['type_formation'] ?? $formation->getTypeFormation());
            $formation->setDureeHeures($data['duree_heures'] ?? $formation->getDureeHeures());
            $formation->setCategorie($data['categorie'] ?? $formation->getCategorie());
            $formation->setProgramme($data['programme'] ?? $formation->getProgramme());
            $formation->setMultiJour($data['multi_jour'] ?? $formation->getMultiJour());
            $formation->setCible($data['cible'] ?? $formation->getCible());
            $formation->setMoyensPedagogiques($data['moyens_pedagogiques'] ?? $formation->getMoyensPedagogiques());
            $formation->setPreRequis($data['pre_requis'] ?? $formation->getPreRequis());
            $formation->setDelaiAcces($data['delai_acces'] ?? $formation->getDelaiAcces());
            $formation->setSupportsPedagogiques($data['supports_pedagogiques'] ?? $formation->getSupportsPedagogiques());
            $formation->setMethodesEvaluation($data['methodes_evaluation'] ?? $formation->getMethodesEvaluation());
            $formation->setAccessible($data['accessible'] ?? $formation->getAccessible());
            $formation->setTauxTva($data['taux_tva'] ?? $formation->getTauxTva());

            // Débogue après la mise à jour
            error_log('Formation après mise à jour: ' . json_encode($formation));  // Log après mise à jour

            // Gestion du fichier welcomeBooklet
            $file = $request->files->get('welcomeBooklet');
            if ($file) {
                error_log('Fichier welcomeBooklet reçu: ' . $file->getClientOriginalName());  // Log du nom du fichier

                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

                // Débogue avant de déplacer le fichier
                error_log('Déplacement du fichier vers: ' . $newFilename);  // Log avant déplacement du fichier

                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    error_log('Fichier téléchargé avec succès');  // Log succès du téléchargement
                } catch (FileException $e) {
                    error_log('Erreur de fichier: ' . $e->getMessage());  // Log en cas d'exception
                    return new Response('Error uploading file', 500);
                }

                $formation->setWelcomeBooklet($newFilename);
                error_log('Nom du fichier après téléchargement: ' . $newFilename);  // Log du nom du fichier téléchargé
            } else {
                error_log('Aucun fichier welcomeBooklet reçu');  // Log si aucun fichier n'est reçu
            }

            // Validation
            $errors = $this->validator->validate($formation);
            if (count($errors) > 0) {
                error_log('Erreurs de validation: ' . (string) $errors);  // Log des erreurs de validation
                return new Response('Validation errors: ' . (string) $errors, 400);
            }

            // Enregistrement en base
            try {
                error_log('Enregistrement dans la base de données...');
                $this->entityManager->flush();
                error_log('Enregistrement réussi');  // Log après succès du flush
            } catch (\Exception $e) {
                error_log('Erreur de flush: ' . $e->getMessage());  // Log en cas d'exception lors du flush
                return new Response('Error while saving data: ' . $e->getMessage(), 500);
            }

            // Sérialisation de la formation
            $jsonFormation = $this->serializer->serialize($formation, 'json', ['groups' => 'formation:read']);
            error_log('Formation sérialisée en JSON: ' . $jsonFormation);  // Log de la sérialisation JSON

            return new Response($jsonFormation, 200, ['Content-Type' => 'application/json']);
        }
    // Route pour supprimer une formation
            #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
            public function delete(Formation $formation): Response
            {
                $this->entityManager->remove($formation);
                $this->entityManager->flush();

                return new Response(null, 204);
            }
}

