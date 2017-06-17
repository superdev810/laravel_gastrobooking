<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Order extends Model
{
    public $table = "orders";

    public $primaryKey = "ID";

    public function orders_detail(){
        return $this->hasMany(OrderDetail::class, "ID_orders")
            ->join('menu_list', 'menu_list.ID', '=', 'orders_detail.ID_menu_list')
            ->select('orders_detail.*');
    }

    public function client()
    {
        return $this->hasMany(Client::class, "ID_client");
    }

    public function restaurant(){
        return $this->belongsTo(Restaurant::class, "ID_restaurant");
    }

    public function scopeFilterByStatus($query, Request $request){
        if ($request->has("status")){
            $query->where('status', $request->status);
        }
    }

}
