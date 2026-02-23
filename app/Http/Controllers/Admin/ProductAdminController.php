<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRulesHandler;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin');
    }

    /**
     * Список товаров
     */
    public function index()
    {
        try {
            $products = Product::all();

            return response()->json([
                'success'  => true,
                'message'  => 'Список товаров',
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            Log::error('ProductAdminController index error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Просмотр конкретного товара
     */
    public function show($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар не найден',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Товар получен',
                'product' => $product,
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductAdminController show error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Форма создания товара
     */
    public function create()
    {
        try {
            return response()->json([
                'success'    => true,
                'message'    => 'Форма создания товара',
                'categories' => ['pizza', 'drink', 'snack', 'dessert'],
            ], 200);
        } catch (\Exception $e) {
            Log::error('ProductAdminController create error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Создание товара
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRulesHandler::getRules('store_product'),
                ValidationRulesHandler::getMessages('store_product')
            );

            $product = DB::transaction(function () use ($validated) {
                return Product::create($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Товар создан',
                'product' => $product,
            ], 201);
        } catch (\Exception $e) {
            Log::error("ProductAdminController store error: " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Форма редактирования товара
     */
    public function edit($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар не найден',
                ], 404);
            }

            return response()->json([
                'success'    => true,
                'message'    => 'Форма редактирования товара',
                'product'    => $product,
                'categories' => ['pizza', 'drink', 'snack', 'dessert'],
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductAdminController edit error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Обновление товара
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар не найден',
                ], 404);
            }

            $validated = $request->validate(
                ValidationRulesHandler::getRules('update_product'),
                ValidationRulesHandler::getMessages('update_product')
            );

            DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Товар обновлён',
                'product' => $product->fresh(),
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductAdminController update error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Удаление товара
     */
    public function destroy($id)
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар не найден',
                ], 404);
            }

            DB::transaction(function () use ($product) {
                $product->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Товар удалён',
            ], 200);
        } catch (\Exception $e) {
            Log::error("ProductAdminController destroy error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }
}
