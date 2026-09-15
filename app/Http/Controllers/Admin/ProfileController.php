<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Order\Order;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $customer = null;
        $orders = collect();

        if ($user) {
            $customer = Customer::with(['addresses', 'orders.deliveryLogs', 'orders.courier'])
                ->where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            if ($customer) {
                $orders = $customer->orders()->with(['deliveryLogs', 'courier'])->latest()->take(20)->get();
            } else {
                // If user doesn't have a linked customer record, show recent store orders
                $orders = Order::with(['deliveryLogs', 'courier'])->latest()->take(10)->get();
            }
        }

        return view('pages.profile.show', compact('user', 'customer', 'orders'));
    }
}
