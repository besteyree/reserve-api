<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilledReservation extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected $appends = ['table'];

    public function getTableAttribute()
    {
        return Table::get()
        ->map(function($row){
            if(isset($row->user_id[0]) == $this->id) {
                return $row;
            }
        });
    }
}
