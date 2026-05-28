<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjahit extends Model
{
    //
    protected $table = 'penjahit';
    protected $fillable =['nama','alamat','no_hp'];
}
