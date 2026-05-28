<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier; // 1. WAJIB DITAMBAHKAN

class SupplierController extends Controller
{
    public function index()
    {
        // Menggunakan variabel 'supplier' agar konsisten dengan view sebelumnya
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
        // Pastikan 'supplier' huruf kecil sesuai nama folder di resources/views
        return view('supplier.edit', compact('supplier')); 
    }

    public function update(Request $request, $id) // 2. TAMBAHKAN $id DI SINI
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
        $supplier = Supplier::find($id);
        $supplier->delete();
        
        return redirect('/supplier')->with('success', 'Data berhasil dihapus!');
    }
}