<?php 
namespace App\Services;
use App\Models\Order;
use App\Models\order_item;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;
class OrderService{
    public function createOrder($user, $cartItems)
    {
        return DB::transaction(function () use ($user, $cartItems) {

          
            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => 0, 
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($cartItems as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new Exception('Product out of stock: ' . $product->name);
                }

                
                // $product->decrement('stock', $item['quantity']);

            
                $itemTotal = $product->price * $item['quantity'];

                
                $commission_amount = round($itemTotal * 0.10, 2);
                $sellerEarning = ($product->price * $item['quantity']) - $commission_amount;

               
                Order_Item::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'seller_id' => $product->seller_id,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'commission_amount' => $commission_amount,
                    'seller_earnings' => $sellerEarning,
                ]);

                $total += $itemTotal;
            }

           

            $order->update(['total_price' => $total]);

            return $order;
        });
    }
}