<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = [
        'table_id','customer_id','from_time','to_time'
        ];
    public function table()
    {
        return $this->belongsTo('App\Models\Table');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    public function order()
    {
        return $this->hasOne('App\Models\Order');
    }
}
