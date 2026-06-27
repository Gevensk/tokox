<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penjahit;

class PenjahitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $penjahit= Penjahit::all();
        return view('penjahit.index',compact('penjahit'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('penjahit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'nama' =>'required',
            'alamat' =>'required',
            'no_hp' =>'required'
        ]);

        Penjahit::create($request->all());

        return redirect('/penjahit')->with('success','Penjahit berhasil ditambah!');
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
        $penjahit = Penjahit::find($id);
        return view('penjahit.edit',compact('penjahit'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'nama'=>'required',
            'alamat'=>'required',
            'no_hp'=>'required'
        ]);
        $penjahit =Penjahit::find($id);
        $penjahit->update($request->all());

        return redirect('/penjahit')->with('success','Data berhasil diubah  !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $penjahit = Penjahit::findOrFail($id);
            $penjahit->delete();
            return redirect('/penjahit')->with('success', 'Data berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000') {
                return redirect()->back()->with('error', 'Penjahit ini tidak dapat dihapus karena sudah memiliki riwayat transaksi (Nota Produksi / Job Order).');
            }
            throw $e;
        }
    }
}
