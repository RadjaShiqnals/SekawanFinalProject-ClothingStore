<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'pembelian_id';

    protected $fillable = [
        'pembelian_user_id',
        'pembelian_metode_pembayaran_id',
        'pembelian_tanggal',
        'pembelian_total_harga',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'pembelian_user_id', 'id');
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'pembelian_metode_pembayaran_id', 'metode_pembayaran_id');
    }

    public function pembelianDetails()
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_detail_pembelian_id', 'pembelian_id');
    }
}