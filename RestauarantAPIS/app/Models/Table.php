<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';
    protected $fillable = [
        'capacity'
            ];
    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation');
    }

    public function order()
    {
        return $this->hasOne('App\Models\Order');
    }
}
