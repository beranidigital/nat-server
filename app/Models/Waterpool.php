<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waterpool extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'status';

    protected $fillable = [
        'temp_current',
        'sensor_list',
        'ph_current',
        'ph_warn_max',
        'ph_warn_min',
        'temp_warn_max',
        'temp_warn_min',
        'tds_current',
        'tds_warn_max',
        'tds_warn_min',
        'ec_current',
        'ec_warn_max',
        'ec_warn_min',
        'salinity_current',
        'salinity_warn_max',
        'salinity_warn_min',
        'pro_current',
        'pro_warn_max',
        'pro_warn_min',
        'orp_current',
        'orp_warn_max',
        'orp_warn_min',
        'cf_current',
        'cf_warn_max',
        'cf_warn_min',
        'rh_current',
        'rh_warn_max',
        'rh_warn_min',
        'created_at',
        'updated_at'
    ];
}
