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

class ConventionController extends AbstractController
{
    #[Route('/convention/prefill/{idSessionFormation}', name: 'prefill_convention', methods: ['GET'])]
    public function getPrefillData(int $idSessionFormation, EntityManagerInterface $entityManager): Response
    {
        $sessionFormation = $entityManager->getRepository(SessionFormation::class)->find($idSessionFormation);
        if (!$sessionFormation) {
            return new Response('Session formation non trouvée', 404);
        }

        $formation = $sessionFormation->getFormation();
        if (!$formation) {
            return new Response('Formation liée non trouvée', 404);
        }

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
            'participants' => $participantsData
        ]);
    }

    #[Route('/convention/create/{idSessionFormation}', name: 'create_convention', methods: ['POST'])]
    public function createConvention(
        int $idSessionFormation,
        Request $request,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new Response('Données JSON invalides', 400);
        }

        $sessionFormation = $entityManager->getRepository(SessionFormation::class)->find($idSessionFormation);
        if (!$sessionFormation) {
            return new Response('Session formation non trouvée', 404);
        }

        $formation = $sessionFormation->getFormation();
        if (!$formation) {
            return new Response('Formation liée non trouvée', 404);
        }

        try {
            $convention = new Convention();

            // --- Stagiaires inscrits ---
            $inscriptions = $entityManager->getRepository(Inscription::class)->findBy([
                'sessionFormation' => $sessionFormation
            ]);

            $listeStagiaires = array_map(function ($inscription) {
                $stagiaire = $inscription->getStagiaire();
                return $stagiaire->getPrenomStagiaire() . ' ' . $stagiaire->getNomStagiaire();
            }, $inscriptions);

            $convention->setStagiaires(implode(', ', $listeStagiaires));

            // --- Infos organisme / société ---
            $convention->setIdSessionFormation($idSessionFormation);
            $convention->setNomOrganisme($data['nomOrganisme'] ?? '');
            $convention->setAdresseOrganisme($data['adresseOrganisme'] ?? '');
            $convention->setDeclarationActivite($data['declarationActivite'] ?? '');
            $convention->setSiretOrganisme($data['siretOrganisme'] ?? '');
            $convention->setRepresentantOrganisme($data['representantOrganisme'] ?? '');
            $convention->setNomSocieteBeneficiaire($data['nomSocieteBeneficiaire'] ?? '');
            $convention->setAdresseSocieteBeneficiaire($data['adresseSocieteBeneficiaire'] ?? '');
            $convention->setSiretSocieteBeneficiaire($data['siretSocieteBeneficiaire'] ?? '');
            $convention->setRepresentantSocieteBeneficiaire($data['representantSocieteBeneficiaire'] ?? '');

            // --- Données Formation ---
            $convention->setObjetFormation($formation->getDescription());
            $convention->setNatureFormation($formation->getCategorie());
            $convention->setDureeFormation($formation->getDureeHeures());
            $convention->setTypeActionFormation($formation->getTypeFormation());
            $convention->setModaliteFormation($formation->getMoyensPedagogiques());
            $convention->setNomFormation($formation->getTitre());
            $convention->setProgrammeFormation($formation->getProgramme());
            $convention->setPrixFormation($formation->getPrixUnitaireHt());
            $convention->setDureePrixFormation($formation->getDureeHeures());
            $convention->setMoyensAppreciationResultats($formation->getMethodesEvaluation());

            // --- Attention au champ "lieu" (date/lieu) ---
            $lieu = $sessionFormation->getLieu();
            $logger->info('Valeur du lieu récupérée pour la convention', ['lieu' => $lieu]);

            if ($lieu === "null") {
                $lieu = null;
            }
            $convention->setDateLieu($lieu);

            // --- Divers ---
            $convention->setModalitesReglement($data['modalitesReglement'] ?? null);
            $convention->setDestinataires($data['destinataires'] ?? null);
            $convention->setDateGeneration(new \DateTime());

            $entityManager->persist($convention);
            $entityManager->flush();

            return new Response('Convention créée avec ID ' . $convention->getId(), 201);

        } catch (\Throwable $e) {
            $logger->error('Erreur lors de la création de la convention : ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'données reçues' => $data
            ]);
            return new Response('Erreur serveur lors de la création de la convention', 500);
        }
    }
}
