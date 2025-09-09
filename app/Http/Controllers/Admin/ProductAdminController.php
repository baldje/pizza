<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductAdminController extends Controller
{
    // Показать список товаров
    public function index()
    {
        $products = Product::all();
        return view('admin.products.index', compact('products'));
    }

    // Показать форму создания товара
    public function create()
    {
        return view('admin.products.create');
    }

    // Сохранить новый товар
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Товар создан');
    }

    // Показать форму редактирования товара
    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    // Обновить товар
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products.index')->with('success', 'Товар обновлен');
    }

    // Удалить товар
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Товар удалён');
    }
}
