<?php
namespace App\Controller;

use App\Service\ZohoSignService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ZohoController extends AbstractController
{
    private LoggerInterface $logger;
    private ZohoSignService $zohoSignService;

    public function __construct(LoggerInterface $logger, ZohoSignService $zohoSignService)
    {
        $this->logger = $logger;
        $this->zohoSignService = $zohoSignService;
    }
    // Route pour initier le processus OAuth avec Zoho
    #[Route('/zoho/callback', name: 'zoho_callback', methods: ['GET'])]
    public function callback(Request $request): Response
    {
        // Récupérer le code d'autorisation depuis la requête
        $code = $request->query->get('code');

        // Vérifier si le code est présent
        if (!$code) {
            return new Response('Code manquant', Response::HTTP_BAD_REQUEST);
        }
        // Log the received code
        try {
            $accessToken = $this->zohoSignService->getAccessToken();
            $this->logger->info('Access Token récupéré avec succès', ['access_token' => $accessToken]);

            return new Response('Access Token récupéré avec succès.');
        } catch (\Exception $e) {
            $this->logger->error('Exception lors de la récupération des tokens OAuth Zoho', ['exception' => $e->getMessage()]);
            return new Response('Exception lors de la récupération des tokens : ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    // Route pour créer une demande de signature
    #[Route('/zoho/request', name: 'create_signature_request', methods: ['POST'])]
    public function createSignatureRequest(Request $request): JsonResponse
    {   // Récupérer les données multipart
        $data = json_decode($request->request->get('data'), true);
        $file = $request->files->get('file');
        //Vérifier que les données sont présentes
        if (!$file) {
            return new JsonResponse(['error' => 'Fichier manquant'], Response::HTTP_BAD_REQUEST);
        }
        // Vérifiez que le fichier est un PDF
        try {
            $response = $this->zohoSignService->createSignatureRequest($data, $file);
            return new JsonResponse($response);
        } catch (\Exception $e) {
            $this->logger->error('Erreur lors de la création de la demande de signature', ['error' => $e->getMessage()]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Route pour mettre à jour une demande de signature
    #[Route('/zoho/request/{requestId}', name: 'update_signature_request', methods: ['POST'])]
    public function updateSignatureRequest(string $requestId, Request $request): JsonResponse
    {
        $this->logger->info('Update signature request received', ['requestId' => $requestId]);

        // Récupérer les données multipart
        $data = $request->request->get('data');

        $this->logger->debug('Raw data received', ['data' => $data]);

        if (empty($data)) {
            $this->logger->warning('No data received in the update signature request', ['requestId' => $requestId]);
            return new JsonResponse(['error' => 'Aucune donnée reçue'], Response::HTTP_BAD_REQUEST);
        }
        
        // Décoder le JSON
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('Invalid JSON data received', [
                'error' => json_last_error_msg(),
                'data' => $data,
                'requestId' => $requestId
            ]);
            return new JsonResponse(['error' => 'Données JSON invalides : ' . json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }
        // Vérifier si la clé 'requests' existe
        if (!isset($decoded['requests'])) {
            $this->logger->error('Missing "requests" key in the JSON data', [
                'requestId' => $requestId,
                'decodedData' => $decoded
            ]);
            return new JsonResponse(['error' => 'La clé "requests" est manquante dans les données'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->logger->info('Attempting to update signature request', ['requestId' => $requestId]);
    $response = $this->zohoSignService->updateSignatureRequest($requestId, [
        'requests' => $decoded['requests'][0] // 👈 important ici
    ]);
            $this->logger->info('Successfully updated signature request', ['requestId' => $requestId, 'response' => $response]);
            return new JsonResponse($response);
        } catch (\Exception $e) {
            $this->logger->error('Error updating signature request', [
                'error' => $e->getMessage(),
                'requestId' => $requestId,
                'data' => $data,
            ]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
        // Route pour soumettre une demande de signature
        #[Route('/zoho/request/{requestId}/submit', name: 'submit_signature_request', methods: ['POST'])]
        public function submitSignatureRequest(string $requestId): JsonResponse
        {
            try {
                $response = $this->zohoSignService->submitSignatureRequest($requestId);
                return new JsonResponse($response);
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la soumission de la demande de signature', ['error' => $e->getMessage()]);
                return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
}
