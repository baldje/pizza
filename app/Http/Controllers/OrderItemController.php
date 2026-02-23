<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRules;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderItemController extends Controller
{
    /**
     * Список элементов заказа
     */
    public function index()
    {
        try {
            $orderItems = OrderItem::with(['order', 'product'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Элементы заказов получены',
                'order_items' => $orderItems
            ], 200);
        } catch (\Exception $e) {
            Log::error('OrderItemController index error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Просмотр элемента заказа
     */
    public function show($id)
    {
        try {
            $orderItem = OrderItem::with(['order', 'product'])->find($id);

            if (!$orderItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Элемент заказа не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Элемент заказа получен',
                'order_item' => $orderItem
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderItemController show error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Создание элемента заказа
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRules::getRules('store_order_item'),
                ValidationRules::getMessages('store_order_item')
            );

            $orderItem = OrderItem::create($validated);
            $orderItem->load(['order', 'product']);

            return response()->json([
                'success' => true,
                'message' => 'Элемент заказа успешно создан',
                'order_item' => $orderItem
            ], 201);
        } catch (\Exception $e) {
            Log::error("OrderItemController store error: " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Обновление элемента заказа
     */
    public function update(Request $request, $id)
    {
        try {
            $orderItem = OrderItem::find($id);

            if (!$orderItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Элемент заказа не найден'
                ], 404);
            }

            $validated = $request->validate(
                ValidationRules::getRules('update_order_item'),
                ValidationRules::getMessages('update_order_item')
            );

            $orderItem->update($validated);
            $orderItem->load(['order', 'product']);

            return response()->json([
                'success' => true,
                'message' => 'Элемент заказа успешно обновлен',
                'order_item' => $orderItem
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderItemController update error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Удаление элемента заказа
     */
    public function destroy($id)
    {
        try {
            $orderItem = OrderItem::find($id);

            if (!$orderItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Элемент заказа не найден'
                ], 404);
            }

            $orderItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Элемент заказа успешно удален'
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderItemController destroy error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }
}
