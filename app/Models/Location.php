<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Location extends Model
{
    use HasFactory;
    protected $guarded = [];

    /**
     * Get the parent locationable model.
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }


}
