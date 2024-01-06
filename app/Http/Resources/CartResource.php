<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = Product::where('id', $this->product_id)->firstOrFail();
        $subtotal = $product->price * $this->qty;
        $discount_total = $subtotal * $this->discount / 100;
        $total = $subtotal - $discount_total;

        return [
            'id' => $this->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'is_for_sale' => $product->is_for_sale,
            'image' => $product->image,
            'qty' => $this->qty,
            'subtotal_product' => $subtotal,
            'discount_percentage_product' => $this->discount,
            'discount_total_product' => $discount_total,
            'total_product' => $total
        ];
    }
}
