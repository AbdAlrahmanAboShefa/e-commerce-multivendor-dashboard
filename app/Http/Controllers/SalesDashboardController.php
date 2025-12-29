<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\order_item;

class SalesDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
     $product_count =  count(auth()->user()->products()->get());
     $order_count = count(order_item::where('seller_id', auth()->id())->where('status', 'delivered')->get());
     $total_earning = order_item::where('seller_id', auth()->id())->where('status', 'delivered')->sum('seller_earnings');
  
     $latest_orders = auth()->user()->order()->latest()->take(5)->get();
     
         return response()->json([
             'product_count' => $product_count,
             'order_count' => $order_count,
             'total_earning' => $total_earning,
             

         ]);
     ;
     return response()->json($stats);
    
    }
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
