<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier; 
use Illuminate\Database\QueryException;

class SupplierController extends Controller
{
    public function index()
    {
        $supplier = Supplier::all(); 
        return view('supplier.index', compact('supplier'));
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'   => 'required',
            'alamat' => 'required',
            'no_hp'  => 'required'
        ]);

        Supplier::create($request->all());

        return redirect('/supplier')->with('success', 'Supplier berhasil ditambah!');
    }

    public function edit($id)
    {
        $supplier = Supplier::find($id);
        return view('supplier.edit', compact('supplier')); 
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama'   => 'required',
            'alamat' => 'required',
            'no_hp'  => 'required'
        ]);

        $supplier = Supplier::find($id);
        $supplier->update($request->all());

        return redirect('/supplier')->with('success', 'Data berhasil diubah!');
    }

    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->delete();
            return redirect('/supplier')->with('success', 'Data berhasil dihapus!');
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Supplier ini tidak dapat dihapus karena sudah memiliki riwayat transaksi (Nota Pembelian).');
            }
            throw $e;
        }
    }
}