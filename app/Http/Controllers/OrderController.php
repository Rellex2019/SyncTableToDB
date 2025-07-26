<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $count = $request->input('count');

        if ($count) {
            $orders = Order::paginate($count);
        } else {
            $orders = Order::paginate(15);
        }

        return view('orders.table', compact('orders'));
    }

    public function create()
    {
        Order::generate();
        return redirect()->route('orders.index')->with('success', '1000 записей успешно сгенерировано');
    }


    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'заказ успешно удален');
    }
    public function destroyTable()
    {
        Order::clearTable();
        return redirect()->route('orders.index')->with('success', 'Все записи из таблицы были удалены');
    }
}
