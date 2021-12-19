<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['user'];

    public function tableType()
    {
        return $this->belongsTo(TableType::class, 'type_id');
    }

    public function getUserAttribute()
    {
        return null;
    }
}
