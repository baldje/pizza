<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRulesHandler;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Список заказов
     */
    public function index()
    {
        try {
            $orders = Order::with(['user', 'orderItems.product'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Заказы получены',
                'orders'  => $orders,
            ], 200);
        } catch (\Exception $e) {
            Log::error('OrderController index error: ' . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Просмотр конкретного заказа
     */
    public function show($id)
    {
        try {
            $order = Order::with(['user', 'orderItems.product'])->find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Заказ получен',
                'order'   => $order,
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderController show error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Создание заказа
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate(
                ValidationRulesHandler::getRules('store_order'),
                ValidationRulesHandler::getMessages('store_order')
            );

            $order = DB::transaction(function () use ($validated) {
                $order = Order::create([
                    'user_id'          => $validated['user_id'],
                    'status'           => $validated['status'],
                    'delivery_time'    => $validated['delivery_time'],
                    'delivery_address' => $validated['delivery_address'],
                ]);

                foreach ($validated['items'] as $item) {
                    $order->orderItems()->create($item);
                }

                return $order;
            });

            $order->load(['user', 'orderItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно создан',
                'order'   => $order,
            ], 201);
        } catch (\Exception $e) {
            Log::error("OrderController store error: " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Обновление заказа
     */
    public function update(Request $request, $id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден',
                ], 404);
            }

            $validated = $request->validate(
                ValidationRulesHandler::getRules('update_order'),
                ValidationRulesHandler::getMessages('update_order')
            );

            DB::transaction(function () use ($order, $validated) {
                $order->update($validated);

                if (isset($validated['items'])) {
                    $order->orderItems()->delete();
                    foreach ($validated['items'] as $item) {
                        $order->orderItems()->create($item);
                    }
                }
            });

            $order->load(['user', 'orderItems.product']);

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно обновлен',
                'order'   => $order,
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderController update error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Удаление заказа
     */
    public function destroy($id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден',
                ], 404);
            }

            DB::transaction(function () use ($order) {
                $order->orderItems()->delete();
                $order->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Заказ успешно удален',
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderController destroy error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Обновление только статуса заказа
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заказ не найден',
                ], 404);
            }

            $validated = $request->validate(
                ValidationRulesHandler::getRules('update_order_status'),
                ValidationRulesHandler::getMessages('update_order_status')
            );

            $order->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Статус заказа обновлен',
                'order'   => $order,
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderController updateStatus error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }
}
