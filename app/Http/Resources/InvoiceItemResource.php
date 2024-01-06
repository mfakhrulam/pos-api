<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $discount_total = $this->subtotal * $this->discount/100;
        $total = $this->subtotal - $discount_total;
        return [
            'id' => $this->id,
            'name' => $this->product_name,
            'description' => $this->product_description,
            'price' => $this->product_price,
            'is_for_sale' => $this->product_is_for_sale,
            'image' => $this->product_image,
            'qty' => $this->qty,
            'subtotal_product' => $this->subtotal,
            'discount_percentage_product' => $this->discount,
            'discount_total_product' => $discount_total,
            'total_product' => $total
        ];
    }
}
