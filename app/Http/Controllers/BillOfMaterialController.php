<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProdukVarian;
use App\Models\BahanBaku;
use App\Models\BillOfMaterial;
use Illuminate\Support\Facades\DB;

class BillOfMaterialController extends Controller
{
    //
    public function index()
{

    $boms = BillOfMaterial::with(['varian.produk', 'bahan_baku'])
            ->get()
            ->groupBy('produk_varian_id');

    return view('bill_of_material.index', compact('boms'));
}
    public function create(){
        $varians =ProdukVarian::with('produk')->get();
        $bahan_bakus = BahanBaku::all();

        return view('bill_of_material.create',compact('varians','bahan_bakus'));
    }

    public function store(Request $request){
        $request->validate([
            'produk_varian_id'=>'required|exists:produk_varian,id',
            'items' => 'required|array',
        ]);

        $exists = BillOfMaterial::where('produk_varian_id',$request->produk_varian_id)->exists();

        if($exists){
            return redirect()->route('bill_of_material.index')->with('error','Bill of material untuk varian ini sudah ada. Silahkan gunakan menu Edit.');
        }

        try{
            DB::beginTransaction();

            foreach ($request->items as $item){
                BillOfMaterial::create([
                    'produk_varian_id'=>$request->produk_varian_id,
                    'bahan_baku_id'=>$item['bahan_baku_id'],
                    'jumlah' =>$item['jumlah'],
                ]);
            }
            DB::commit();
            return redirect()->route('bill_of_material.index')->with('success','Bill of Material berhasil disimpan!');
        }

        catch (\Exception $e){
            DB::rollback();
            return back()->with('error','Terjadi kesalahan: '. $e->getMessage());
        }
    }

    public function edit($produk_varian_id){
        $varian = ProdukVarian::with('produk')->findOrFail($produk_varian_id);
        $current_bom = BillOfMaterial::where('produk_varian_id',$produk_varian_id)->get();
        $bahan_bakus = BahanBaku::all();

        return view('bill_of_material.edit',compact('varian','current_bom','bahan_bakus'));
    }

    public function update(Request $request, $produk_varian_id){
        $request->validate([
            'items' => 'required|array',
            'items.*.bahan_baku_id' => 'required|exists:bahan_baku,id',
            'items.*.jumlah' => 'required|numeric|min:0.0001',
        ]);

        try{
            DB::beginTransaction();

            BillOfMaterial::where('produk_varian_id',$produk_varian_id)->delete();
            foreach($request->items as $item){
                BillOfMaterial::create([
                   'produk_varian_id' => $produk_varian_id,
                   'bahan_baku_id' => $item['bahan_baku_id'],
                   'jumlah' => $item['jumlah'],
                ]);
            }
            DB::commit();
            return redirect()->route('bill_of_material.index')->with('success','Bill of material berhasil diperbarui!');
        }
        catch(\Exception $e){
            DB::rollBack();
            return back()->with('error','Gagal update: '.$e->getMessage());
        }
    }

    public function destroy($produk_varian_id)
    {
        try {
            DB::beginTransaction();

            // Hapus seluruh baris resep yang terikat dengan ID varian ini
            BillOfMaterial::where('produk_varian_id', $produk_varian_id)->delete();

            DB::commit();
            return redirect()->route('bill_of_material.index')
                ->with('success', 'Seluruh resep BoM untuk varian tersebut berhasil dihapus!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('bill_of_material.index')
                ->with('error', 'Gagal menghapus resep: ' . $e->getMessage());
        }
    }
}
