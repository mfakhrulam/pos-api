<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    protected $keyType= 'int';
    public $timestamps = true;
    public $incrementing = true;

    public function outlet(): BelongsTo 
    {
        return $this->belongsTo(Outlet::class, 'outlet_id', 'id');
    }
}
