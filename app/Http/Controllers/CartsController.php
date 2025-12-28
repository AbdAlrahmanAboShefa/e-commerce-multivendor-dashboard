<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Models\Cart_item;
use Illuminate\Support\Facades\Auth;

class CartsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $cart = $request->user()->cart()->with('items.product')->first();
        if (!$cart) {
            return response()->json(['items' => [], 'total' => 0]);
        }
        $cart_total = $cart->items->sum(fn($item) => $item->quantity * $item->product->price);
        return response()->json([$cart, ['total_price'=>round($cart_total,2)]]);
    }   

  

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
       $carts = auth()->user()->cart;
       if ($carts) 
        { if (!$carts->items) {
            return response()->json(['message' => 'Cart is already empty.']);
        }
           $carts->delete();}
         return response()->json(['message' => 'Cart cleared successfully.']);
    }
}
