<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with(['user', 'addresses', 'orders']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('deleted', false);
            } elseif ($status === 'inactive') {
                $query->where('deleted', true);
            }
        }

        $customers = $query->paginate(15);
        return $this->successResponse($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $customer = Customer::create($validated);
        return $this->successResponse($customer, 'Customer created', 201);
    }

    public function show(string $id): JsonResponse
    {
        $customer = Customer::with(['user', 'addresses', 'orders'])->find($id);
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }
        return $this->successResponse($customer);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $customer->update($validated);
        return $this->successResponse($customer, 'Customer updated');
    }

    public function destroy(string $id): JsonResponse
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return $this->errorResponse('Customer not found', 404);
        }
        $customer->update(['deleted' => true]);
        return $this->successResponse(null, 'Customer deleted', 204);
    }
}
