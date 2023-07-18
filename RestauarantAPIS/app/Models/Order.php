<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'table_id','reservation_id','cusomter_id','waiter_id','total','paid','date'
    ];
    public function table()
    {
        return $this->belongsTo('App\Models\Table');
    }

    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    public function orderDetails()
    {
        return $this->hasMany('App\Models\OrderDetail');
    }
}
