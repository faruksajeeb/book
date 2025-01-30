<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function getProductAttributes()
    {
        try {
            // Fetch all attributes along with their values
            $attributes = ProductAttribute::with('options')->whereStatus(1)->get();
            //dd($attributes->toArray());
            return response()->json($attributes);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attributes.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
