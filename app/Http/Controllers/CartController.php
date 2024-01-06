<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\InvoiceResource;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class CartController extends Controller
{
    private function getProduct(User $user, int $idProduct): Product
    {
        $product = Product::where('id', $idProduct)->where('user_id', $user->id)->first();
        if(!$product) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Product not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $product;
    }

    private function getOutlet(User $user, int $idOutlet): Outlet
    {
        $outlet = Outlet::where('id', $idOutlet)->where('user_id', $user->id)->first();
        if(!$outlet) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Outlet not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $outlet;
    }

    private function getCustomer(User $user, int $idCustomer): Customer
    {
        $customer = Customer::where('id', $idCustomer)->where('user_id', $user->id)->first();
        if(!$customer) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Customer not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return $customer;
    }


    public function addOrUpdateItem(CartRequest $request): CartResource
    {
        $data = $request->validated();
        $employee = $request->user('employee');
        $user = Auth::user();
        $product = $this->getProduct($user, $data['product_id']);

        // $cart = Cart::where('product_id', $product->id)->where('employee_id', $employee->id) ?? new Cart();
        $cart = Cart::firstOrNew([
            'product_id' => $product->id,
            'employee_id' => $employee->id,
        ]);

        $cart->product_id = $product->id;
        $cart->employee_id = $employee->id;
        $cart->qty = $data['qty'];
        if ($request->get('discount_percentage')) {
            $cart->discount = $data['discount_percentage'];
        }
        $cart->save();

        return new CartResource($cart);
    }

    public function get(Request $request): JsonResponse
    {
        $employee = $request->user('employee');
        $cartItems = Cart::with('product')->where('employee_id', $employee->id)->get();
        $totalItems = Cart::where('employee_id', $employee->id)->count();

        $totalPrice = 0;

        foreach ($cartItems as $cartItem) {
            $productPrice = $cartItem->product->price;
            $quantity = $cartItem->qty;
        
            // Calculate subtotal for this cart item
            $subtotal = $productPrice * $quantity;
            $discount = $subtotal * $cartItem->discount / 100;
            $totalProduct = $subtotal - $discount;
        
            // Add this subtotal to the total price
            $totalPrice += $totalProduct;
        }

        return (CartResource::collection($cartItems))->additional([
            'total_items' => $totalItems,
            'total_price' => $totalPrice
        ])->response();
    }

    public function deleteCart(Request $request): JsonResponse
    {
        $employee = $request->user('employee');
        Cart::where('employee_id', $employee->id)->delete();

        return response()->json(['data' => true])->setStatusCode(200);
    }

    public function deleteItems(int $id, Request $request): JsonResponse
    {
        $employee = $request->user('employee');
        Cart::where('employee_id', $employee->id)
            ->where('product_id', $id)
            ->delete();

        return response()->json(['data' => true])
            ->setStatusCode(200);
    }

    public function checkout(Request $request): InvoiceResource
    {
        $validator = Validator::make($request->all(),[
            'payment_type' => ['required', 'string'],
            'customer_id' => ['numeric', 'min:0', "nullable"],
            'outlet_id' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['numeric', 'min:0'],
            'tax_percentage' => ['numeric', 'min:0']
        ]);

        if($validator->fails()){
            throw new HttpResponseException(response([
                'errors' => $validator->getMessageBag()
            ], 400));
        }

        $user = Auth::user();
        $employee = $request->user('employee');
        $outlet = $this->getOutlet($user, $request->outlet_id);
        $cartItems = Cart::where('employee_id', $employee->id)->get();
        $cartSubtotal = $cartItems->sum(function ($item) {
            $totalProduct = $item->product->price * $item->qty;
            return $totalProduct - ($totalProduct * $item->discount/100);
        });

        $discountTotal = $cartSubtotal * $request->discount_percentage/100;
        $taxTotal = ($cartSubtotal - $discountTotal) * $request->tax_percentage/100;
        $cartTotal = $cartSubtotal - $discountTotal + $taxTotal;

        if ($request->amount_paid < $cartTotal) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'Insufficient payment'
                    ]
                ]
            ])->setStatusCode(400));
        }

        $return = $request->amount_paid - $cartTotal;

        $invoice = new Invoice();
        if ($request->get('customer_id')) {
            $customer = $this->getCustomer($user, $request->customer_id);
            $invoice->customer_id = $customer->id;
        }

        $invoiceNumber = 'INV-' . date('Ymd', $invoice->created_at) . '-' . strtoupper(substr(uniqid(), -6));
        $invoice->payment_type = $request->payment_type;
        $invoice->invoice = $invoiceNumber;
        $invoice->total_paid = $request->amount_paid;
        $invoice->discount = $request->discount_percentage;
        $invoice->tax = $request->tax_percentage;
        $invoice->outlet_id = $outlet->id;
        $invoice->employee_id = $employee->id;
        $invoice->is_refunded = false;     
        $invoice->is_paid = false;   
        $invoice->total = $cartTotal;
        $invoice->return = $return;
        $invoice->save();
        

        foreach ($cartItems as $item) {
            $product = $this->getProduct($user, $item->product_id);
            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;

            $invoiceItem->product_id = $item->product_id;
            $invoiceItem->product_name = $product->name;
            $invoiceItem->product_image = $product->image;
            $invoiceItem->product_description = $product->description;
            $invoiceItem->product_price = $product->price;
            $invoiceItem->product_is_for_sale = $product->is_for_sale;

            $invoiceItem->qty = $item->qty;
            $invoiceItem->discount = $item->discount;
            $invoiceItem->subtotal = $product->price * $item->qty;

            $invoiceItem->save();
        }



        // Cart::where('employee_id', $employee->id)->delete();

        return new InvoiceResource($invoice->loadMissing('invoiceItems'));
    }
}
