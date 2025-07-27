<?php

namespace App\Services;

use App\Models\Order;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GoogleSheetsService
{
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $client = new Client();

        $httpClient = new \GuzzleHttp\Client([
            'verify' => false
        ]);
        $client->setHttpClient($httpClient);

        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setRedirectUri('http://localhost:8000/auth/callback');
        $client->addScope(Sheets::SPREADSHEETS);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        if (Storage::exists('google_token.json')) {
            $accessToken = json_decode(Storage::get('google_token.json'), true);
            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                Storage::put('google_token.json', json_encode($client->getAccessToken()));
            }
        }

        $this->service = new Sheets($client);
        $this->spreadsheetId = Cache::get('current_spreadsheet_id');
    }

    public function setSpreadsheet(string $url)
    {
        $this->spreadsheetId = $this->extractSpreadsheetId($url);
        Cache::put('current_spreadsheet_id', $this->spreadsheetId, 1440);
        Cache::put('current_spreadsheet', $url, 1440);
        return $this;
    }

    public function getSpreadsheetId() : string|null
    {
        return $this->spreadsheetId ?? session('sheet_url') ?? null;
        
    }
    public function setSpreadsheetId(string $id) : string
    {
        return $this->spreadsheetId = $id;
        
    }

    public function exportOrders() : bool
    {
        if (!$this->service->getClient()->getAccessToken()) {
            throw new \Exception('Необходима авторизация. Перейдите по URL: ' . $this->getAuthUrl());
        }

        $values = [
            ['ID', 'Status', 'Created At', 'Updated At', 'Комментарий']
        ];
        $orders = Order::allowed()->get();
        foreach ($orders as $order) {
            $values[] = [
                $order->id,
                $order->status,
                $order->created_at->format('Y-m-d H:i'),
                $order->updated_at->format('Y-m-d H:i')
            ];
        }

        $body = new ValueRange(['values' => $values]);
        $params = ['valueInputOption' => 'USER_ENTERED'];
        $range = 'A1:E';
        
        try {
            $this->service->spreadsheets_values->clear(
                $this->getSpreadsheetId(),
                'A1:D',
                new \Google\Service\Sheets\ClearValuesRequest()
            );
            
            if (!empty($values)) {
                $this->service->spreadsheets_values->update(
                    $this->getSpreadsheetId(),
                    $range,
                    $body,
                    $params
                );
            }
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Ошибка при обновлении таблицы: ' . $e->getMessage());
        }
    }

    public function getAuthUrl()
    {
        return $this->service->getClient()->createAuthUrl();
    }

    public function handleCallback($code)
    {
        $client = $this->service->getClient();
        $accessToken = $client->fetchAccessTokenWithAuthCode($code);
        $client->setAccessToken($accessToken);

        // Сохраняем токен
        Storage::put('google_token.json', json_encode($accessToken));

        return $accessToken;
    }

    public function extractSpreadsheetId($url)
    {
        preg_match('/\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $url, $matches);
        return $matches[1] ?? null;
    }
}
