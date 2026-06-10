<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundBudget extends Model
{
    protected $fillable = ['kategori', 'nama_unit', 'triwulan', 'total_dana', 'sisa_dana'];
}
