<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
    protected $primaryKey = 'id';
    protected $keyType= 'int';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'payment_type',
        'customer_id',
        'outlet_id'
    ];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->BelongsTo(Customer::class, 'customer_id', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->BelongsTo(Employee::class, 'employee_id', 'id');
    }

    public function outlet(): BelongsTo
    {
        return $this->BelongsTo(Outlet::class, 'outlet_id', 'id');
    }
}
