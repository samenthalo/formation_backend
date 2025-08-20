<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Entity\Stagiaire;
use App\Repository\StagiaireRepository;

#[AsController]
class AuthController
{
    // Route pour l'inscription d'un nouvel utilisateur
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérification des données requises
        if (!isset($data['email'], $data['motDePasse'], $data['nom'], $data['prenom'], $data['role'])) {
            return new JsonResponse(['message' => 'Données incomplètes'], 400);
        }

        // Vérification de l'existence de l'utilisateur
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return new JsonResponse(['message' => 'Adresse email déjà utilisée'], 409);
        }

        // Création et sauvegarde du nouvel utilisateur
        $user = new User();
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setEmail($data['email']);
        $user->setRole($data['role']);
        $hashedPassword = $passwordHasher->hashPassword($user, $data['motDePasse']);
        $user->setMotDePasse($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur enregistré avec succès'], 201);
    }

    
    // Route pour la connexion d'un utilisateur
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérification des données requises
        if (!isset($data['email'], $data['motDePasse'])) {
            return new JsonResponse(['message' => 'Email ou mot de passe manquant'], 400);
        }

        // Vérification des identifiants utilisateur
        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user || !$passwordHasher->isPasswordValid($user, $data['motDePasse'])) {
            return new JsonResponse(['message' => 'Identifiants invalides'], 401);
        }

        // Récupération du stagiaire lié à l'utilisateur
        $stagiaire = $em->getRepository(Stagiaire::class)->findOneBy(['email_stagiaire' => $data['email']]);

        // Configuration de la session utilisateur
        $session = $request->getSession();
        $session->set('user_id', $user->getId());

        return new JsonResponse([
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'role' => $user->getRole(),
                'stagiaire_id' => $stagiaire ? $stagiaire->getIdStagiaire() : null,
            ]
        ]);
    }

    // Route pour accéder au tableau de bord de l'utilisateur
    #[Route('/dashboard', name: 'api_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        // Vérification de l'authentification de l'utilisateur
        if (!$userId) {
            return new JsonResponse(['message' => 'Utilisateur non connecté'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        // Récupération des informations du stagiaire
        $stagiaire = $em->getRepository(Stagiaire::class)->findOneBy([
            'email_stagiaire' => $user->getEmail()
        ]);

        $stagiaireData = null;
        $prochaineSessionData = null;
        $historiqueSessions = [];

        if ($stagiaire) {
            $stagiaireData = [
                'id' => $stagiaire->getIdStagiaire(),
                'nom' => $stagiaire->getNomStagiaire(),
                'prenom' => $stagiaire->getPrenomStagiaire(),
                'email' => $stagiaire->getEmailStagiaire(),
                'entreprise' => $stagiaire->getEntrepriseStagiaire(),
                'fonction' => $stagiaire->getFonctionStagiaire(),
                'telephone' => $stagiaire->getTelephoneStagiaire(),
            ];

            $now = new \DateTime();
            $inscriptions = $em->getRepository(\App\Entity\Inscription::class)->findBy([
                'stagiaire' => $stagiaire,
            ]);

            $prochaineDate = null;
            $prochaineSession = null;
            $prochainCreneau = null;

            foreach ($inscriptions as $inscription) {
                $sessionFormation = $inscription->getSessionFormation();
                $sessionInfo = [
                    'inscription_id' => $inscription->getIdInscription(),
                    'sessionFormation' => $sessionFormation ? $sessionFormation->getIdSession() : null,
                    'titre' => $sessionFormation ? $sessionFormation->getTitre() : null,
                    'creneaux' => []
                ];

                if (!$sessionFormation) {
                    continue;
                }

                $hasPastCreneau = false;
                foreach ($sessionFormation->getCreneaux() as $creneau) {
                    $jour = $creneau->getJour();
                    $dateStr = $jour instanceof \DateTimeInterface ? $jour->format('Y-m-d') : 'non défini';
                    $heureDebut = $creneau->getHeureDebut();
                    $heureFin = $creneau->getHeureFin();
                    $heureDebutStr = $heureDebut instanceof \DateTimeInterface ? $heureDebut->format('H:i') : null;
                    $heureFinStr = $heureFin instanceof \DateTimeInterface ? $heureFin->format('H:i') : null;

                    $sessionInfo['creneaux'][] = [
                        'date' => $dateStr,
                        'heure_debut' => $heureDebutStr,
                        'heure_fin' => $heureFinStr,
                    ];

                    if ($jour >= $now) {
                        if (is_null($prochaineDate) || $jour < $prochaineDate) {
                            $prochaineDate = $jour;
                            $prochaineSession = $sessionFormation;
                            $prochainCreneau = $creneau;
                        }
                    } else {
                        $hasPastCreneau = true;
                    }
                }

                if ($hasPastCreneau) {
                    $historiqueSessions[] = $sessionInfo;
                }
            }

            if ($prochaineSession && $prochaineDate && $prochainCreneau) {
                $prochaineSessionData = [
                    'id' => $prochaineSession->getIdSession(),
                    'titre' => $prochaineSession->getTitre(),
                    'lieu' => $prochaineSession->getLieu(),
                    'lien' => $prochaineSession->getLien(),
                    'dateProchaineSession' => $prochaineDate->format('Y-m-d'),
                    'heure_debut' => $prochainCreneau->getHeureDebut() ? $prochainCreneau->getHeureDebut()->format('H:i') : null,
                    'heure_fin' => $prochainCreneau->getHeureFin() ? $prochainCreneau->getHeureFin()->format('H:i') : null,
                ];
            }
        }

        return new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'role' => $user->getRole(),
            ],
            'stagiaire' => $stagiaireData,
            'historiqueSessions' => $historiqueSessions,
            'prochaineSession' => $prochaineSessionData,
        ]);
    }

    // Route pour la déconnexion de l'utilisateur
    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $session = $request->getSession();
        $session->invalidate(); // Suppression des données de la session

        return new JsonResponse(['message' => 'Déconnexion réussie']);
    }

    // Route pour vérifier l'authentification de l'utilisateur
    #[Route('/check-auth', name: 'api_check_auth', methods: ['GET'])]
    public function checkAuth(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $session = $request->getSession();
        $userId = $session->get('user_id');

        if (!$userId) {
            return new JsonResponse(['message' => 'Non autorisé'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé'], 404);
        }

        return new JsonResponse([
            'message' => 'Utilisateur authentifié',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'role' => $user->getRole(),
            ]
        ]);
    }
}
