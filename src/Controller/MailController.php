<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailController extends AbstractController
{
    //Route pour envoyer une attestation par email
    #[Route('/envoyer-attestation', name: 'envoyer_attestation', methods: ['POST'])]
    public function envoyerAttestation(Request $request, MailerInterface $mailer): JsonResponse
    {
        // Récupérez les données de la requête
        $emailCible = $request->request->get('email');
        $messagePerso = $request->request->get('message', 'Voici votre attestation de fin de stage.');
        $attestation = $request->files->get('attestation');

        if (!$attestation || $attestation->getClientOriginalExtension() !== 'pdf') {
            return new JsonResponse(['error' => 'Fichier attestation manquant ou invalide'], 400);
        }
        // Créez l'email avec l'attestation en pièce jointe
        $email = (new Email())
            ->from('stage@monprojet.dev')
            ->to($emailCible)
            ->subject('Votre attestation de fin de stage - Vivasoft')
            ->text($messagePerso)
            ->html("<p>$messagePerso</p>")
            ->attachFromPath($attestation->getPathname(), $attestation->getClientOriginalName(), 'application/pdf');
        // Envoyez l'email
        $mailer->send($email);

        return new JsonResponse(['message' => 'Attestation envoyée avec succès']);
    }
    // Route pour envoyer une feuille de présence par email
    #[Route('/envoyer-presence', name: 'envoyer_presence', methods: ['POST'])]
    public function envoyerPresence(Request $request, MailerInterface $mailer): JsonResponse
    {   // Récupérez les données de la requête
        $emailCible = $request->request->get('email');
        $messagePerso = $request->request->get('message', 'Voici votre feuille de présence.');
        $presence = $request->files->get('presence');

        if (!$presence || $presence->getClientOriginalExtension() !== 'pdf') {
            return new JsonResponse(['error' => 'Fichier feuille de présence manquant ou invalide'], 400);
        }
        // Créez l'email avec la feuille de présence en pièce jointe
        $email = (new Email())
            ->from('stage@monprojet.dev')
            ->to($emailCible)
            ->subject('Votre feuille de présence')
            ->text($messagePerso)
            ->html("<p>$messagePerso</p>")
            ->attachFromPath($presence->getPathname(), $presence->getClientOriginalName(), 'application/pdf');

        $mailer->send($email);

        return new JsonResponse(['message' => 'Feuille de présence envoyée avec succès']);
    }

}
