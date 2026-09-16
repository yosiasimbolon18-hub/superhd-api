<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AturanAsosiasi extends Model
{
    protected $table = 'aturan_asosiasi';
    public $timestamps = false;
    protected $fillable = ['run_id', 'antecedent', 'consequent', 'support', 'confidence', 'lift', 'is_active', 'created_at'];
    protected $casts = ['antecedent' => 'array', 'consequent' => 'array', 'is_active' => 'boolean'];
}
