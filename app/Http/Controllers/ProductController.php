<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRules;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Список продуктов
     */
    public function index()
    {
        try {
            $products = Product::all();

            return response()->json([
                'success'  => true,
                'message'  => 'Продукты получены',
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ProductController index error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Просмотр конкретного продукта
     */
    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Продукт не найден',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Продукт получен',
                'product' => $product,
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductController show error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Создание продукта
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRules::getRules('store_product'),
                ValidationRules::getMessages('store_product')
            );

            $product = DB::transaction(function () use ($validated) {
                return Product::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Продукт успешно создан',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            Log::error("ProductController store error: " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Обновление продукта
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Продукт не найден',
                ], 404);
            }

            $validated = $request->validate(
                ValidationRules::getRules('update_product'),
                ValidationRules::getMessages('update_product')
            );

            DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Продукт успешно обновлен',
                'product' => $product->fresh(),
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductController update error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Удаление продукта
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Продукт не найден',
                ], 404);
            }

            DB::transaction(function () use ($product) {
                $product->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Продукт успешно удален',
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductController destroy error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }
}
