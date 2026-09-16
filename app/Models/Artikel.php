<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';
    protected $fillable = ['penulis_id', 'judul', 'slug', 'thumbnail', 'ringkasan', 'konten', 'meta_title', 'meta_desc', 'is_published'];
}
