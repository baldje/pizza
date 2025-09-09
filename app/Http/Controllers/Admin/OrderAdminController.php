<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderAdminController extends Controller
{
    //TODO update delete
    public function index()
    {
        $orders = Order::with('user')->get();
        return response()->json([
            'success' => true,
            'message' => 'Заказы админа',
            'order' => $orders
        ], 201);
    }

    public function create()
    {
        $users = User::all();
        // $products = Product::all(); // если нужно, можно подключить модель Product
        return view('admin.orders.create', compact('users'));
    }

    // Сохранить новый заказ
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|max:50',
            'delivery_time' => 'required|date',
            'delivery_address' => 'required|string'
            // Добавьте другие необходимые поля
        ]);

        $order = Order::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Заказ создан',
            'order' => $order
        ], 201);
    }

    // Показать форму редактирования заказа
    public function edit(Order $order)
    {
//        $users = User::all();
//        return view('admin.orders.edit', compact('order', 'users'));
    }

    // Обновить заказ
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|max:50',
            'total' => 'required|numeric|min:0',
        ]);

        $order->update($validated);

        return redirect()->route('admin.orders.index')->with('success', 'Заказ обновлен');
    }

    // Удалить заказ
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Заказ удалён');
    }
}
