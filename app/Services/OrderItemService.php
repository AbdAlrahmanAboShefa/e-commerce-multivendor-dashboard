<?php

namespace App\Services;

use App\Models\Order_Item;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderItemService
{
    public function updateOrderItemStatus($orderItemId, $newStatus)
    {
        $orderitem = Order_Item::where('id', $orderItemId)->where('seller_id', auth()->id())->firstorFail();
        $orderitem->status = $newStatus;
        $orderitem->save();
        if ($newStatus === 'canceled') {
            $orderitem->delete();
            return response()->json(['message' => 'Order item cancelled and removed successfully.']);
        }
        if ($newStatus === 'delivered') {
            $product = Product::find($orderitem->product_id);
            $product->stock -= $orderitem->quantity;
            if ($product->stock < 0) {
                throw new Exception('Insufficient stock for product ID: ' . $product->id);
            }
            $product->save();
        }
        $this->syncorderStatus($orderitem->order_id);
    }
    private function syncorderStatus($orderId)
    {
        $items = Order_Item::where('order_id', $orderId)->get();
        if ($items->every(fn($item) => $item->status === 'delivered')) {
            $order = $items->first()->order;
            $order->status = 'completed';
            $order->save();
        } elseif ($items->some(fn($item) => $item->status === 'delivered')) {
            $order = $items->first()->order;
            $order->status = 'partially_completed';
            $order->save();
        }
    }
    public function getAllOrderItems()
    {
        return Order_Item::where('seller_id', auth()->id())->get();
    }
}
