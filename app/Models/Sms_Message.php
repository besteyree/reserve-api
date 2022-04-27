<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sms_Message extends Model
{
    use HasFactory;
    public $table = "sms_messages";
    protected $fillable = [
        'flow_id',
        'sender',
        'restaurant_id',
    ];
}
