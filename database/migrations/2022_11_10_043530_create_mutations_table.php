<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMutationsTable extends Migration
{
    public function up()
    {
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users', 'id')
                  ->cascadeOnDelete()
                  ->index();
            $table->string('tujuan')->nullable();
            $table->string('alasan')->nullable();
            $table->date('tanggal_pindah')->nullable();
            $table->timestamps();
            // $table->softDeletes(); // kalau mau soft delete
        });
    }

    public function down()
    {
        Schema::dropIfExists('mutations');
    }
}
