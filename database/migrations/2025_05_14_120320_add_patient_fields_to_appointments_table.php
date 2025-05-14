<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('patient_cid')->after('status')->nullable();
            $table->string('patient_hn')->after('patient_cid')->nullable();
            $table->string('patient_pname')->after('patient_hn')->nullable();
            $table->string('patient_fname')->after('patient_pname')->nullable();
            $table->string('patient_lname')->after('patient_fname')->nullable();
            $table->date('patient_birthdate')->after('patient_lname')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'patient_cid',
                'patient_hn',
                'patient_pname',
                'patient_fname',
                'patient_lname',
                'patient_birthdate',
            ]);
        });
    }
};