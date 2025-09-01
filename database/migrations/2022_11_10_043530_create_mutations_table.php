<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMutationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users') // otomatis foreign key ke tabel users
                  ->onDelete('cascade'); // kalau user dihapus, data mutasi ikut terhapus
            $table->string('tujuan')->nullable();
            $table->string('alasan')->nullable();
            $table->date('tanggal_pindah')->nullable(); // lebih baik pakai tipe date
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
        Schema::dropIfExists('mutations');
    }
}
