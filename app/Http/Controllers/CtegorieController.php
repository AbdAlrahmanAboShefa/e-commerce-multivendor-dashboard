<?php

namespace App\Http\Controllers;

use App\Models\Category;

use Illuminate\Http\Request;

class CtegorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'categories' => Category::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = Category::create($data);

        return response()->json(['category' => $category], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json([
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category->update($data);

        return response()->json(['category' => $category]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted successfully']);
        
    }
}
