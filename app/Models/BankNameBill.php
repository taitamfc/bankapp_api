<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankNameBill extends Model
{
    use HasFactory;
    protected $table = 'bank_name_bills';
    protected $fillable = [
        'name',
        'code',
        'bin',
        'shortName',
        'logo',
        'short_name',
        'type',
    ];
}
