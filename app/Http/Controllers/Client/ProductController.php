<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::where('user_id', Auth::id())->get();
        return $this->sendResponse('Products retrieved successfully.', $products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:255', Rule::unique('products')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })],
            'description' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'tax_code' => 'required|string', // Should match one of the available tax codes
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product = Product::create([
            'user_id' => Auth::id(),
            'code' => $request->code,
            'description' => $request->description,
            'unit_price' => $request->unit_price,
            'tax_code' => $request->tax_code,
        ]);

        return $this->sendResponse('Product created successfully.', $product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized.', ['error'=>'You do not own this product.'], 403);
        }

        return $this->sendResponse('Product retrieved successfully.', $product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized.', ['error'=>'You do not own this product.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:255', Rule::unique('products')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            })->ignore($product->id)],
            'description' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'tax_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        $product->update($request->all());

        return $this->sendResponse('Product updated successfully.', $product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        if ($product->user_id !== Auth::id()) {
            return $this->sendError('Unauthorized.', ['error'=>'You do not own this product.'], 403);
        }

        $product->delete();

        return $this->sendResponse('Product deleted successfully.');
    }
}
