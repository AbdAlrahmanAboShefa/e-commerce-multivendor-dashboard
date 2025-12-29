<?php

namespace App\Http\Controllers;

use App\Models\order_item;
use Illuminate\Http\Request;
use App\Services\OrderItemService;
class OrderItemsController extends Controller
{
    protected $OrderItemService;

    public function __construct(OrderItemService $OrderItemService)
    {
        $this->OrderItemService = $OrderItemService;
        
    }

     /**
     * Update the specified resource in storage.
     */
   public function updateStatus(Request $request, $id)
    { 
        $order_item = order_item::findOrFail($id);
        $this->authorize('updatestatus', $order_item);
        $request->validate([
            'status' => 'required|in:pending,shipped,delivered',
        ]);
        try {
        $this->OrderItemService->updateOrderItemStatus($id, $request->status);
        return response()->json(['message' => 'Order item status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
    public function index()
    {
        $order_items = $this->OrderItemService->getAllOrderItems();
        $this->authorize('viewAny', order_item::class);
        return response()->json($order_items);
    }
}
