<?php
namespace App\Controller;

use App\Repository\ParametreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ParametreController extends AbstractController
{
    // Route pour récupérer l'email de rappel
    #[Route('/email-rappel', name: 'get_email_rappel', methods: ['GET'])]
    public function getEmailRappel(ParametreRepository $parametreRepository): JsonResponse
    {
        $email = $parametreRepository->findValeurParNom('email_rappel_destinataire');
        return new JsonResponse(['email' => $email], 200);
    }

    // Route pour modifier l'email de rappel
    #[Route('/modifier-email-rappel', name: 'modifier_email_rappel', methods: ['POST'])]
    public function modifierEmailRappel(Request $request, ParametreRepository $parametreRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $nouvelEmail = $data['email'] ?? null;

        if (!$nouvelEmail || !filter_var($nouvelEmail, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide'], 400);
        }

        $parametre = $parametreRepository->findOneBy(['nom' => 'email_rappel_destinataire']);
        if (!$parametre) {
            return new JsonResponse(['error' => 'Paramètre non trouvé'], 404);
        }

        $parametre->setValeur($nouvelEmail);
        $em->flush();

        return new JsonResponse(['message' => 'Email modifié avec succès'], 200);
    }

    // Route pour récupérer les paramètres de délais
    #[Route('/delais', name: 'get_parametres_delais', methods: ['GET'])]
    public function getDelaisRappels(ParametreRepository $parametreRepository): JsonResponse
    {
        $nomsParametres = [
            'delai_questionnaire_preformation',
            'delai_quiz',
            'delai_questionnaire_satisfaction',
            'delai_attestation_fin_formation',
            'delai_questionnaire_opco',
            'delai_questionnaire_froid',
        ];

        $delais = [];
        foreach ($nomsParametres as $nom) {
            $delais[$nom] = $parametreRepository->findValeurParNom($nom);
        }

        return new JsonResponse($delais);
    }

    // Route pour mettre à jour les paramètres de délais
    #[Route('/delais', name: 'update_parametres_delais', methods: ['POST'])]
    public function updateDelaisRappels(
        Request $request,
        ParametreRepository $parametreRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        foreach ($data as $nom => $valeur) {
            if (!is_numeric($valeur)) {
                return new JsonResponse(['error' => "La valeur de $nom doit être un nombre."], 400);
            }

            $parametre = $parametreRepository->findOneBy(['nom' => $nom]);
            if ($parametre) {
                $parametre->setValeur($valeur);
            }
        }

        $em->flush();

        return new JsonResponse(['message' => 'Délais mis à jour avec succès.']);
    }
}
