<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemsetFrequent extends Model
{
    protected $table = 'itemset_frequent';
    public $timestamps = false;
    protected $fillable = ['run_id', 'produk_ids', 'k', 'support', 'created_at'];
    protected $casts = ['produk_ids' => 'array'];
}
