<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorData extends Model
{
    use HasFactory;
    protected $table = 'sensor_data';

    protected $fillable = [
        'temp_current',
        'ph_current',
        'tds_current',
        'ec_current',
        'salinity_current',
    ];
}
