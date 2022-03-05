<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilledReservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [''];

    protected $casts = [
        'date' => 'datetime',
    ];


    protected $appends = ['table', 'visit'];

    public function getTableAttribute()
    {
        return Table::where('user_id', $this->id)->get() ?? null;
    }

    public function getVisitAttribute()
    {
        return FilledReservation::where('phone', $this->phone)->count() ?? 0;
    }
}
