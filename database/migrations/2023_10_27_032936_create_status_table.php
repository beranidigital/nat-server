<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->decimal('temp_current', 10, 2); // Tipe data NUMERIC(10,2)
            $table->text('sensor_list');
            $table->decimal('ph_current', 10, 2);
            $table->decimal('ph_warn_max', 10, 2);
            $table->decimal('ph_warn_min', 10, 2);
            $table->decimal('temp_warn_max', 10, 2);
            $table->decimal('temp_warn_min', 10, 2);
            $table->decimal('tds_current', 10, 2);
            $table->decimal('tds_warn_max', 10, 2);
            $table->decimal('tds_warn_min', 10, 2);
            $table->decimal('ec_current', 10, 2);
            $table->decimal('ec_warn_max', 10, 2);
            $table->decimal('ec_warn_min', 10, 2);
            $table->decimal('salinity_current', 10, 2);
            $table->decimal('salinity_warn_max', 10, 2);
            $table->decimal('salinity_warn_min', 10, 2);
            $table->decimal('pro_current', 10, 2);
            $table->decimal('pro_warn_max', 10, 2);
            $table->decimal('pro_warn_min', 10, 2);
            $table->decimal('orp_current', 10, 2);
            $table->decimal('orp_warn_max', 10, 2);
            $table->decimal('orp_warn_min', 10, 2);
            $table->decimal('cf_current', 10, 2);
            $table->decimal('cf_warn_max', 10, 2);
            $table->decimal('cf_warn_min', 10, 2);
            $table->decimal('rh_current', 10, 2);
            $table->decimal('rh_warn_max', 10, 2);
            $table->decimal('rh_warn_min', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status');
    }
};
