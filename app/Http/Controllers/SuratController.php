<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Surat;
use App\Identitas;
use App\Kematian;
use App\Staff;
use App\Penduduk;
use App\Kelahiran;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $path_folder="master_surat";
    protected $identitas = array();

    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request,$file_name)
    {
        $surat = new Surat;
        
        
        $surat->nama_surat =  $surat->getsuratValue($request->kode_surat,"title");
        $surat->tanggal = Date("Y-m-d h:i:s");
        $surat->hal = $request->hal;
        $surat->surat_filename = $file_name;
        $surat->penduduk_id = $request->penduduk_id;
        $surat->staff_id =null;
        $surat->created_at = Date("Y-m-d h:i:s");
        $surat->updated_at = Date("Y-m-d h:i:s");
        $surat->save();
        
        return $surat;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function format_surat()
    {
        $surat = new Surat;
        $result =   $surat->format_surat;
        return View("pages.surat.format_surat",['surat' => $result]);
    }
    public function download($file)
    {
        return response()->download(public_path('master_surat/'.$file));
    }
    public function upload(Request $request)
    {
        $surat = new Surat;

		$file = $request->file('file');
        
        $to_folder = 'master_surat';
        $file->move($to_folder,$surat->getnamefile($request->kode));
        return redirect()->back();
    }

    public function daftar_cetak_surat()
    {
        $surat = new Surat;
        $result =   $surat->format_surat;
        return View("pages.surat.daftar_cetak_surat",['surat' => $result]);

    }
    public function form_cetak_surat($kode_surat)
    {
        $surat = new Surat;
  
        $staff = Staff::all();

        if($kode_surat == "S02"){
            $penduduk = Kematian::join('penduduk', 'penduduk.penduduk_id', '=', 'kematian.penduduk_id')
            ->select('kematian.*','penduduk.nik','penduduk.full_name')
            ->get();
           
            return View($surat->getsuratValue($kode_surat,"page"),['kode_surat' => $kode_surat,'penduduk' => $penduduk,'staff' => $staff]);
        }else if($kode_surat == "S04"){
            $penduduk = Penduduk::all();
           
            return View($surat->getsuratValue($kode_surat,"page"),['kode_surat' => $kode_surat,'penduduk' => $penduduk,'staff' => $staff]);
        }else if($kode_surat == "S01")
        {
            $penduduk = Kelahiran::join('penduduk', 'penduduk.penduduk_id', '=', 'kelahiran.penduduk_id')
            ->select('kelahiran.*','penduduk.nik','penduduk.full_name')
            ->get();
            return View($surat->getsuratValue($kode_surat,"page"),['kode_surat' => $kode_surat,'penduduk' => $penduduk,'staff' => $staff]);

        }
      
    }
    public function cetak_surat_kematian(Request $request)
    {
         $surat = new Surat;
        
         $staff = Staff::find($request->staf_id);

         $penduduk = Penduduk::join('wilayah as dusun', 'dusun.wilayah_id', '=', 'penduduk.wilayah_dusun')
         ->join('wilayah as  rw', 'rw.wilayah_id', '=', 'penduduk.wilayah_rw')
         ->join('wilayah as  rt', 'rt.wilayah_id', '=', 'penduduk.wilayah_rt')
         ->select('penduduk.*', 'dusun.wilayah_nama as DUSUN','rw.wilayah_nama as RW','rt.wilayah_nama as RT')
         ->where('penduduk.penduduk_id',$request->penduduk_id)->first();

         $kematian = Kematian::where('penduduk_id',$request->penduduk_id)->first();

        $this->getIdentitalDesaAll();
        $document =  file_get_contents(public_path('master_surat').'\\'.$surat->getnamefile($request->kode_surat));
 
        $document = str_replace("[SEBUTAN_KABUPATEN]",$this->getIdentitas("sebutan_kabupaten"), $document);
        $document = str_replace("[NAMA_KAB]", $this->getIdentitas("nama_kab"), $document);
        $document = str_replace("[NAMA_KEC]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[SEBUTAN_DESA]",$this->getIdentitas("sebutan_desa"), $document);
        $document = str_replace("[NAMA_DES]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[alamat_des]", $this->getIdentitas("alamat_desa"), $document);
        
        
        $document = str_replace("[judul_surat]", $surat->getTitleFile($request->kode_surat), $document);
        $document = str_replace("[nomor_surat]", $request->nomor_surat, $document);
        $document = str_replace("[tahun]", Date("Y"), $document);
        
        
        $document = str_replace("[jabatan]", $staff->staff_posisi, $document);
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[nama_kec]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[sebutan_kabupaten]", $this->getIdentitas("sebutan_kabupaten"), $document);
        $document = str_replace("[nama_kab]", $this->getIdentitas("nama_kab"), $document);
        $document = str_replace("[nama_provinsi]", $this->getIdentitas("nama_prov"), $document);
        
        
        $document = str_replace("[nama]", $penduduk->full_name, $document);
        $document = str_replace("[no_ktp]", $penduduk->nik, $document);
        $document = str_replace("[sex]", $penduduk->jekel, $document);
        $document = str_replace("[tempatlahir]",$penduduk->tempat_lahir, $document);
        $document = str_replace("[tanggallahir]",date("d-m-Y",strtotime($penduduk->tanggal_lahir)), $document);
        $document = str_replace("[agama]", $penduduk->agama, $document);
        $document = str_replace("[rt]", $penduduk->RT, $document);
        $document = str_replace("[rw]", $penduduk->RW, $document);
        $document = str_replace("[dusun]", $penduduk->DUSUN, $document);
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[nama_kec]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[nama_kab]", $this->getIdentitas("nama_kab"), $document);
        
        
        $document = str_replace("[form_hari]", date("l",strtotime($kematian->tgl_kematian)), $document);
        $document = str_replace("[form_tanggal_mati]", date("d",strtotime($kematian->tgl_kematian)), $document);
        $document = str_replace("[form_jam]", $kematian->jam_kematian, $document);
        $document = str_replace("[form_tempat_mati]", $kematian->tempat_kematian, $document);
        $document = str_replace("[sebab_nama]", $kematian->sebab_kematian, $document);
        
        
        $document = str_replace("[nama_pelapor]", $request->nama_pelapor, $document);
        $document = str_replace("[nik_pelapor]", $request->nik_pelapor, $document);
        $document = str_replace("[tanggal_lahir_pelapor]", date("d-m-Y",strtotime($request->tanggal_lahir_pelapor)), $document);
        $document = str_replace("[pekerjaanpelapor]", $request->pekerjaan_pelapor, $document);
        $document = str_replace("[alamat_pelapor]", $request->alamat_pelapor, $document);
        $document = str_replace("[hubungan_pelapor]", $request->hubungan_pelapor, $document);
        
        
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[tgl_surat]", Date("d-m-Y"), $document);
        $document = str_replace("[jabatan]", $staff->staff_posisi, $document);
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        
        $document = str_replace("[nama_pamong]", $staff->nama_staff, $document);
        $document = str_replace("[pamong_nip]", $staff->staff_nip, $document);

        $document = str_replace("[kode_desa]", $this->getIdentitas("kode_pos"), $document);
        $document = str_replace("[kode_surat]", $request->nomor_surat, $document);

        $filename = $surat->getTitleFile($request->kode_surat)."_".Date("Ymdhis").".doc";
        $filepath = public_path('data_surat')."\\".$filename;
        
        file_put_contents($filepath, $document);
        
        $result =   $this->save($request,$filename);


        return redirect('surat/get-surat/'.$result->surat_id);
    }
    public function cetak_surat_pengantar(Request $request)
    {
         $surat = new Surat;
        
         $staff = Staff::find($request->staf_id);

         $penduduk = Penduduk::join('wilayah as dusun', 'dusun.wilayah_id', '=', 'penduduk.wilayah_dusun')
         ->join('wilayah as  rw', 'rw.wilayah_id', '=', 'penduduk.wilayah_rw')
         ->join('wilayah as  rt', 'rt.wilayah_id', '=', 'penduduk.wilayah_rt')
         ->select('penduduk.*', 'dusun.wilayah_nama as DUSUN','rw.wilayah_nama as RW','rt.wilayah_nama as RT')
         ->where('penduduk.penduduk_id',$request->penduduk_id)->first();


        $this->getIdentitalDesaAll();
        $document =  file_get_contents(public_path('master_surat').'\\'.$surat->getnamefile($request->kode_surat));
 
        $document = str_replace("[SEBUTAN_KABUPATEN]",$this->getIdentitas("sebutan_kabupaten"), $document);
        $document = str_replace("[NAMA_KAB]", $this->getIdentitas("nama_kab"), $document);
        $document = str_replace("[NAMA_KEC]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[sebutan_desa]",$this->getIdentitas("sebutan_desa"), $document);
        $document = str_replace("[NAMA_DES]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[alamat_des]", $this->getIdentitas("alamat_desa"), $document);
        
        
        $document = str_replace("[judul_surat]", $surat->getTitleFile($request->kode_surat), $document);
        $document = str_replace("[nomor_surat]", $request->nomor_surat, $document);
        $document = str_replace("[tahun]", Date("Y"), $document);
        
        $document = str_replace("[sebutan_kabupaten]", $this->getIdentitas("sebutan_kabupaten"), $document);
        $document = str_replace("[nama_provinsi]", $this->getIdentitas("nama_prov"), $document);
        
        
        $document = str_replace("[nama]", $penduduk->full_name, $document);
        $document = str_replace("[tempatlahir]",$penduduk->tempat_lahir, $document);
        $document = str_replace("[tanggallahir]",date("d-m-Y",strtotime($penduduk->tanggal_lahir)), $document);
        $document = str_replace("[usia]",date_diff(date_create($penduduk->tanggal_lahir), date_create('now'))->y, $document);
        $document = str_replace("[warga_negara]","Indonesia", $document);
        $document = str_replace("[agama]", $penduduk->agama, $document);
        $document = str_replace("[sex]", $penduduk->jekel, $document);
        $document = str_replace("[pekerjaan]", $penduduk->pekerjaan, $document);
        $document = str_replace("[rt]", $penduduk->RT, $document);
        $document = str_replace("[rw]", $penduduk->RW, $document);
        $document = str_replace("[dusun]", $penduduk->DUSUN, $document);
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[nama_kec]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[nama_kab]", $this->getIdentitas("nama_kab"), $document);
        
        
        
        
        $document = str_replace("[no_ktp]", $request->nama_pelapor, $document);
        $document = str_replace("[no_kk]", $request->nik_pelapor, $document);
        $document = str_replace("[keperluan]",$request->hal, $document);
        $document = str_replace("[mulai_berlaku]", date("d-m-Y",strtotime($request->berlaku_mulai)), $document);
        $document = str_replace("[tgl_akhir]", date("d-m-Y",strtotime($request->berlaku_sampai)), $document);
        $document = str_replace("[gol_darah]", $request->golongan_darah, $document);
        
        
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[tgl_surat]", Date("d-m-Y"), $document);
        $document = str_replace("[jabatan]", $staff->staff_posisi, $document);
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        
        
        $document = str_replace("[nama]", $penduduk->full_name, $document);
        $document = str_replace("[nama_pamong]", $staff->nama_staff, $document);

        $document = str_replace("[kode_desa]", $this->getIdentitas("kode_pos"), $document);
        $document = str_replace("[kode_surat]", $request->nomor_surat, $document);

        $filename = $surat->getTitleFile($request->kode_surat)."_".Date("Ymdhis").".doc";
        $filepath = public_path('data_surat')."\\".$filename;
        
        file_put_contents($filepath, $document);
        
        $result =   $this->save($request,$filename);


        return redirect('surat/get-surat/'.$result->surat_id);
    }
    public function cetak_surat_kelahiran(Request $request)
    {
         $surat = new Surat;
        
         $staff = Staff::find($request->staf_id);

        $this->getIdentitalDesaAll();
        $document =  file_get_contents(public_path('master_surat').'\\'.$surat->getnamefile($request->kode_surat));
 
        $document = str_replace("[SEBUTAN_KABUPATEN]",$this->getIdentitas("sebutan_kabupaten"), $document);
        $document = str_replace("[NAMA_KAB]", $this->getIdentitas("nama_kab"), $document);
        $document = str_replace("[SEBUTAN_KECAMATAN]", $this->getIdentitas("sebutan_kecamatan"), $document);
        $document = str_replace("[NAMA_KEC]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[sebutan_desa]",$this->getIdentitas("sebutan_desa"), $document);
        $document = str_replace("[NAMA_DES]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[alamat_des]", $this->getIdentitas("alamat_desa"), $document);
        
        
        
        $document = str_replace("[judul_surat]", $surat->getTitleFile($request->kode_surat), $document);
        $document = str_replace("[kode_surat]", $request->nomor_surat, $document);
        $document = str_replace("[nomor_surat]", $request->nomor_surat, $document);
        $document = str_replace("[kode_desa]",$this->getIdentitas("kode_pos"), $document);
        $document = str_replace("[tahun]", Date("Y"), $document);
        

        $document = str_replace("[jabatan]", $staff->staff_posisi, $document);
        $document = str_replace("[nama_des]", $this->getIdentitas("nama_desa"), $document);
        $document = str_replace("[sebutan_kecamatan]", $this->getIdentitas("sebutan_kecamatan"), $document);
        $document = str_replace("[nama_kec]", $this->getIdentitas("nama_kec"), $document);
        $document = str_replace("[sebutan_kabupaten]", $this->getIdentitas("sebutan_kabupaten"), $document);
        $document = str_replace("[nama_kab]", $this->getIdentitas("nama_kab"), $document);
        $document = str_replace("[nama_provinsi]", $this->getIdentitas("nama_prov"), $document);
        
        
      
        
        $filename = $surat->getTitleFile($request->kode_surat)."_".Date("Ymdhis").".doc";
        $filepath = public_path('data_surat')."\\".$filename;
        
        file_put_contents($filepath, $document);
        
        $result =   $this->save($request,$filename);


        return redirect('surat/get-surat/'.$result->surat_id);
    }
    public function get_surat($id)
    {
        $surat = Surat::find($id);

        $document = public_path("data_surat\\").$surat->surat_filename;

        return response()->download($document);
       
    }
    
    public function getIdentitalDesaAll()
    {
        
      $data =  Identitas::all();
      foreach ($data as $key => $val) {
         array_push($this->identitas,array(
            "key"=> $val["identitas_key"],
            "title" => $val["identitas_titel"],
            "value" => $val["identitas_value"]
         ));
      }
    }
    public function getIdentitas($paramkey,$res = "value")
    {

        foreach ($this->identitas as $key => $val) {

            if($val["key"] == $paramkey)
            {
                return  $val[$res];
            }
        }
        return null;

    }
}