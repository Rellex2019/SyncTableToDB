<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Cache;

class SyncOrdersToGoogleSheets extends Command
{
    protected $signature = 'sync:orders {--link= : Link to Google Sheet}';
    protected $description = 'Sync orders to Google Sheets';


    public function handle(GoogleSheetsService $sheetsService)
    {

        #Здесь можно настроить интервал запросов на синхронизацию
        $interval = 1 * 60;
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
        $this->info('Синхранизация успешно начата');
        while (true) {
            $orders = Order::all();
            $sheetsService->exportOrders($orders);
            $this->info('Успешно синхронизировано ' . $orders->count() . ' заказов');
            sleep($interval);
        }
    }
}
