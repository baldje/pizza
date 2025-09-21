<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Validation\ExceptionHandler;
use App\Http\Controllers\Validation\ValidationRules;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderAdminController extends Controller
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
            Log::error('OrderAdminController index error: ' . $e->getMessage());
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
            Log::error("OrderAdminController show error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Форма создания заказа
     */
    public function create()
    {
        try {
            $users = User::all();
            $products = Product::all();

            return response()->json([
                'success'  => true,
                'message'  => 'Форма создания заказа',
                'users'    => $users,
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            Log::error('OrderAdminController create error: ' . $e->getMessage());
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
                ValidationRules::getRules('store_order'),
                ValidationRules::getMessages('store_order')
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
            Log::error("OrderAdminController store error: " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }

    /**
     * Форма редактирования заказа
     */
    public function edit($id)
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
                'success'  => true,
                'message'  => 'Форма редактирования заказа',
                'order'    => $order,
                'users'    => User::all(),
                'products' => Product::all(),
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderAdminController edit error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
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
                ValidationRules::getRules('update_order'),
                ValidationRules::getMessages('update_order')
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
                'message' => 'Заказ успешно обновлён',
                'order'   => $order,
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderAdminController update error - ID: $id - " . $e->getMessage());
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
                'message' => 'Заказ успешно удалён',
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderAdminController destroy error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle(request(), $e);
        }
    }

    /**
     * Обновление статуса заказа
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
                ValidationRules::getRules('update_order_status'),
                ValidationRules::getMessages('update_order_status')
            );

            DB::transaction(function () use ($order, $validated) {
                $order->update(['status' => $validated['status']]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Статус заказа обновлён',
                'order'   => $order->fresh(),
            ], 200);
        } catch (\Exception $e) {
            Log::error("OrderAdminController updateStatus error - ID: $id - " . $e->getMessage());
            return ExceptionHandler::handle($request, $e);
        }
    }
}
