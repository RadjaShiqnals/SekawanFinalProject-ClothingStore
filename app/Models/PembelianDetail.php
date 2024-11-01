<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PembelianDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembelian_detail';
    protected $primaryKey = 'pembelian_detail_id';

    protected $fillable = [
        'pembelian_detail_pembelian_id',
        'pembelian_detail_pakaian_id',
        'pembelian_detail_jumlah',
        'pembelian_detail_total_harga',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_detail_pembelian_id', 'pembelian_id');
    }

    public function pakaian()
    {
        return $this->belongsTo(Pakaian::class, 'pembelian_detail_pakaian_id', 'pakaian_id');
    }
}