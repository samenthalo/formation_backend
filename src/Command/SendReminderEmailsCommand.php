<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParametreRepository;

class SendReminderEmailsCommand extends Command
{
    private MailerInterface $mailer;
    private EntityManagerInterface $entityManager;
    private ParametreRepository $parametreRepository;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        ParametreRepository $parametreRepository
    ) {
        parent::__construct();
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->parametreRepository = $parametreRepository;
    }

    protected function configure()
    {
        $this
            ->setName('app:send-reminder-emails')
            ->setDescription('Envoie un mail de rappel à l\'admin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conn = $this->entityManager->getConnection();
        $today = new \DateTime('now'); 
        
        // Mapping entre le nom court et le nom complet des types d'événements
        $mappingEvenement = [
            'Questionnaire pré-formation' => 'Questionnaire de positionnement envoyé',
            'Questionnaire de positionnement' => 'Questionnaire de positionnement envoyé',
            'Quiz' => 'Quiz envoyé',
            'Questionnaire de satisfaction' => 'Enquête de satisfaction envoyée',
            'Attestation fin formation' => 'Attestation de fin de formation envoyée',
            'Questionnaire OPCO' => 'Questionnaire OPCO envoyé',
            'Questionnaire à froid' => 'Questionnaire à froid envoyé',
            'Feuille de présence' => 'Feuille de présence envoyée',
        ];

        // Récupération des délais depuis la base de données ou utilisation des valeurs par défaut
        $delais = [
            'delai_questionnaire_preformation' => $this->parametreRepository->findValeurParNom('delai_questionnaire_preformation'),
            'delai_quiz' => $this->parametreRepository->findValeurParNom('delai_quiz'),
            'delai_questionnaire_satisfaction' => $this->parametreRepository->findValeurParNom('delai_questionnaire_satisfaction'),
            'delai_attestation_fin_formation' => $this->parametreRepository->findValeurParNom('delai_attestation_fin_formation'),
            'delai_questionnaire_opco' => $this->parametreRepository->findValeurParNom('delai_questionnaire_opco'),
            'delai_questionnaire_froid' => $this->parametreRepository->findValeurParNom('delai_questionnaire_froid'),
            'delai_feuille_presence' => $this->parametreRepository->findValeurParNom('delai_feuille_presence'),
            'delai_questionnaire_positionnement' => $this->parametreRepository->findValeurParNom('delai_questionnaire_positionnement'),
            'delai_participants_enregistres' => $this->parametreRepository->findValeurParNom('delai_participants_enregistres'),
            'delai_conventions_generees' => $this->parametreRepository->findValeurParNom('delai_conventions_generees'),
        ];

        $defaults = [
            'delai_questionnaire_preformation' => -3,
            'delai_quiz' => 1,
            'delai_questionnaire_satisfaction' => 1,
            'delai_attestation_fin_formation' => 1,
            'delai_questionnaire_opco' => 1,
            'delai_questionnaire_froid' => 14,
            'delai_feuille_presence' => 1,
            'delai_questionnaire_positionnement' => 1,
            'delai_participants_enregistres' => 0,
            'delai_conventions_generees' => 0,
        ];

        // Tableau des rappels à envoyer, avec les délais correspondants
        $reminders = [
            ['name' => 'Questionnaire pré-formation', 'offsetDays' => $delais['delai_questionnaire_preformation'] ?? $defaults['delai_questionnaire_preformation']],
            ['name' => 'Quiz', 'offsetDays' => $delais['delai_quiz'] ?? $defaults['delai_quiz']],
            ['name' => 'Questionnaire de satisfaction', 'offsetDays' => $delais['delai_questionnaire_satisfaction'] ?? $defaults['delai_questionnaire_satisfaction']],
            ['name' => 'Attestation fin formation', 'offsetDays' => $delais['delai_attestation_fin_formation'] ?? $defaults['delai_attestation_fin_formation']],
            ['name' => 'Questionnaire OPCO', 'offsetDays' => $delais['delai_questionnaire_opco'] ?? $defaults['delai_questionnaire_opco']],
            ['name' => 'Questionnaire à froid', 'offsetDays' => $delais['delai_questionnaire_froid'] ?? $defaults['delai_questionnaire_froid']],
            ['name' => 'Feuille de présence', 'offsetDays' => $delais['delai_feuille_presence'] ?? $defaults['delai_feuille_presence']],
            ['name' => 'Questionnaire de positionnement', 'offsetDays' => $delais['delai_questionnaire_positionnement'] ?? $defaults['delai_questionnaire_positionnement']],
            ['name' => 'Participants enregistrés', 'offsetDays' => $delais['delai_participants_enregistres'] ?? $defaults['delai_participants_enregistres']],
            ['name' => 'Conventions générées', 'offsetDays' => $delais['delai_conventions_generees'] ?? $defaults['delai_conventions_generees']],
        ];

        $emailAdmin = $this->parametreRepository->findValeurParNom('email_rappel_destinataire') ?: 'vivasoft.noreply@gmail.com';

        foreach ($reminders as $reminder) {
            $targetDate = (clone $today)->modify((-1 * $reminder['offsetDays']) . ' days');
            $targetDateStr = $targetDate->format('Y-m-d');

            // Récupération des créneaux pour la date cible
            $sql = "SELECT c.*, s.titre, s.responsable_email, s.id_session
                    FROM session_creneau c
                    INNER JOIN sessionformation s ON c.id_session = s.id_session
                    WHERE c.jour = :targetDate";
            $creneaux = $conn->executeQuery($sql, ['targetDate' => $targetDateStr])->fetchAllAssociative();

            foreach ($creneaux as $creneau) {
                $idSession = $creneau['id_session'];
                $typeRappel = $reminder['name'];
                $typeEvenement = $mappingEvenement[$typeRappel] ?? null;

                if (!$typeEvenement) {
                    $output->writeln("<error>Type d'événement non reconnu pour {$typeRappel}</error>");
                    continue;
                }

                // Vérifier si l'action a déjà été faite
                $actionDejaFaite = (int)$conn->fetchOne(
                    "SELECT COUNT(*) FROM chronologie WHERE id_session = :id_session AND type_evenement = :type_evenement",
                    [
                        'id_session' => $idSession,
                        'type_evenement' => $typeEvenement,
                    ]
                );

                if ($actionDejaFaite > 0) {
                    $output->writeln("Action déjà faite : {$typeEvenement} pour {$creneau['titre']}, aucun rappel.");
                    continue;
                }

                // Vérifier si le rappel a déjà été envoyé aujourd'hui
                $alreadySent = (int)$conn->fetchOne(
                    "SELECT COUNT(*) FROM rappel_envoye WHERE id_session = :id_session AND type_rappel = :type_rappel AND date_envoi = :date_envoi",
                    [
                        'id_session' => $idSession,
                        'type_rappel' => $typeRappel,
                        'date_envoi' => $today->format('Y-m-d')
                    ]
                );

                if ($alreadySent > 0) {
                    $output->writeln("Rappel déjà envoyé aujourd'hui pour {$typeRappel} - {$creneau['titre']}");
                    continue;
                }

                // Envoi du mail
                $email = (new Email())
                    ->from('vivasoft.noreply@gmail.com')
                    ->to($emailAdmin)
                    ->subject("Rappel : {$typeRappel} - {$creneau['titre']}")
                    ->text("Bonjour,\n\nCeci est un rappel automatique pour : {$typeRappel} de la session '{$creneau['titre']}' prévue le {$creneau['jour']}.\n\nMerci.");

                $this->mailer->send($email);
                $output->writeln("Mail envoyé pour {$typeRappel} - {$creneau['titre']} à {$emailAdmin}");

                // Enregistrement du rappel
                $conn->insert('rappel_envoye', [
                    'id_session' => $idSession,
                    'type_rappel' => $typeRappel,
                    'date_envoi' => $today->format('Y-m-d'),
                    'titre_formation' => $creneau['titre'],
                    'destinataire' => $emailAdmin
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
