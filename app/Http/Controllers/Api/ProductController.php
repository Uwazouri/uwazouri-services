<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $filePath = NULL;
        $fileUrl = NULL;

        if ($request->hasFile('image'))
        {
            $filePath = $request->file('image')->store('images', 'azure');
            $fileUrl = Storage::disk('azure')->url($filePath);
        }

        $product = new Product();
        $product->name = $request->name;
        $product->description = $request->description;
        $product->image_file = $filePath;
        $product->image_url = $fileUrl;
        $product->save();

        return response()->json($product);
    }

    public function show(Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $filePath = $product->image_file;
        $fileUrl = $product->image_url;

        if ($request->hasFile('image'))
        {
            if ($request->file('image'->isValid()))
            {
                $filePath = $request->file('image')->store('images', 'azure');
                $fileUrl = Storage::disk('azure')->url($filePath);
                if ($product->image_file != NULL)
                    Storage::disk('azure')->delete($product->image_file);
            }
        }

        if ($request->has('name'))
            $product->name = $request->name;
        if ($request->has('description'))
            $product->description = $request->description;
        $product->image_file = $filePath;
        $product->image_url = $fileUrl;
        $product->save();

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        if ($product->image_file != NULL)
            Storage::disk('azure')->delete($product->image_file);
        $product->delete();
        return response()->json(null, 204);
    }
}
