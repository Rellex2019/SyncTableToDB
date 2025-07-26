<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable =[
        'status'
    ];

    static public function generate($count = 1000)
    {
        $statuses = ['Allowed', 'Prohibited'];
        
        for ($i = 0; $i < $count; $i++) {
            $status = $statuses[$i % 2];
            
            Order::create([
                'status' => $status,
            ]);
        }
        
    }
    static public function clearTable()
    {
        Order::truncate();
    }
}
