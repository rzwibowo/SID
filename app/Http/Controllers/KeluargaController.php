<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Wilayah;
use App\Penduduk;
use App\Keluarga;

class KeluargaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $keluarga = new Keluarga;

        $pages = 0;
        $page = 0;
        $showdata = 10;

        $kelaurga = $keluarga->newQuery();

        

        $keluarga = $keluarga::leftjoin('penduduk', 'penduduk.penduduk_id', '=', 'keluarga.kepala_keluarga')
        ->select('keluarga.*', 'penduduk.full_name');

        if(isset($request->search))
        {
            $keluarga->where('keluarga.no_kk','like',''.$request->search.'%');
            $keluarga->orWhere('penduduk.full_name', 'like',''.$request->search.'%');
        }

        if(isset($request->page) && !empty($request->page))
        {
            $page = $request->page;

        }

        if(isset($request->showdata) && !empty($request->showdata))
        {
            $showdata = $request->showdata;

        }

        
        if(isset($request->order) && !empty($request->order))
        {
            if($request->order == "desc")
            {
                $keluarga->orderBy('penduduk.full_name', 'desc');
            }else
            {
                $keluarga->orderBy('penduduk.full_name', 'asc');
            }
        }else
        {
            $keluarga->orderBy('penduduk.full_name', 'asc');
        }
        
        $result  = $keluarga->get();

        $keluarga->offset(($page * $showdata));
        $keluarga->limit($showdata);

        $keluarga = $keluarga->get();
         
        $pages =  ceil(count($result) / $showdata);
      
        return view('pages.kependudukan.keluarga.index',['keluarga' => $keluarga,'pages' => $pages,'page' => $page,'showdata' => $showdata]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dusun =  Wilayah::where('wilayah_part',1)->get();
        $penduduk = Penduduk::where("keluarga_id",NULL)->get();
        return view('pages.kependudukan.keluarga.form',['dusun' => $dusun,'penduduk'=>$penduduk]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $keluarga = new Keluarga();
        
       $keluarga->kepala_keluarga = $request->kepala_keluarga;
       $keluarga->no_kk= $request->no_kk;
       $keluarga->wilayah_dusun = $request->wilayah_dusun;
       $keluarga->wilayah_rw = $request->wilayah_rw;
       $keluarga->wilayah_rt = $request->wilayah_rt;
       $keluarga->alamat_keluarga = $request->alamat_keluarga;
       $keluarga->created_at = Date("Y-m-d h:i:s");
       $keluarga->updated_at = Date("Y-m-d h:i:s");

       if($keluarga->save())
       {
        $penduduk = Penduduk::find($keluarga->kepala_keluarga);
        $penduduk->keluarga_id =  $keluarga->keluarga_id;
        $penduduk->hubungan_keluarga =  $keluarga->hubungan_keluarga['kepala_keluarga'];
        $penduduk->save();
       }

       return redirect()->action('KeluargaController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $keluarga = Keluarga::leftjoin('wilayah as dusun','dusun.wilayah_id', '=','keluarga.wilayah_dusun')
        ->leftjoin('wilayah as rw','rw.wilayah_id', '=','keluarga.wilayah_rw')
        ->leftjoin('wilayah as rt','rt.wilayah_id', '=','keluarga.wilayah_rt')
        ->leftjoin('penduduk', 'penduduk.penduduk_id', '=', 'keluarga.kepala_keluarga')
        ->where('keluarga.keluarga_id','=',$id)
        ->select('keluarga.*', 'penduduk.full_name','dusun.wilayah_nama as DUSUN','rw.wilayah_nama as RW','rt.wilayah_nama as RT')->first();
     
        $penduduk = Penduduk::where('keluarga_id',$keluarga->keluarga_id)->get();

       $penduduk_baru = Penduduk::where('keluarga_id',NULL)->get();

        return view('pages.kependudukan.keluarga.view',['keluarga' =>$keluarga,'penduduk' =>$penduduk,'penduduk_baru' => $penduduk_baru]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $keluarga = Keluarga::find($id);

        $wilayah = new Wilayah();

        $dusun = Wilayah::where('wilayah_part',$wilayah->part['dusun'])->get();
        $rw =  Wilayah::where('wilayah_part',$wilayah->part['rw'])->where('wilayah_dusun',$keluarga->wilayah_dusun)->get();
        $rt = Wilayah::where('wilayah_part',$wilayah->part['rt'])->where('wilayah_rw',$keluarga->wilayah_rw)->get();

        $penduduk = Penduduk::where("keluarga_id",NULL)->orWhere('keluarga_id',$keluarga->keluarga_id)->get();

        return view('pages.kependudukan.keluarga.edit',['penduduk'=>$penduduk,'dusun' => $dusun,'rw'=>$rw,'rt' => $rt,'keluarga' => $keluarga]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $keluarga = Keluarga::find($id);
        $keluarga->kepala_keluarga = $request->kepala_keluarga;
        $keluarga->no_kk= $request->no_kk;
        $keluarga->wilayah_dusun = $request->wilayah_dusun;
        $keluarga->wilayah_rw = $request->wilayah_rw;
        $keluarga->wilayah_rt = $request->wilayah_rt;
        $keluarga->alamat_keluarga = $request->alamat_keluarga;
        $keluarga->updated_at = Date("Y-m-d h:i:s");

       if($keluarga->save())
       {
        return redirect()->action('KeluargaController@index');
       }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Penduduk::where('keluarga_id',$id)->update(['keluarga_id' => null]);

        $keluarga = Keluarga::find($id);
        $keluarga->delete();
        return redirect()->back();
        
    }
    public function destroy_anggota($id)
    {
       $keluarga = new Keluarga();
       $penduduk = Penduduk::find($id);
       $keluarga_id = $penduduk->keluarga_id;
       $penduduk->keluarga_id = null;
        
        if($penduduk->save())
        {
            if($penduduk->hubungan_keluarga == $keluarga->hubungan_keluarga['kepala_keluarga']){

                $keluarga  = $keluarga::find($keluarga_id);
                $keluarga->kepala_keluarga = null;

                if($keluarga->save())
                {
                    return redirect()->back();
                }
                
            }else
            {
                return redirect()->back();
            }
        }
    }
    public function store_keluarga(Request $request, $id)
    {
        $keluarga = new Keluarga();
        $penduduk = Penduduk::find($request->penduduk_id);
        $penduduk->keluarga_id = $id;
        $penduduk->hubungan_keluarga = $request->hubungan_keluarga;

        if($penduduk->save()){
            if($request->hubungan_keluarga == $keluarga->hubungan_keluarga['kepala_keluarga']){
                $keluarga  = $keluarga::find($id);
                $keluarga->kepala_keluarga = $request->penduduk_id;
                if($keluarga->save())
                {
                    return redirect()->back(); 
                }
            }else{
             return redirect()->back();
            }
        }
    }
    public function edit_keluarga($id)
    {
        $penduduk = Penduduk::join('keluarga as kel','kel.keluarga_id', '=','penduduk.keluarga_id')
        ->where('penduduk.penduduk_id',$id)
        ->select('penduduk.*','kel.no_kk')->first();
        
        return $penduduk;
    }
    public function update_keluarga(Request $request, $id)
    {
        $keluarga = new Keluarga();
        $penduduk = Penduduk::find($id);
        $keluarga  = $keluarga::find($penduduk->keluarga_id);

        if($penduduk->hubungan_keluarga == $keluarga->hubungan_keluarga['kepala_keluarga'] && $request->hubungan_keluarga !== $keluarga->hubungan_keluarga['kepala_keluarga'])
        {
          
            if($keluarga !== null)
            {
                $keluarga->kepala_keluarga = null;
                $keluarga->save();
            }
        }else if($request->hubungan_keluarga == $keluarga->hubungan_keluarga['kepala_keluarga']){

            if($keluarga !== null)
            {
                $keluarga->kepala_keluarga = $penduduk->penduduk_id;
                $keluarga->save();
            }
        }
            
        $penduduk->hubungan_keluarga = $request->hubungan_keluarga;
        if($penduduk->save())
        {
                 
            return redirect()->back();
        }

        
    }
    public function get_data_keluarga($id)
    {
        $result = array();

        $penduduk = Penduduk::find($id);

        $kepala_keluarga = Penduduk::where('status_kependudukan','!=',"Pindah")
        ->where('keluarga_id','=', $penduduk->keluarga_id)
        ->where('penduduk_id','!=',$penduduk->penduduk_id)
        ->where('hubungan_keluarga','=','KEPALA KELUARGA')
        ->first();

        $keluarga = Penduduk::where('status_kependudukan','!=',"Pindah")
        ->where('keluarga_id','=', $penduduk->keluarga_id)
        ->where('penduduk_id','!=',$penduduk->penduduk_id)
        ->where('hubungan_keluarga','!=','KEPALA KELUARGA')
        ->get();

        if($kepala_keluarga != null){
            array_push($result,array(
                "kepala_keluarga" => true,
                "hubungan_keluarga" => $kepala_keluarga->hubungan_keluarga,
                "penduduk_id" =>$kepala_keluarga->penduduk_id,
                "kelamin" =>$kepala_keluarga->jekel,
                "nik" => $kepala_keluarga->nik,
                "nama" => $kepala_keluarga->full_name,
                "kelamin" => $kepala_keluarga->jekel,
                "tanggal_lahir" => $kepala_keluarga->tanggal_lahir,
                "umur" => date_diff(date_create($kepala_keluarga->tanggal_lahir), date_create('now'))->y
            ));
        }

       foreach($keluarga as $key => $val)
       {

        array_push($result,array(
                "kepala_keluarga" => false,
                "hubungan_keluarga" => $val->hubungan_keluarga,
                "kelamin" =>$val->jekel,
                "nik" => $val->nik,
                "penduduk_id" =>$val->penduduk_id,
                "nama" => $val->full_name,
                "kelamin" => $val->jekel,
                "tanggal_lahir" => $val->tanggal_lahir,
                "umur" => date_diff(date_create($val->tanggal_lahir), date_create('now'))->y
            ));
       }

       return $result;

    }
    public function validation_no_kk($kk,$id)
    {

        $keluarga = new Keluarga;
        $keluarga = $keluarga->newQuery();

        $is_exis = false;

        $keluarga = Keluarga::where('no_kk','=',$kk);

        if($id !== "null")
        {
           
            $keluarga->where('keluarga_id','!=',$id);
        }
    
        $res = $keluarga->first();

        if($res !== null)
        {
            $is_exis = true;
        }
        
        return array('response' => $is_exis);
    }


}
