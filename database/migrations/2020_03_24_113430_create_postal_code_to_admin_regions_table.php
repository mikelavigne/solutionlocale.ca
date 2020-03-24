<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostalCodeToAdminRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postal_code_to_admin_regions', function (Blueprint $table) {
            $table->id();
            $table->string('postal_code', 10);
            $table->string('municipality', 200);
            $table->string('rmc', 200);
            $table->string('administrative_region', 200);
            $table->string('electoral_division', 200);
            $table->string('new_electoral_division', 200)->nullable();
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
        Schema::dropIfExists('postal_code_to_admin_regions');
    }
}
