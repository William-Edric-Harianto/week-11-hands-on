<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::all();

        return view('customers.view-customer', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create-customer');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone_number' => 'required|string|min:12|max:20',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            $data['profile_picture'] = $request->file('profile_picture')->store('customers', 'public');
        }

        Customer::create($data);

        return redirect()->route('customer-list');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit-customer', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => 'required|string|min:2|max:100',
            'phone_number' => 'required|string|min:12|max:20',
            'profile_picture' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('profile_picture')) {
            if ($customer->profile_picture) {
                Storage::disk('public')->delete($customer->profile_picture);
            }

            $data['profile_picture'] = $request->file('profile_picture')->store('customers', 'public');
        }

        $customer->update($data);

        return redirect()->route('customer-list');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        if ($customer->profile_picture) {
            Storage::disk('public')->delete($customer->profile_picture);
        }

        $customer->delete();

        return redirect()->route('customer-list');
    }
}
