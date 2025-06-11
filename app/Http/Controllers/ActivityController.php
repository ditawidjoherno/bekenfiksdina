<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function index()
{
    $activities = UserActivity::with('user')
        ->orderByDesc('created_at')
        ->take(10)
        ->get()
        ->map(function ($activity) {
            $user = $activity->user;

            return [
                'date' => $activity->created_at->format('M d, Y'),
                'time' => $activity->created_at->format('h.i a'),
                'name' => $user->nama,
                'role' => ucfirst($user->role),
                'action' => $activity->action,
                'avatar' => $user->foto_profil
                    ? asset('storage/' . $user->foto_profil)
                    : ($user->role === 'guru'
                        ? asset('/images/profiladmin.jpg')
                        : asset('/images/profilsiswa.jpg')),
                'color' => $user->role === 'guru' ? 'bg-pink-300' : 'bg-purple-300',
                'textColor' => 'text-black',
                'actionColor' => 'text-black',
                'roleColor' => $user->role === 'guru' ? 'text-[#EC4899]' : 'text-[#7E22CE]',
            ];
        });

    return response()->json($activities);
}
public function ongoing()
{
    $today = Carbon::today();

    $ekskulActivities = DB::table('kegiatan_ekskuls')
        ->join('ekskuls', 'kegiatan_ekskuls.ekskul_id', '=', 'ekskuls.id')
        ->select(
            'kegiatan_ekskuls.title as name',
            DB::raw("CONCAT(ekskuls.name, ' - Ekstrakurikuler') as category"),
            'kegiatan_ekskuls.created_at as start',
            'kegiatan_ekskuls.date as end',
            DB::raw("DATEDIFF(kegiatan_ekskuls.date, kegiatan_ekskuls.created_at) as totalDays")
        )
        ->whereDate('kegiatan_ekskuls.created_at', '<=', $today)
        ->whereDate('kegiatan_ekskuls.date', '>=', $today);

    $studyTourActivities = DB::table('info_karya_wisata')
        ->select(
            'info_karya_wisata.title as name',
            DB::raw("'Karya Wisata' as category"),
            'info_karya_wisata.created_at as start',
            'info_karya_wisata.tanggal as end',
            DB::raw("DATEDIFF(info_karya_wisata.tanggal, info_karya_wisata.created_at) as totalDays")
        )
        ->whereDate('info_karya_wisata.created_at', '<=', $today)
        ->whereDate('info_karya_wisata.tanggal', '>=', $today);


    $ongoingActivities = $ekskulActivities
        ->unionAll($studyTourActivities)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $ongoingActivities
    ]);
}

public function KegiatanSelesai()
{
    $today = Carbon::today()->toDateString();

    $ekskulActivities = DB::table('kegiatan_ekskuls')
        ->join('ekskuls', 'kegiatan_ekskuls.ekskul_id', '=', 'ekskuls.id')
        ->select(
            'kegiatan_ekskuls.title as name',
            DB::raw("CONCAT(ekskuls.name, ' - Ekstrakurikuler') as category"),
            'kegiatan_ekskuls.created_at as start',
            'kegiatan_ekskuls.date as end'
        )
        ->whereDate('kegiatan_ekskuls.date', '<', $today);

    $studyTourActivities = DB::table('info_karya_wisata')
        ->select(
            'info_karya_wisata.title as name',
            DB::raw("'Karya Wisata' as category"),
            'info_karya_wisata.created_at as start',
            'info_karya_wisata.tanggal as end'
        )
        ->whereDate('info_karya_wisata.tanggal', '<', $today);

    $pameranActivities = DB::table('info_pameran')
        ->select(
            'info_pameran.title as name',
            DB::raw("'Pameran' as category"),
            'info_pameran.created_at as start',
            'info_pameran.tanggal as end'
        )
        ->whereDate('info_pameran.tanggal', '<', $today);

    $activities = $ekskulActivities
        ->unionAll($studyTourActivities)
        ->unionAll($pameranActivities)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $activities
    ]);
}


public function PesertaOngoing()
{
    $today = Carbon::now()->toDateString();

    $ekskulParticipants = DB::table('anggota_ekskul')
        ->join('kegiatan_ekskuls', 'anggota_ekskul.ekskul_id', '=', 'kegiatan_ekskuls.id')
        ->join('users', 'anggota_ekskul.nisn', '=', 'users.nisn')
        ->whereDate('kegiatan_ekskuls.created_at', '<=', $today)
        ->whereDate('kegiatan_ekskuls.date', '>=', $today)
        ->select(
            'users.id',
            'users.nama',
            'users.nisn',
            'users.kelas',
            DB::raw("'Ekstrakurikuler' as jenis_kegiatan")
        );

    $studyTourParticipants = DB::table('study_tour')
        ->join('study_tour_info', 'study_tour.id', '=', 'study_tour_info.id')
        ->join('users', 'study_tour.user_id', '=', 'users.id')
        ->whereDate('study_tour_info.created_at', '<=', $today)
        ->whereDate('study_tour_info.tanggal', '>=', $today)
        ->select(
            'users.id',
            'users.nama',
            'users.nisn',
            'users.kelas',
            DB::raw("'Study Tour' as jenis_kegiatan")
        );

    $pameranParticipants = DB::table('pameran')
        ->join('info_pameran', 'pameran.id', '=', 'info_pameran.id')
        ->join('users', 'pameran.user_id', '=', 'users.id')
        ->whereDate('info_pameran.created_at', '<=', $today)
        ->whereDate('info_pameran.tanggal', '>=', $today)
        ->select(
            'users.id',
            'users.nama',
            'users.nisn',
            'users.kelas',
            DB::raw("'Pameran' as jenis_kegiatan")
        );

    $participants = $ekskulParticipants
        ->unionAll($studyTourParticipants)
        ->unionAll($pameranParticipants)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $participants
    ]);
}


