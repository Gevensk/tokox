<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BahanBaku;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $bahan_baku=BahanBaku::all();
        return view('bahan_baku.index',compact('bahan_baku')) ;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('bahan_baku.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nama' =>'required',
            'satuan' => 'required'
        ]);

            BahanBaku::create([
                'nama' => $request->nama,
                'satuan' => $request->satuan,
                'stok_total' => 0,          // Set awal 0
                'harga_rata_rata' => 0,     // Set awal 0
            ]);

            return redirect('/bahan_baku')->with('success','Bahan baku berhasil ditambahkan');
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
        $bahan_baku =BahanBaku::find($id);
        return view('bahan_baku.edit',compact('bahan_baku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        //
        $request->validate([
            'nama'=> 'required',
            'satuan'=>'required'
        ]);

        $bahan_baku=BahanBaku::find($id);
        $bahan_baku->update($request->all());

        return redirect('/bahan_baku')->with('success','Data berhasil diubah!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $bahan_baku=BahanBaku::find($id);
        $bahan_baku->delete();
        return redirect('/bahan_baku')->with('success','Data berhasil dihapus!');
    }
}
