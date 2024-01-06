<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'carts';
    protected $primaryKey = 'id';
    protected $keyType= 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'qty',
        'discount'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function employee(): BelongsTo {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
