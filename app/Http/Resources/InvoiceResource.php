<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // invoice	payment_type	discount	tax	total	total_paid	return
        $invoiceSubtotal = $this->invoiceItems->sum(function ($item){
            return $item->subtotal - ($item->subtotal * $item->discount/100);
        });

        $discountTotal = $invoiceSubtotal * $this->discount/100;
        $taxTotal = ($invoiceSubtotal - $discountTotal) * $this->tax/100;

        return [
            'id' => $this->id,
            'invoice' => $this->invoice, // ada di database
            'payment_type' => $this->payment_type, // ada di database
            'is_paid' => $this->is_paid, // ada di database
            'is_refunded' => $this->is_refunded, // ada di database
            'discount_percentage' => $this->discount, // ada di database
            'discount_total' => $discountTotal,
            'tax_percentage' => $this->tax, // ada di database
            'tax_total' => $taxTotal,
            'subtotal' => $invoiceSubtotal,
            'total' => $this->total, // ada di database
            'total_paid' => $this->total_paid, // ada di database
            'return' => $this->return, // ada di database
            'invoice_items' => InvoiceItemResource::collection($this->invoiceItems),
            'customer' => new CustomerResource($this->customer),
            'employee' => new EmployeeResource($this->employee),
            'outlet' => new OutletResource($this->outlet),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
