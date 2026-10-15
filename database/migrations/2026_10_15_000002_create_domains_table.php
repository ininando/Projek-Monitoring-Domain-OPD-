<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            // OPD yang masih memiliki domain tidak boleh dihapus (restrict).
            $table->foreignId('opd_id')
                ->constrained('opd')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('nama_domain', 255)->unique();
            $table->date('tanggal_registrasi')->nullable();
            $table->date('tanggal_expired')->index();
            $table->enum('status', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->string('registrar', 100)->nullable();
            $table->text('nameserver')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('hosting_provider', 100)->nullable();
            $table->enum('ssl_status', ['aktif', 'tidak_aktif'])->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
