<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriPakaian extends Model
{
    use HasFactory;

    protected $table = 'kategori_pakaian';
    protected $primaryKey = 'kategori_pakaian_id';

    protected $fillable = [
        'kategori_pakaian_nama',
    ];

    public function pakaian()
    {
        return $this->hasMany(Pakaian::class, 'pakaian_kategori_pakaian_id', 'kategori_pakaian_id');
    }
}