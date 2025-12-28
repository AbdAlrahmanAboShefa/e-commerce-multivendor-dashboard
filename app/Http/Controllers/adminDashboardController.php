<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
class adminDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productCount = count(Product::all());
        $userCount = count(User::all());
        $orderCount = count(Order::all());
        $stats = cache()->remember('admin_stats', 60, function () use ($productCount, $userCount, $orderCount) {
            return [
                'product_count' => $productCount,
                'user_count' => $userCount,
                'order_count' => $orderCount
            ];
        });
        return response()->json($stats);
    }
    /**
     * Store a newly created resource in storage.
     */
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
