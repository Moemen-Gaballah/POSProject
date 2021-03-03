<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::whereHas('client', function($q) use ($request){
            return $q->where('name', 'like', '%' . $request->search . '%');
        })->paginate(5);

        return view('dashboard.orders.index', compact('orders'));
    }


    public function products(Order $order)
    {
        $products = $order->products;
        return view('dashboard.orders._products', compact('order', 'products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function destroy(Order $order)
    {
        foreach ($order->products as $product) {
            $product->update([
                'stock' => $product->stock + $product->pivot->quantity
            ]);
        } // end of for each

        $order->delete();
        session()->flash('success', __('site.deleted_successfully'));
        return redirect()->route('dashboard.orders.index');
    }
}
