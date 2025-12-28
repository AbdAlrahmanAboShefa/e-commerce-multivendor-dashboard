<?php

namespace App\Http\Controllers;

use App\Http\Requests\cartitemrequest;
use App\Models\Cart_item;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartItemRequest $request)
    {
        $cart = auth()->user()->cart ?? Cart::create(['user_id' => auth()->user()->id]);

        $item = $cart->items()->where('product_id', $request->product_id)->first();

      
         if ($item) {
            
          $item->quantity += $request->quantity;
          
          $item->save();
         } else {
       $cart->items()->create([
        'product_id' => $request->product_id,
        'quantity' => $request->quantity,
        'seller_id' => Product::find($request->product_id)->seller_id,
        'price' => Product::find($request->product_id)->price,
       ]);}


        return response()->json([
            'items' => $cart->items,
           
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Cart_item $cart_items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $cart = auth()->user()->cart;
        $items = auth()->user()->cart->items()->findOrFail($id);

        $items->update(['quantity' => $request->quantity]);

        $cart->load('items.product');
        

        return response()->json([
            'items' => $cart->items,
         
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        $cart = $request->user()->cart;
        $items = $cart->items()->find($id);
        $items->delete();
        $cart->load('items.product');
        $total = $cart->items->sum(fn($item) => $item->quantity * $item->product->price);

        return response()->json([
            'items' => $cart->items,
            'total' => $total
        ]);
    }
}
