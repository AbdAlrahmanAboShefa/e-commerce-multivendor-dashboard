<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Product::with('category')->with('seller');
        if($user && $user->hasrole('seller')){
            $query = Product::with('category')->where('seller_id', auth()->id());
            $this->authorize('view', $query->first() ?? new Product());
            return response()->json($query->get());
        }else{

        $allowedSorts = ['price', 'name', 'created_at'];

    

    if ($request->has('category_id') && $request->category_id != null) {
        $query->where('category_id', $request->category_id);
    }

    if ($request->has('name') && $request->name != null) {
        $query->where('name', 'like', '%' . $request->name . '%');
    }

    if ($request->has('sort_by') && in_array($request->sort_by, $allowedSorts)) {
        $sortDirection = strtolower($request->get('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $query->orderBy($request->sort_by, $sortDirection);
    }

    $products = $query->paginate(10);

    return response()->json($products);}
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    { $this->authorize('create', Product::class);
        $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')
            ->store('products', 'public');
    }
       $product = Product::create([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'category_id' => $request->category_id,
        'image' => $imagePath,
        'seller_id' => auth()->id(),
       ]);
       return response()->json($product, 201);
        }
    
    /**
     * Display the specified resource.
     */
    public function show($id)
    { $product = Product::with('category')->find($id);

        return response()->json($product);
    }

   
   
    /**
     * Update the specified resource in storage.
     */
 public function update(UpdateProductRequest $request, $id)
{
    
    $product = Product::with('category')->where('seller_id', auth()->id())->find($id);
    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }
  if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
        $product->image = $imagePath;
    }
    

    $product->update([
        'name' => $request->input('name', $product->name),
        'description' => $request->input('description', $product->description),
        'price' => $request->input('price', $product->price),
        'stock' => $request->input('stock', $product->stock),
        'category_id' => $request->input('category_id', $product->category_id),
        'image' => $product->image,
    ]);
$this->authorize('update', $product);
    return response()->json(['product' => $product->load('category')]);
}
     
     
    public function destroy($id)
    {
        $product = Product::with('category')->where('seller_id', auth()->id())->find($id);
        $this->authorize('delete', $product);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        if($product->delete()){
            return response()->json(['message' => 'Product deleted successfully'], 200);
        }
        else{
            return response()->json(['message' => 'Failed to delete product'], 500);
        }
        
    }
}
