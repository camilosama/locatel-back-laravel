<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class banca extends Model
{
    protected $table = 'cuenta';
    public $timestamps = false;
    protected $fillable = ['cuentaNom', 'cuentaNum', 'ValorInicial','ValorActual'];
}
