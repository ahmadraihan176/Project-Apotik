<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = ['nama', 'status', 'tanggal', 'user_id', 'jam_masuk'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}