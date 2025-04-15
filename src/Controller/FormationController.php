<?php
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

#[Route('formations', name: 'formation_')]
class FormationController extends AbstractController
{
    private $entityManager;
    private $serializer;
    private $validator;
    private $formationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        FormationRepository $formationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->formationRepository = $formationRepository;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $formations = $this->formationRepository->findAll();
        $jsonFormations = $this->serializer->serialize($formations, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormations, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Formation $formation): Response
    {
        $jsonFormation = $this->serializer->serialize($formation, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormation, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $formation = new Formation();

        $formation->setTitre($data['titre'] ?? null);
        $formation->setDescription($data['description'] ?? null);
        $formation->setPrixUnitaireHt($data['prix_unitaire_ht'] ?? null);
        $formation->setNbParticipantsMax($data['nb_participants_max'] ?? null);
        $formation->setEstActive($data['est_active'] ?? null);
        $formation->setTypeFormation($data['type_formation'] ?? null);
        $formation->setDureeHeures($data['duree_heures'] ?? null);
        $formation->setCategorie($data['categorie'] ?? null);
        $formation->setProgramme($data['programme'] ?? null);
        $formation->setMultiJour($data['multi_jour'] ?? null);
        $formation->setCible($data['cible'] ?? null);
        $formation->setMoyensPedagogiques($data['moyens_pedagogiques'] ?? null);
        $formation->setPreRequis($data['pre_requis'] ?? null);
        $formation->setDelaiAcces($data['delai_acces'] ?? null);
        $formation->setSupportsPedagogiques($data['supports_pedagogiques'] ?? null);
        $formation->setMethodesEvaluation($data['methodes_evaluation'] ?? null);
        $formation->setAccessible($data['accessible'] ?? null);
        $formation->setTauxTva($data['taux_tva'] ?? null);

        $errors = $this->validator->validate($formation);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $this->entityManager->persist($formation);
        $this->entityManager->flush();

        $jsonFormation = $this->serializer->serialize($formation, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormation, 201, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, Formation $formation): Response
    {
        $data = json_decode($request->getContent(), true);

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

        $errors = $this->validator->validate($formation);
        if (count($errors) > 0) {
            return new Response((string) $errors, 400);
        }

        $this->entityManager->flush();

        $jsonFormation = $this->serializer->serialize($formation, 'json', ['groups' => 'formation:read']);

        return new Response($jsonFormation, 200, ['Content-Type' => 'application/json']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Formation $formation): Response
    {
        $this->entityManager->remove($formation);
        $this->entityManager->flush();

        return new Response(null, 204);
    }
}
