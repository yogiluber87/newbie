<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Pemilik data mutasi (siswa).
     * Dipakai antara lain saat menghapus mutasi untuk mengaktifkan siswa kembali.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
