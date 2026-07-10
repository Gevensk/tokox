<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Database\QueryException;

class CustomerController extends Controller
{
    public function index()
    {
        $customer = Customer::all();
        return view('customer.index', compact('customer'));
    }

    public function create()
    {
        return view('customer.create');
    }

    public function store(Request $request)
    {
        // Bersih dari atribut 'tipe'
        $request->validate([
            'nama'   => 'required',
            'alamat' => 'required',
            'no_hp'  => 'required',
        ]);

        Customer::create($request->all());

        return redirect('/customer')->with('success', 'Customer berhasil ditambah!');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $customer = Customer::find($id);
        return view('customer.edit', compact('customer')); 
    }

    public function update(Request $request, string $id)
    {
        // Bersih dari atribut 'tipe'
        $request->validate([
            'nama'   => 'required',
            'alamat' => 'required',
            'no_hp'  => 'required',
        ]);

        $customer = Customer::find($id);
        $customer->update($request->all());

        return redirect('/customer')->with('success', 'Data berhasil diubah!');
    }

    public function destroy(string $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $customer->delete();
            return redirect('/customer')->with('success', 'Data berhasil dihapus!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Customer ini tidak dapat dihapus karena sudah memiliki riwayat transaksi (Nota Order / Produk Custom).');
            }
            throw $e;
        }
    }
}