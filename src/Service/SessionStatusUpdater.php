<?php

namespace App\Service;

use App\Entity\SessionFormation;
use DateTime;

class SessionStatusUpdater
{
    /**
     * Met à jour le statut d'une session de formation en fonction de ses créneaux horaires.
     *
     * @param SessionFormation $session La session de formation à mettre à jour.
     * @return SessionFormation La session de formation avec son statut mis à jour.
     */
    public function updateStatus(SessionFormation $session): SessionFormation
    {
        // Si la session est déjà annulée, ne pas changer son statut
        if ($session->getStatut() === 'annulée') {
            return $session;
        }

        $now = new \DateTime();
        $creneaux = $session->getCreneaux();

        // Si la session n'a pas de créneaux, elle est considérée comme "créée"
        if ($creneaux->isEmpty()) {
            $session->setStatut('créée');
            return $session;
        }

        $allPast = true;
        $hasOngoing = false;

        // Parcourir chaque créneau pour déterminer le statut de la session
        foreach ($creneaux as $creneau) {
            $start = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $creneau->getJour()->format('Y-m-d') . ' ' . $creneau->getHeureDebut()->format('H:i:s')
            );
            $end = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $creneau->getJour()->format('Y-m-d') . ' ' . $creneau->getHeureFin()->format('H:i:s')
            );

            // Vérifier si le créneau actuel est en cours
            if ($now >= $start && $now <= $end) {
                $hasOngoing = true;
                $allPast = false;
                break; // Pas besoin de continuer, on sait que c'est "en cours"
            } elseif ($now < $start) {
                // Si un créneau futur est trouvé, la session n'est pas encore terminée
                $allPast = false;
            }
        }

        // Mettre à jour le statut de la session en fonction des vérifications
        if ($hasOngoing) {
            $session->setStatut('en cours');
        } elseif ($allPast) {
            $session->setStatut('terminée');
        } else {
            $session->setStatut('créée');
        }

        return $session;
    }
}
