<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin');
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Админ панель',
            'data' => [
                'users_count' => \App\Models\User::count(),
                'products_count' => \App\Models\Product::count(),
                'orders_count' => \App\Models\Order::count(),
            ]
        ]);
    }
}
