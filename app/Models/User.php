<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType= 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'name', 
        'email',
        'phone',
        'password',
        'token',
        'token_expired_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class, 'user_id', 'id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'user_id', 'id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'user_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'user_id', 'id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id', 'id');
    }

    // /**
    //  * Get the name of the unique identifier for the user.
    //  *
    //  * @return string
    //  */
    // public function getAuthIdentifierName() 
    // {
    //     return 'email';
    // }

    // /**
    //  * Get the unique identifier for the user.
    //  *
    //  * @return mixed
    //  */
    // public function getAuthIdentifier()
    // {
    //     return $this->email;
    // }

    // /**
    //  * Get the password for the user.
    //  *
    //  * @return string
    //  */
    // public function getAuthPassword()
    // {
    //     return $this->password;
    // }

    // /**
    //  * Get the token value for the "remember me" session.
    //  *
    //  * @return string
    //  */
    // public function getRememberToken()
    // {
    //     return $this->token;
    // }

    // /**
    //  * Set the token value for the "remember me" session.
    //  *
    //  * @param  string  $value
    //  * @return void
    //  */
    // public function setRememberToken($value)
    // {
    //     $this->token = $value;
    // }

    // /**
    //  * Get the column name for the "remember me" token.
    //  *
    //  * @return string
    //  */
    // public function getRememberTokenName()
    // {
    //     return 'token';
    // }

}
