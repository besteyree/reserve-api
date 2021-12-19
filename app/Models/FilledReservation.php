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
        return Table::where('user_id', $this->id)->first() ?? null;
    }
}
