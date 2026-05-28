<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    // Beritahu Laravel nama tabel aslinya di DB
    protected $table = 'supplier'; 

    // Beritahu kolom mana saja yang boleh diisi
    protected $fillable = ['nama', 'alamat', 'no_hp'];


}
