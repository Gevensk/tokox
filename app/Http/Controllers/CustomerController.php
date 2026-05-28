<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $customer = Customer::all();
        return view('customer.index',compact('customer'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('customer.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'   => 'required',
            'alamat' => 'required',
            'no_hp'  => 'required',
            'tipe'   => 'required|in:internal,customer' // Validasi ketat sesuai ENUM
        ]);

        Customer::create($request->all());

        return redirect('/customer')->with('success', 'Customer berhasil ditambah!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $customer = Customer::find($id);
          return view('customer.edit', compact('customer')); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'nama'   => 'required',
            'alamat' => 'required',
            'no_hp'  => 'required',
            'tipe' => 'required'
        ]);

        $customer = Customer::find($id);
        $customer->update($request->all());

        return redirect('/customer')->with('success', 'Data berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $customer = Customer::find($id);
        $customer->delete();
        
        return redirect('/customer')->with('success', 'Data berhasil dihapus!');
    }
}