public function PenanggungJawab()
    {
        $data = DB::table('ekskuls')
            ->join('users', 'ekskuls.mentor', '=', 'nama')
            ->select(
                'users.nama as nama',
                'users.nip',
                'users.kelas'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

public function semuaKegiatan()
{
    $ekskulActivities = DB::table('kegiatan_ekskuls')
        ->join('ekskuls', 'kegiatan_ekskuls.ekskul_id', '=', 'ekskuls.id')
        ->select(
            'ekskuls.name as nama_kegiatan',
            DB::raw("'Ekstrakurikuler' as category"),
            'kegiatan_ekskuls.created_at as start',
            'kegiatan_ekskuls.date as end',
            DB::raw("DATEDIFF(kegiatan_ekskuls.date, kegiatan_ekskuls.created_at) as totalDays")
        );

    $studyTourActivities = DB::table('info_karya_wisata')
        ->select(
            'title as nama_kegiatan',
            DB::raw("'Karya Wisata' as category"),
            'created_at as start',
            'tanggal as end',
            DB::raw("DATEDIFF(tanggal, created_at) as totalDays")
        );


    $allActivities = $ekskulActivities
        ->unionAll($studyTourActivities)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $allActivities
    ]);
}


public function jumlahKegiatan()
{
    $today = Carbon::today();

    $ekskulBerlangsung = DB::table('kegiatan_ekskuls')
        ->whereDate('created_at', '<=', $today)
        ->whereDate('date', '>=', $today)
        ->count();

    $ekskulSelesai = DB::table('kegiatan_ekskuls')
        ->whereDate('date', '<', $today)
        ->count();

    $studyTourBerlangsung = DB::table('info_karya_wisata')
        ->whereDate('created_at', '<=', $today)
        ->whereDate('tanggal', '>=', $today)
        ->count();

    $studyTourSelesai = DB::table('info_karya_wisata')
        ->whereDate('tanggal', '<', $today)
        ->count();

    $berlangsung = $ekskulBerlangsung + $studyTourBerlangsung ;
    $selesai = $ekskulSelesai + $studyTourSelesai;

    $pesertaEkskul = DB::table('anggota_ekskul')
        ->join('kegiatan_ekskuls', 'anggota_ekskul.ekskul_id', '=', 'kegiatan_ekskuls.id')
        ->whereDate('kegiatan_ekskuls.created_at', '<=', $today)
        ->whereDate('kegiatan_ekskuls.date', '>=', $today)
        ->count();

    $pesertaStudyTour = DB::table('study_tour')
        ->join('study_tour_info', 'study_tour.id', '=', 'study_tour_info.id')
        ->whereDate('study_tour_info.created_at', '<=', $today)
        ->whereDate('study_tour_info.tanggal', '>=', $today)
        ->count();

    $pesertaPameran = DB::table('pameran')
        ->join('info_pameran', 'pameran.id', '=', 'info_pameran.id')
        ->whereDate('info_pameran.created_at', '<=', $today)
        ->whereDate('info_pameran.tanggal', '>=', $today)
        ->count();

    $peserta = $pesertaEkskul + $pesertaStudyTour + $pesertaPameran;

    $penanggungJawabEkskul = DB::table('ekskuls')
        ->join('users', 'ekskuls.mentor', '=', 'users.nama')
        ->count(DB::raw('DISTINCT users.nip'));


    return response()->json([
        'status' => 'success',
        'data' => [
            'berlangsung' => $berlangsung,
            'selesai' => $selesai,
            'peserta' => $peserta,
            'penanggung_jawab' => $penanggungJawabEkskul,
        ]
    ]);
}

public function PerjalananSebelumnya()
{
    $studyTour = DB::table('info_karya_wisata')
        ->select(
            'id',
            DB::raw("'Karya Wisata' as title"),
            'tanggal',
            'title as location'
        )
        ->get();

    $pameran = DB::table('info_pameran')
        ->select(
            'id',
            DB::raw("'Pameran' as title"),
            'tanggal',
            'title as location'
        )
        ->get();

    $combined = $studyTour->merge($pameran)->values();

    return response()->json($combined);
}

}
