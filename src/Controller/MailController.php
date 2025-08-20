<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\InscriptionRepository;
use App\Repository\AttestationRepository;

class MailController extends AbstractController
{
    // Route pour envoyer une attestation par email
    #[Route('/envoyer-attestation', name: 'envoyer_attestation', methods: ['POST'])]
    public function envoyerAttestation(Request $request, MailerInterface $mailer): JsonResponse
    {
        $emailCible = $request->request->get('email');
        $emailCc = $request->request->get('cc'); // Récupérer les emails en CC
        $messagePerso = $request->request->get('message', 'Voici votre attestation de fin de formation.');
        $attestation = $request->files->get('attestation');

        if (!$attestation || $attestation->getClientOriginalExtension() !== 'pdf') {
            return new JsonResponse(['error' => 'Fichier attestation manquant ou invalide'], 400);
        }

        $email = (new Email())
            ->from('vivasoft.noreply@gmail.com')
            ->to($emailCible)
            ->subject('Votre attestation de fin de formation - Vivasoft')
            ->text($messagePerso)
            ->html("<p>$messagePerso</p>")
            ->attachFromPath($attestation->getPathname(), $attestation->getClientOriginalName(), 'application/pdf');

        // Ajouter les destinataires en CC si le paramètre est fourni
        if ($emailCc) {
            $email->cc($emailCc);
        }

        $mailer->send($email);

        return new JsonResponse(['message' => 'Attestation envoyée avec succès']);
    }

    // Route pour envoyer une feuille de présence par email
    #[Route('/envoyer-presence', name: 'envoyer_presence', methods: ['POST'])]
    public function envoyerPresence(Request $request, MailerInterface $mailer): JsonResponse
    {
        $emailCible = $request->request->get('email');
        $messagePerso = $request->request->get('message', 'Voici votre feuille de présence.');
        $presence = $request->files->get('presence');

        if (!$presence || $presence->getClientOriginalExtension() !== 'pdf') {
            return new JsonResponse(['error' => 'Fichier feuille de présence manquant ou invalide'], 400);
        }

        $email = (new Email())
            ->from('vivasoft.noreply@gmail.com', 'Vivasoft NoReply')
            ->to($emailCible)
            ->subject('Votre feuille de présence')
            ->text($messagePerso)
            ->html("<p>$messagePerso</p>")
            ->attachFromPath($presence->getPathname(), $presence->getClientOriginalName(), 'application/pdf');

        $mailer->send($email);

        return new JsonResponse(['message' => 'Feuille de présence envoyée avec succès']);
    }

    // Route pour mettre à jour le MAILER_DSN dans le fichier .env
    #[Route('/modifier-mailer-dsn', name: 'modifier_mailer_dsn', methods: ['POST'])]
    public function modifierMailerDsn(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['mailerDsn'])) {
            return new JsonResponse(['error' => 'Le paramètre mailerDsn est requis.'], 400);
        }

        $dsn = $data['mailerDsn'];
        $envPath = $this->getParameter('kernel.project_dir') . '/.env';

        if (!file_exists($envPath)) {
            file_put_contents($envPath, "MAILER_DSN=$dsn\n");
        } else {
            $content = file_get_contents($envPath);

            if (str_contains($content, 'MAILER_DSN=')) {
                $content = preg_replace('/MAILER_DSN=.*/', "MAILER_DSN=$dsn", $content);
            } else {
                $content .= "\nMAILER_DSN=$dsn\n";
            }

            file_put_contents($envPath, $content);
        }

        return new JsonResponse(['message' => 'MAILER_DSN mis à jour avec succès']);
    }

    // Route pour envoyer un quiz par email
    #[Route('/envoyer-quiz', name: 'envoyer_quiz', methods: ['POST'])]
    public function envoyerQuiz(Request $request, MailerInterface $mailer): JsonResponse
    {
        $emailCible = $request->request->get('email');
        $emailCc = $request->request->get('cc'); // Optionnel
        $sujet = $request->request->get('sujet', 'Voici votre quiz');
        $messagePerso = $request->request->get('message', 'Voici le lien ou le contenu du quiz.');

        if (!$emailCible) {
            return new JsonResponse(['error' => 'L\'email du destinataire est requis.'], 400);
        }

        // Convertit les retours à la ligne en <br> et protège le contenu HTML
        $htmlMessage = nl2br(htmlspecialchars($messagePerso));

        $email = (new Email())
            ->from('vivasoft.noreply@gmail.com')
            ->to($emailCible)
            ->subject($sujet)
            ->text($messagePerso)
            ->html("<p>$htmlMessage</p>");

        if ($emailCc) {
            $email->cc($emailCc);
        }

        $mailer->send($email);

        return new JsonResponse(['message' => 'Quiz envoyé avec succès']);
    }

    // Route pour envoyer un questionnaire par email
    #[Route('/envoyer-questionnaire', name: 'envoyer_questionnaire', methods: ['POST'])]
    public function envoyerQuestionnaire(Request $request, MailerInterface $mailer): JsonResponse
    {
        $emailCible = $request->request->get('email');
        $emailCc = $request->request->get('cc'); // Optionnel
        $sujet = $request->request->get('sujet', 'Voici votre questionnaire');
        $messagePerso = $request->request->get('message', 'Voici le lien ou le contenu du questionnaire.');

        if (!$emailCible) {
            return new JsonResponse(['error' => 'L\'email du destinataire est requis.'], 400);
        }

        // Convertit les retours à la ligne en <br> et protège le contenu HTML
        $htmlMessage = nl2br(htmlspecialchars($messagePerso));

        $email = (new Email())
            ->from('vivasoft.noreply@gmail.com')
            ->to($emailCible)
            ->subject($sujet)
            ->text($messagePerso)
            ->html("<p>$htmlMessage</p>");

        if ($emailCc) {
            $email->cc($emailCc);
        }

        $mailer->send($email);

        return new JsonResponse(['message' => 'Questionnaire envoyé avec succès']);
    }

#[Route('/envoyer-message-session', name: 'envoyer_message_session', methods: ['POST'])]
public function envoyerMessageSession(
    Request $request,
    MailerInterface $mailer,
    InscriptionRepository $inscriptionRepository
): JsonResponse {
    $idSession = $request->request->get('id_session');
    $sujet = $request->request->get('sujet', 'Message de Vivasoft');
    $messagePerso = $request->request->get('message', '');
    $emailCc = $request->request->get('cc'); // Optionnel

    if (!$idSession) {
        return new JsonResponse(['error' => 'ID de session manquant.'], 400);
    }

    // Récupère tous les inscrits à la session
    $inscriptions = $inscriptionRepository->findBySession($idSession);

    if (!$inscriptions) {
        return new JsonResponse(['error' => 'Aucun participant trouvé pour cette session.'], 404);
    }

    // Prépare le message HTML sécurisé
    $htmlMessage = nl2br(htmlspecialchars($messagePerso));

    // Envoi un mail à chaque participant individuellement
    foreach ($inscriptions as $inscription) {
        $stagiaire = $inscription->getStagiaire();
        if (!$stagiaire || !$stagiaire->getEmailStagiaire()) {
            continue;
        }

        $email = (new Email())
            ->from('vivasoft.noreply@gmail.com')
            ->to($stagiaire->getEmailStagiaire())
            ->subject($sujet)
            ->text($messagePerso)
            ->html("<p>$htmlMessage</p>");

        if ($emailCc) {
            $email->cc($emailCc);
        }

        $mailer->send($email);
    }

    return new JsonResponse(['message' => 'Message envoyé à tous les participants de la session']);
}
}
