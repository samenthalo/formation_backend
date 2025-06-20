<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use GuzzleHttp\Client;

class ZohoSignService
{
    private HttpClientInterface $client;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;
    private string $refreshToken;
    private ?string $accessToken = null;
    private ?int $accessTokenExpireAt = null;
    private ?SessionInterface $session;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $client,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        string $refreshToken,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->refreshToken = $refreshToken;
        $this->session = $requestStack->getSession();
        $this->logger = $logger;

        if ($this->session !== null) {
            $this->accessToken = $this->session->get('zoho_access_token');
            $this->accessTokenExpireAt = $this->session->get('zoho_access_token_expire_at');
        }
    }

    public function refreshAccessToken(): string
    {
        $this->logger->info('Refreshing Zoho access token');
        $response = $this->client->request('POST', 'https://accounts.zoho.com/oauth/v2/token', [
            'body' => [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token',
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $data = $response->toArray(false);

        if (!isset($data['access_token'])) {
            $this->logger->error('Failed to refresh Zoho token', ['response' => $data]);
            throw new \Exception('Impossible de rafraîchir le token Zoho, réponse : ' . json_encode($data));
        }

        file_put_contents(__DIR__ . '/../../var/log/zoho_token_debug.json', json_encode($data, JSON_PRETTY_PRINT));

        $this->accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;
        $this->accessTokenExpireAt = time() + $expiresIn - 30;

        if ($this->session !== null) {
            $this->session->set('zoho_access_token', $this->accessToken);
            $this->session->set('zoho_access_token_expire_at', $this->accessTokenExpireAt);
        }

        $this->logger->info('Successfully refreshed Zoho access token');
        return $this->accessToken;
    }

    public function getAccessToken(): string
    {
        if ($this->accessToken === null || $this->accessTokenExpireAt === null || time() >= $this->accessTokenExpireAt) {
            $this->logger->info('Access token expired or not set, refreshing token');
            return $this->refreshAccessToken();
        }

        return $this->accessToken;
    }

    public function createSignatureRequest(array $data, UploadedFile $file): array
    {
        $token = $this->getAccessToken();
        $client = new Client(['verify' => false]);

        $this->logger->info('Creating signature request', ['data' => $data, 'file' => $file->getClientOriginalName()]);

        try {
            $response = $client->request('POST', 'https://sign.zoho.com/api/v1/requests?testing=true', [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken ' . $token,
                ],
                'multipart' => [
                    [
                        'name' => 'data',
                        'contents' => json_encode($data),
                        'headers' => ['Content-Type' => 'application/json']
                    ],
                    [
                        'name' => 'file',
                        'contents' => fopen($file->getPathname(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                        'headers' => ['Content-Type' => 'application/pdf']
                    ],
                ],
            ]);

            $content = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($content, true);

            if ($statusCode !== 200) {
                $message = $responseData['message'] ?? $content;
                $this->logger->error('Error creating signature request', ['statusCode' => $statusCode, 'message' => $message]);
                throw new \Exception('Erreur Zoho API createSignatureRequest : ' . $message);
            }

            $this->logger->info('Successfully created signature request', ['response' => $responseData]);
            return $responseData;
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            $this->logger->error('Guzzle exception while creating signature request', ['error' => $e->getMessage()]);
            throw new \Exception('Erreur lors de l\'envoi de la requête : ' . $e->getMessage());
        }
    }

public function updateSignatureRequest(string $requestId, array $updateData): array
{
    $token = $this->getAccessToken();
    $client = new Client(['verify' => false]);

    $this->logger->info('Updating signature request', ['requestId' => $requestId, 'updateData' => $updateData]);

    try {
        $response = $client->request('PUT', "https://sign.zoho.com/api/v1/requests/{$requestId}?testing=true", [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $token,
            ],
            'multipart' => [
                [
                    'name' => 'data',
                    'contents' => json_encode($updateData),
                    'headers' => ['Content-Type' => 'application/json']
                ],
            ],
        ]);

        $content = $response->getBody()->getContents();
        $statusCode = $response->getStatusCode();
        $responseData = json_decode($content, true);

        if ($statusCode !== 200) {
            $message = $responseData['message'] ?? $content;
            $this->logger->error('Error updating signature request', ['statusCode' => $statusCode, 'message' => $message]);
            throw new \Exception('Erreur Zoho API updateSignatureRequest : ' . $message);
        }

        $this->logger->info('Successfully updated signature request', ['response' => $responseData]);
        return $responseData;
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        $this->logger->error('Guzzle exception while updating signature request', ['error' => $e->getMessage()]);
        throw new \Exception('Erreur lors de l\'envoi de la requête PUT : ' . $e->getMessage());
    }
}

    public function submitSignatureRequest(string $requestId): array
    {
        $token = $this->getAccessToken();
        $url = "https://sign.zoho.com/api/v1/requests/{$requestId}/submit?testing=true";

        $this->logger->info('Submitting signature request', ['requestId' => $requestId]);

        $response = $this->client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Zoho-oauthtoken ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        $content = $response->getContent(false);
        $statusCode = $response->getStatusCode();
        $data = json_decode($content, true);

        if ($statusCode !== 200) {
            $message = $data['message'] ?? $content;
            $this->logger->error('Error submitting signature request', ['statusCode' => $statusCode, 'message' => $message]);
            throw new \Exception('Erreur Zoho API submitSignatureRequest : ' . $message);
        }

        $this->logger->info('Successfully submitted signature request', ['response' => $data]);
        return $data;
    }
}
