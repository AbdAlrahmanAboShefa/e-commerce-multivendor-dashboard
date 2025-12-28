<?php 
namespace App\Services;
use App\Models\Order_Item;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;
class OrderItemService{
    public function updateOrderItemStatus($orderItemId, $newStatus)
    {
        $orderitem = Order_Item::where('id', $orderItemId)->where('seller_id', auth()->id())->firstorFail();
        $orderitem->status = $newStatus;
        $orderitem->save(); 
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
}