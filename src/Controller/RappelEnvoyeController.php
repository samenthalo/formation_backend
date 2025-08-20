<?php

namespace App\Controller;

use App\Repository\RappelEnvoyeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RappelEnvoyeController extends AbstractController
{
    #[Route('/rappels', name: 'get_all_rappels', methods: ['GET'])]
    public function getAllRappels(RappelEnvoyeRepository $repo): JsonResponse
    {
        // On récupère uniquement les rappels non effectués
        $rappels = $repo->findRappelsNonDansChronologie();

        
        return $this->json($rappels);
    }

}
