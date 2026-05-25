<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if (filled($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('supplier', 'like', "%{$request->search}%");
            });
        }

        if (filled($request->category)) {
            $query->where('category', $request->category);
        }

        if (filled($request->stock)) {
            match ($request->stock) {
                'in_stock'     => $query->where('quantity', '>', 0)->whereRaw('quantity > low_stock_threshold'),
                'low_stock'    => $query->where('quantity', '>', 0)->whereRaw('quantity <= low_stock_threshold'),
                'out_of_stock' => $query->where('quantity', 0),
                default        => null,
            };
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Product::distinct()->orderBy('category')->pluck('category');

        $stats = [
            'total'     => Product::count(),
            'low_stock' => Product::where('quantity', '>', 0)->whereRaw('quantity <= low_stock_threshold')->count(),
            'out'       => Product::where('quantity', 0)->count(),
            'value'     => Product::selectRaw('SUM(quantity * price) as total_value')->value('total_value') ?? 0,
        ];

        return view('products.index', compact('products', 'categories', 'stats'));
    }
}
