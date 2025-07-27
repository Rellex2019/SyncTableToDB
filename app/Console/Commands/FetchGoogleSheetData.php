<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsService;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchGoogleSheetData extends Command
{
    protected $signature = 'fetch:sheet {count? : Limit of rows} {--link= : Link to Google Sheet}';
    protected $description = 'Fetch data from Google Sheet and show in console';

    public function handle(GoogleSheetsService $sheetsService)
    {
        $client = new Client();
        
        $httpClient = new \GuzzleHttp\Client([
            'verify' => false
        ]);
        $client->setHttpClient($httpClient);

        $client->setDeveloperKey(env('GOOGLE_API_KEY'));
        
        $service = new Sheets($client);

        if($this->option('link'))
        {
            $sheetsService->setSpreadsheet($this->option('link'));
        }
        else if (Cache::get('current_spreadsheet')) {
            $sheetsService->setSpreadsheetId(Cache::get('current_spreadsheet_id'));
        }
        else
        {
            $this->error('Google Sheet URL не установлен. Используйте опцию --link для установки');
            return;
        }   

        try {
            $range = 'A1:E'; 
            $response = $service->spreadsheets_values->get($sheetsService->getSpreadsheetId(), $range);
            $values = $response->getValues();

            if (empty($values)) {
                $this->info("Таблица пустая.");
                return 0;
            }

            $count = $this->argument('count') ?? count($values);
            $rows = array_slice($values, 0, $count);
            
            $this->info("Запрос данных из Google Sheet...");
            
            $output = "";
            foreach ($rows as $row) {
                if (!empty($row[0])) {
                    $line = sprintf(
                        "ID: %s | Comment: %s",
                        $row[0] ?? 'N/A',
                        $row[4] ?? 'No comment'
                    );
                    $this->line($line);
                    $output .= $line . "\n";
                }
            }

            $summary = sprintf("\nDisplayed %d rows.", count($rows));
            $this->info($summary);
            $output .= $summary;
            
            // Возвращаем вывод для использования в контроллере
            return $output;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }
}