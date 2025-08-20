<?php
namespace App\Repository;

use App\Entity\ReponseUtilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReponseUtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReponseUtilisateur::class);
    }

    /**
     * Trouve les réponses des utilisateurs pour un quiz donné.
     *
     * @param int $evaluationId L'ID de l'évaluation.
     * @return array Les résultats regroupés par stagiaire et par question.
     */
    public function findByQuiz(int $evaluationId): array
    {
        // Création d'une requête pour obtenir les réponses des utilisateurs pour un quiz spécifique
        $queryBuilder = $this->createQueryBuilder('ru')
            ->join('ru.stagiaire', 's')
            ->join('ru.question', 'q')
            ->leftJoin('App\Entity\Reponse', 'r', 'WITH', 'r.question = q.id')
            ->leftJoin('App\Entity\EvaluationStagiaire', 'es', 'WITH', 'es.stagiaire = s.id_stagiaire AND es.evaluation = :evaluationId')
            ->andWhere('q.evaluation = :evaluationId')
            ->setParameter('evaluationId', $evaluationId)
            ->select(
                'ru.id as id_reponse_utilisateur',
                's.id_stagiaire as stagiaireId',
                's.nom_stagiaire as stagiaireNom',
                's.prenom_stagiaire as stagiairePrenom',
                'q.id as questionId',
                'q.contenu as questionContenu',
                'q.type as type_question',
                'ru.reponse as reponseLibre',
                'ru.dateReponse as dateReponse',
                'r.id as reponseId',
                'r.contenu as reponseContenu',
                'r.estCorrect as estCorrecte',
                'r.note as note',
                'es.score as score',
                'CASE WHEN r.id = ru.reponsePredefinie THEN true ELSE false END as estChoisie'
            )
            ->orderBy('s.id_stagiaire', 'ASC')
            ->addOrderBy('q.id', 'ASC');

        $query = $queryBuilder->getQuery();
        $results = $query->getArrayResult();

        // Regrouper les résultats par stagiaire et par question
        $groupedResults = [];
        foreach ($results as $result) {
            $stagiaireId = $result['stagiaireId'];
            $questionId = $result['questionId'];
            if (!isset($groupedResults[$stagiaireId])) {
                $groupedResults[$stagiaireId] = [];
            }
            if (!isset($groupedResults[$stagiaireId][$questionId])) {
                $groupedResults[$stagiaireId][$questionId] = [
                    'stagiaireId' => $result['stagiaireId'],
                    'stagiaireNom' => $result['stagiairePrenom'] . ' ' . $result['stagiaireNom'],
                    'questionId' => $result['questionId'],
                    'questionContenu' => $result['questionContenu'],
                    'reponseLibre' => $result['reponseLibre'],
                    'dateReponse' => $result['dateReponse'],
                    'score' => $result['score'],
                    'reponsesPossibles' => [],
                ];
            }
            $groupedResults[$stagiaireId][$questionId]['reponsesPossibles'][] = [
                'reponseId' => $result['reponseId'],
                'reponseContenu' => $result['reponseContenu'],
                'estChoisie' => $result['estChoisie'],
                'estCorrecte' => $result['estCorrecte'],
                'note' => $result['note'],
            ];
        }

        // Aplatir les résultats pour obtenir une liste simple
        $flatResults = [];
        foreach ($groupedResults as $stagiaireResults) {
            foreach ($stagiaireResults as $result) {
                $flatResults[] = $result;
            }
        }

        return $flatResults;
    }

    /**
     * Trouve les réponses des utilisateurs pour un questionnaire donné.
     *
     * @param int $evaluationId L'ID de l'évaluation.
     * @return array Les résultats regroupés par stagiaire et par question.
     */
    public function findByQuestionnaire(int $evaluationId): array
    {
        // Création d'une requête pour obtenir les réponses des utilisateurs pour un questionnaire spécifique
        $results = $this->createQueryBuilder('ru')
            ->join('ru.stagiaire', 's')
            ->join('ru.question', 'q')
            ->join('q.evaluation', 'e')
            ->leftJoin('ru.reponsePredefinie', 'r')
            ->leftJoin('App\Entity\Reponse', 'rp', 'WITH', 'rp.question = q.id')
            ->andWhere('e.id = :evaluationId')
            ->andWhere('e.type = :type')
            ->setParameter('evaluationId', $evaluationId)
            ->setParameter('type', 'questionnaire')
            ->select(
                'ru.id',
                's.id_stagiaire as stagiaireId',
                's.nom_stagiaire as stagiaireNom',
                'q.id as questionId',
                'q.contenu as questionContenu',
                'r.id as reponsePredefinieId',
                'r.contenu as reponsePredefinieContenu',
                'ru.reponse as reponseLibre',
                'ru.dateReponse as dateReponse',
                'rp.id as reponsePossibleId',
                'rp.contenu as reponsePossibleContenu',
                'CASE WHEN r.id = rp.id THEN true ELSE false END as estChoisie'
            )
            ->getQuery()
            ->getArrayResult();

        // Regrouper les résultats par stagiaire et par question
        $groupedResults = [];
        foreach ($results as $result) {
            $stagiaireId = $result['stagiaireId'];
            $questionId = $result['questionId'];
            if (!isset($groupedResults[$stagiaireId])) {
                $groupedResults[$stagiaireId] = [];
            }
            if (!isset($groupedResults[$stagiaireId][$questionId])) {
                $groupedResults[$stagiaireId][$questionId] = [
                    'id' => $result['id'],
                    'stagiaireId' => $result['stagiaireId'],
                    'stagiaireNom' => $result['stagiaireNom'],
                    'questionId' => $result['questionId'],
                    'questionContenu' => $result['questionContenu'],
                    'reponseLibre' => $result['reponseLibre'],
                    'dateReponse' => $result['dateReponse'],
                    'reponsesPossibles' => [],
                ];
            }
            if ($result['reponsePossibleId'] !== null) {
                $groupedResults[$stagiaireId][$questionId]['reponsesPossibles'][] = [
                    'reponsePossibleId' => $result['reponsePossibleId'],
                    'reponsePossibleContenu' => $result['reponsePossibleContenu'],
                    'estChoisie' => $result['estChoisie'],
                ];
            }
        }

        // Aplatir les résultats pour obtenir une liste simple
        $flattenedResults = [];
        foreach ($groupedResults as $stagiaireResults) {
            foreach ($stagiaireResults as $questionResult) {
                $flattenedResults[] = $questionResult;
            }
        }

        return $flattenedResults;
    }
}
