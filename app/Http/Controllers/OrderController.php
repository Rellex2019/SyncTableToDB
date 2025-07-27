<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class OrderController extends Controller
{
    public $sheetsService;

    public function __construct( GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    public function index(Request $request)
    {
        $count = $request->input('count');

        if ($count) {
            $orders = Order::paginate($count);
        } else {
            $orders = Order::paginate(15);
        }
        $url = Cache::get('current_spreadsheet');
        return view('orders.table', compact('orders', 'url'));
    }

    public function create()
    {
        Order::generate();
        $this->exportOrders();
        return redirect()->route('orders.index')->with('success', '1000 записей успешно сгенерировано');
    }


    public function destroy(Order $order)
    {
        $order->delete();
        $this->exportOrders();
        return redirect()->route('orders.index')->with('success', 'заказ успешно удален');
    }
    public function destroyTable()
    {
        Order::clearTable();
        $this->exportOrders();
        return redirect()->route('orders.index')->with('success', 'Все записи из таблицы были удалены');
    }

    public function setSheet(Request $request)
    {
        $request->validate([
            'sheet_url' => 'required|url'
        ]);
        $this->sheetsService->setSpreadsheet($request->sheet_url);
        return back()->with([
            'success' => 'Google Sheet URL сохранен',
            'sheet_url' => $request->sheet_url
        ]);
    }
    
    private function exportOrders()
    {
        $this->sheetsService->exportOrders();
    }

    
    public function fetchOrders($count = null)
    {
        $output = \Artisan::call('fetch:sheet', [
            'count' => $count   
        ]);
        
        $output = \Artisan::output();
        
        $htmlOutput = nl2br(e($output));
        
        return response($htmlOutput);
    }
}
