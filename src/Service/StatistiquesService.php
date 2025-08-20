<?php
// src/Service/StatistiquesService.php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class StatistiquesService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Récupère les statistiques générales des sessions et des stagiaires.
     *
     * @return array Un tableau contenant les statistiques générales.
     */
    public function getStatistiques(): array
    {
        // Nombre total de sessions
        $totalSessions = (int) $this->em->createQuery('SELECT COUNT(s.id_session) FROM App\Entity\SessionFormation s')
            ->getSingleScalarResult();

        // Nombre total d'inscriptions (toutes sessions confondues)
        $totalInscriptions = (int) $this->em->createQuery('SELECT COUNT(i.id_inscription) FROM App\Entity\Inscription i')
            ->getSingleScalarResult();

        // Nombre total de stagiaires uniques ayant participé à des sessions
        $totalStagiairesUniques = (int) $this->em->createQuery('SELECT COUNT(DISTINCT s.id_stagiaire) FROM App\Entity\Stagiaire s JOIN s.inscriptions i')
            ->getSingleScalarResult();

        // Nombre total de stagiaires dans le système
        $totalStagiairesGlobal = (int) $this->em->createQuery('
            SELECT COUNT(s.id_stagiaire) FROM App\Entity\Stagiaire s
        ')->getSingleScalarResult();

        // Calcul de la moyenne de stagiaires par session
        $moyenneParSession = $totalSessions > 0 ? $totalInscriptions / $totalSessions : 0;

        return [
            'total_sessions' => $totalSessions,
            'total_inscriptions' => $totalInscriptions,
            'total_stagiaires_uniques' => $totalStagiairesUniques,
            'total_stagiaires_global' => $totalStagiairesGlobal,
            'moyenne_stagiaires_par_session' => round($moyenneParSession, 2),
        ];
    }

    /**
     * Calcule le taux de satisfaction global pour les questionnaires.
     *
     * @return float Le taux de satisfaction en pourcentage.
     */
    public function calculerTauxSatisfactionQuestionnaires(): float
    {
        $conn = $this->em->getConnection();

        // Nombre de stagiaires satisfaits (réponses 4 ou 5)
        $sqlSatisfaits = "
            SELECT COUNT(DISTINCT ru.id_stagiaire) AS nb_satisfaits
            FROM reponse_utilisateur ru
            JOIN question q ON ru.id_question = q.id
            JOIN evaluation e ON q.id_evaluation = e.id
            WHERE e.type = 'questionnaire'
            AND (
                ru.reponse IN ('4', '5')
                OR ru.id_reponse IN (
                SELECT id FROM reponse WHERE reponse IN (4, 5)
                )
            )
        ";
        $nbSatisfaits = (int) $conn->executeQuery($sqlSatisfaits)->fetchOne();

        // Nombre total de stagiaires ayant répondu au questionnaire
        $sqlTotal = "
            SELECT COUNT(DISTINCT ru.id_stagiaire) AS nb_total
            FROM reponse_utilisateur ru
            JOIN question q ON ru.id_question = q.id
            JOIN evaluation e ON q.id_evaluation = e.id
            WHERE e.type = 'questionnaire'
            AND (ru.reponse IS NOT NULL OR ru.id_reponse IS NOT NULL)
        ";
        $nbTotal = (int) $conn->executeQuery($sqlTotal)->fetchOne();

        // Calcul du taux de satisfaction
        $taux = $nbTotal > 0 ? ($nbSatisfaits / $nbTotal) * 100 : 0;
        return round($taux, 2);
    }

    /**
     * Calcule le taux de réussite global pour les quiz.
     *
     * @return float Le taux de réussite en pourcentage.
     */
    public function calculerTauxReussiteQuiz(): float
    {
        $conn = $this->em->getConnection();

        // Nombre de stagiaires ayant réussi le quiz (score >= 50%)
        $sqlReussite = "
            SELECT COUNT(DISTINCT es.id_stagiaire) AS nb_reussite
            FROM evaluation_stagiaire es
            JOIN evaluation e ON es.id_evaluation = e.id
            WHERE e.type = 'quiz' AND es.score >= 50
        ";
        $nbReussite = (int) $conn->executeQuery($sqlReussite)->fetchOne();

        // Nombre total de stagiaires ayant passé le quiz
        $sqlTotal = "
            SELECT COUNT(DISTINCT es.id_stagiaire) AS nb_total
            FROM evaluation_stagiaire es
            JOIN evaluation e ON es.id_evaluation = e.id
            WHERE e.type = 'quiz'
        ";
        $nbTotal = (int) $conn->executeQuery($sqlTotal)->fetchOne();

        // Calcul du taux de réussite
        $taux = $nbTotal > 0 ? ($nbReussite / $nbTotal) * 100 : 0;
        return round($taux, 2);
    }

    /**
     * Calcule le taux de satisfaction pour chaque questionnaire.
     *
     * @return array Un tableau contenant les taux de satisfaction par questionnaire.
     */
    public function calculerTauxSatisfactionParQuestionnaire(): array
    {
        $conn = $this->em->getConnection();

        // Requête pour obtenir le nombre de stagiaires satisfaits et le total par questionnaire
        $sql = "
            SELECT
                e.id,
                e.titre,
                COUNT(DISTINCT CASE
                    WHEN ru.reponse IN ('4', '5') THEN ru.id_stagiaire
                END) AS nb_satisfaits,
                COUNT(DISTINCT ru.id_stagiaire) AS nb_total
            FROM evaluation e
            JOIN question q ON q.id_evaluation = e.id
            JOIN reponse_utilisateur ru ON ru.id_question = q.id
            WHERE e.type = 'questionnaire'
            GROUP BY e.id, e.titre
        ";

        $result = $conn->executeQuery($sql)->fetchAllAssociative();

        // Calcul du taux de satisfaction pour chaque questionnaire
        return array_map(function ($row) {
            $taux = ($row['nb_total'] > 0)
                ? round(($row['nb_satisfaits'] / $row['nb_total']) * 100, 2)
                : 0;
            return [
                'id' => (int) $row['id'],
                'titre' => $row['titre'],
                'type' => 'questionnaire',
                'taux_satisfaction' => $taux
            ];
        }, $result);
    }

    /**
     * Calcule le taux de réussite pour chaque quiz.
     *
     * @return array Un tableau contenant les taux de réussite par quiz.
     */
    public function calculerTauxReussiteParQuiz(): array
    {
        $conn = $this->em->getConnection();

        // Requête pour obtenir le nombre de stagiaires ayant réussi et le total par quiz
        $sql = "
            SELECT
                e.id,
                e.titre,
                COUNT(DISTINCT CASE WHEN es.score >= 50 THEN es.id_stagiaire END) AS nb_reussite,
                COUNT(DISTINCT es.id_stagiaire) AS nb_total
            FROM evaluation_stagiaire es
            JOIN evaluation e ON es.id_evaluation = e.id
            WHERE e.type = 'quiz'
            GROUP BY e.id, e.titre
        ";

        $result = $conn->executeQuery($sql)->fetchAllAssociative();

        // Calcul du taux de réussite pour chaque quiz
        return array_map(function ($row) {
            $taux = ($row['nb_total'] > 0)
                ? round(($row['nb_reussite'] / $row['nb_total']) * 100, 2)
                : 0;
            return [
                'id' => (int) $row['id'],
                'titre' => $row['titre'],
                'type' => 'quiz',
                'taux_reussite' => $taux
            ];
        }, $result);
    }
}
