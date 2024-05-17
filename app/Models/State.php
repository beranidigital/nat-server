<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $table = 'states';


    protected $casts = [
        'formatted_timestamp' => 'datetime',
    ];

    public function metadata(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StateMeta::class, 'metadata_id', 'metadata_id');
    }
}
