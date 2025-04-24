<?php
// src/Controller/FormateurController.php

namespace App\Controller;

use App\Repository\FormateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FormateurController extends AbstractController
{
    #[Route('/formateur', name: 'get_all_formateurs', methods: ['GET'])]
    public function getAll(FormateurRepository $formateurRepository): JsonResponse
    {
        $formateurs = $formateurRepository->findAll();

        $data = [];

        foreach ($formateurs as $formateur) {
            $data[] = [
                'id_formateur' => $formateur->getIdFormateur(),
                'nom' => $formateur->getNom(),
                'prenom' => $formateur->getPrenom(),
                'email' => $formateur->getEmail(),
                'telephone' => $formateur->getTelephone(),
                'specialites' => $formateur->getSpecialites(),
                'bio' => $formateur->getBio(),
                'est_actif' => $formateur->getEstActif(),  // Utilisation de getEstActif() ici
                'cree_le' => $formateur->getCreeLe()?->format('Y-m-d H:i:s'),
                'mis_a_jour' => $formateur->getMisAJour()?->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }
}

//
