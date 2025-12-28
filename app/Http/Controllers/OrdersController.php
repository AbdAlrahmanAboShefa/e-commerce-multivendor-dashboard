<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Cart_item;
use App\Models\order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\order_item;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
class OrdersController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)->with('items.product')->first();

        try {
            $order = $this->orderService->createOrder($user, $cart->items->toArray());
            return response()->json(['order' => $order, 'message' => 'Order created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function index()
    {
        $user = auth()->user();
        $orders = order::where('user_id', $user->id)->with('items.product')->get();
        return response()->json($orders);
    }
    public function delete(Request $request,$id)
    {
        $user = auth()->user();
        $order = order::where('user_id', $user->id)->findOrFail($id);
        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Only pending orders can be deleted'], 400);
        }
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
