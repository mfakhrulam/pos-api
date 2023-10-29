<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType= 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'name', 
        'email',
        'phone',
        'password'
    ];


    public function outlet(): HasMany
    {
        return $this->hasMany(Outlet::class, 'user_id', 'id');
    }

}
