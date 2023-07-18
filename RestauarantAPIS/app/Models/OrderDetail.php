<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';
    protected $fillable = [
        'order_id','meal_id','amount_to_pay'
    ];
    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    public function meal()
    {
        return $this->belongsTo('App\Models\Meal');
    }
}
