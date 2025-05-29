<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->string('bukti_transfer')->nullable();
            $table->enum('status_verifikasi', ['pending', 'terverifikasi'])->default('pending');
        });
    }

    public function down()
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropColumn(['bukti_transfer', 'status_verifikasi']);
        });
    }
};
