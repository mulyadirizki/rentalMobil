<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Models\Rental;
use App\Models\T_user;
use App\Models\Rekembali;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Traits\MasterTrait;

class MainController extends Controller
{
    use MasterTrait;

    public function index()
    {
        $user = DB::table('t_user')->join('users', 't_user.id_tuser', '=', 'users.id_tuser')
                ->where('users.roles', 2)
                ->count();
        $rental = Rental::all()
                ->count();
        return view('backend.home', compact('user', 'rental'));
    }

    public function getRentaldmin()
    {
        $data = Rental::select('t_rental.*', 'm_mobil.*', 't_user.nama', 't_user.no_hp',
            (DB::raw('DATEDIFF(t_rental.tgl_kembali, t_rental.tgl_rental) as lama_sewa')))
            ->leftJoin('m_mobil', 't_rental.id_mobil', '=', 'm_mobil.id_mobil')
            ->leftJoin('t_user', 't_rental.id_tuser', '=', 't_user.id_tuser')
            ->leftJoin('t_pembayaran', 't_pembayaran.id_rental', '=', 't_pembayaran.id_pembayaran')
            ->get();
        return view('backend.data.rental', compact('data'));
    }

    public function getUserdmin()
    {
        $data = T_user::select('t_user.*', 'users.email',
            (DB::raw('(CASE WHEN t_user.j_kel = 1 THEN "Laki - Laki" WHEN t_user.j_kel = 2 THEN "Perempuan" ELSE "-" END) as jkel')))
            ->leftJoin('users', 'users.id_tuser', '=', 't_user.id_tuser')
            ->where('users.roles', 2)
            ->get();
        return view('backend.data.user', compact('data'));
    }

    // rental kembali
    public function getRentalKembali(Request $request)
    {
        $data = Rental::select('t_rental.*', 'm_mobil.*', 't_user.nama', 't_user.no_hp',
        (DB::raw('DATEDIFF(t_rental.tgl_kembali, t_rental.tgl_rental) as lama_sewa')),
        (DB::raw('(CASE
                        WHEN CURDATE() > t_rental.tgl_kembali THEN "Expired tgl kembali"
                        WHEN CURDATE() = t_rental.tgl_kembali THEN "Hari ini dikembalikan"
                        ELSE "Belum Expired tgl Kembali" END) as tgl_expired')))
        ->leftJoin('m_mobil', 't_rental.id_mobil', '=', 'm_mobil.id_mobil')
        ->leftJoin('t_user', 't_rental.id_tuser', '=', 't_user.id_tuser')
        ->where('status_rental', 1)
        ->get();
        return view('backend.rental.index', compact('data'));
    }

    public function rentalKembaliStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_rental'      => 'required',
            'kondisi_mobil'  => 'required'
        ]);

        //if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $id_ren_kemb = $this->idCreate('t_rental_kembali', 'id_ren_kemb');
            DB::transaction(function () use ($request, $id_ren_kemb) {
                // create tuser
                Rekembali::create([
                    'id_ren_kemb'    => $id_ren_kemb,
                    'id_rental'      => $request->id_rental,
                    'tgl_diterima'   => $request->tgl_diterima,
                    'kondisi_mobil'  => $request->kondisi_mobil
                ]);

                // create user
                Rental::where('id_rental', $request->id_rental)
                    ->update([
                        'status_rental' => 2
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'success create data'
            ]);
        }catch (\Exception $e) {
            //return JSON process insert failed
            return response()->json([
                'success' => false,
                'message' => $e,
            ], 422);
        }

        // $save = Rekembali::create([
        //     'id_ren_kemb'    => $id_ren_kemb,
        //     'id_rental'      => $request->id_rental,
        //     'tgl_diterima'   => $request->tgl_diterima,
        //     'kondisi_mobil'  => $request->kondisi_mobil
        // ]);

        // if ($save) {
        //     $data = Rekembali::where('t_rental_kembali.id_ren_kemb', $id_ren_kemb)
        //         ->select('t_rental_kembali.*')
        //         ->first();

        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Success create data',
        //         'data'    => $data
        //     ], 200);
        // } else {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Failed create data'
        //     ], 200);
        // }
    }
}
