<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Bonus extends Model
{
    protected $fillable = [
        "rewardType",
        "rewardTitle",
        "txnId",
        "playerId",
        "amount",
        "currency"
    ];

    /**
     * Increment type
     */
    protected $keyType = 'string';
    public $incrementing = false;


    /**
     * Generate the uuid
     */
    public static function booted() {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
