<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aktivitas;
use App\Models\Nasabah;
use App\Models\Dokumentasi;
use App\Models\User;
use App\Events\AktivitasDitambahkan;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Events\AktivitasChanged;
use Illuminate\Support\Facades\DB;


class AktivitasController extends Controller
{
    public function tambahAktivitas(Request $request)
    {
        \Log::info('Memulai proses tambah aktivitas');
        
        try {
            $validatedData = $request->validate([
                'nama_nasabah' => 'required|string',
                'aktivitas' => 'required|string|in:Tabungan,Depo Ritel,NTB - PBO,NOA BTN Move,Transaksi Teller,Transaksi CRM,Operasional MKK,QRIS,EDC,Agen,Kuadran Agen,NOA Payroll,VOA Payroll,NOA Pensiun,VOA Pensiun,VOA E-Batarapos,NOA Giro,Akuisi Satker,CMS,Jumlah PKS PPO,DPK Lembaga',
                'tipe_nasabah' => 'required|string',
                'prospek' => 'nullable|string',
                'nominal_prospek' => 'nullable|integer', 
                'closing' => 'nullable|integer', 
                'status_aktivitas' => 'required|string',
                'keterangan_aktivitas' => 'required|string',
                'created_at' => 'nullable|date', 
                'dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            ]);
            
            
            \Log::info('Validasi berhasil', $validatedData);
    
            $nasabah = Nasabah::where('nama', $request->nama_nasabah)->first();
            if (!$nasabah) {
                \Log::warning('Nasabah tidak ditemukan', ['nama_nasabah' => $request->nama_nasabah]);
                return response()->json([
                    'message' => 'Nasabah dengan nama ' . $request->nama_nasabah . ' tidak ditemukan!',
                ], 404);
            }
            
            \Log::info('Nasabah ditemukan', ['nasabah_id' => $nasabah->id]);
    
            $user = JWTAuth::user();
            \Log::info('User yang login', ['user_id' => $user->id]);
    
            if ($nasabah->created_by != $user->id) {
                \Log::warning('User tidak berhak mengisi aktivitas', ['user_id' => $user->id, 'nasabah_created_by' => $nasabah->created_by]);
                return response()->json([
                    'message' => 'Anda tidak berhak mengisi aktivitas untuk nasabah ini.',
                ], 403);
            }
    
            $aktivitas = new Aktivitas();
            $aktivitas->nasabah_id = $nasabah->id;
            $aktivitas->aktivitas = $request->aktivitas;
            $aktivitas->tipe_nasabah = $request->tipe_nasabah;
            $aktivitas->prospek = $request->prospek; 
            $aktivitas->nominal_prospek = $request->nominal_prospek;
            $aktivitas->closing = $request->closing;
            $aktivitas->status_aktivitas = $request->status_aktivitas;
            $aktivitas->keterangan_aktivitas = $request->keterangan_aktivitas;
            $aktivitas->created_by = $user->id;
    
            $aktivitas->created_at = $request->created_at ? $request->created_at : now();
    
            \Log::info('Aktivitas disiapkan untuk disimpan', $aktivitas->toArray());
            $aktivitas->save();
    
            if ($request->hasFile('dokumentasi')) {
                $filePaths = [];
                foreach ($request->file('dokumentasi') as $file) {
                    $filePath = $file->store('dokumentasi', 'public');
                    $dokumentasi = new Dokumentasi();
                    $dokumentasi->aktivitas_id = $aktivitas->id;
                    $dokumentasi->file_path = $filePath;
                    $dokumentasi->save();
                    $filePaths[] = $filePath;
                }
                \Log::info('Dokumentasi berhasil disimpan', $filePaths);
            }
    
            \Log::info('Aktivitas berhasil disimpan ke database', ['aktivitas_id' => $aktivitas->id]);
    
            event(new AktivitasDitambahkan($aktivitas));
    
            return response()->json([
                'message' => 'Aktivitas berhasil ditambahkan!',
                'data' => $aktivitas
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error saat menambahkan aktivitas', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan saat menambahkan aktivitas.', 'error' => $e->getMessage()], 500);
        }
    }
    

    public function hapusAktivitas(Request $request, $id)
    {
        try {
            $aktivitas = Aktivitas::find($id);
    
            if (!$aktivitas) {
                return response()->json(['error' => 'Aktivitas tidak ditemukan.'], 404);
            }
    
            $aktivitas->delete();
    
            return response()->json(['success' => 'Aktivitas berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Error saat menghapus aktivitas: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus aktivitas.'], 500);
        }
    }
    
    public function updateAktivitas(Request $request, $id)
    {
        \Log::info('Memulai proses update aktivitas', ['aktivitas_id' => $id]);
    
        try {
            $aktivitas = Aktivitas::find($id);
            if (!$aktivitas) {
                \Log::warning('Aktivitas tidak ditemukan', ['aktivitas_id' => $id]);
                return response()->json([
                    'message' => 'Aktivitas tidak ditemukan!',
                ], 404);
            }
    
            $validatedData = $request->validate([
                'aktivitas' => 'nullable|string|in:Tabungan,Depo Ritel,NTB - PBO,NOA BTN Move,Transaksi Teller,Transaksi CRM,Operasional MKK,QRIS,EDC,Agen,Kuadran Agen,NOA Payroll,VOA Payroll,NOA Pensiun,VOA Pensiun,VOA E-Batarapos,NOA Giro,Akuisi Satker,CMS,Jumlah PKS PPO,DPK Lembaga',
                'tipe_nasabah' => 'nullable|string',
                'prospek' => 'nullable|string',
                'nominal_prospek' => 'nullable|integer',
                'closing' => 'nullable|integer',
                'status_aktivitas' => 'nullable|string',
                'aktivitas_sales' => 'nullable|string',
                'keterangan_aktivitas' => 'nullable|string',
            ]);
    
            \Log::info('Validasi berhasil', ['data' => $validatedData]);
    
            $aktivitas->fill($request->only([
                'aktivitas',
                'tipe_nasabah',
                'prospek',
                'nominal_prospek',
                'closing',
                'status_aktivitas',
                'aktivitas_sales',
                'keterangan_aktivitas',
            ]));
    
            if ($aktivitas->isDirty()) {
                $aktivitas->save();
                \Log::info('Aktivitas berhasil diperbarui', $aktivitas->toArray());
            } else {
                \Log::info('Tidak ada perubahan pada aktivitas', $aktivitas->toArray());
            }
    
            return response()->json([
                'message' => 'Aktivitas berhasil diperbarui!',
                'data' => $aktivitas, 
            ], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error saat memperbarui aktivitas', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan saat memperbarui aktivitas.'], 500);
        }
    }
    
    public function tambahDokumentasi(Request $request, $id)
    {
        \Log::info('Memulai proses menambah dokumentasi untuk aktivitas', ['aktivitas_id' => $id]);
    
        try {
            $aktivitas = Aktivitas::find($id);
            if (!$aktivitas) {
                \Log::warning('Aktivitas tidak ditemukan', ['aktivitas_id' => $id]);
                return response()->json([
                    'message' => 'Aktivitas tidak ditemukan!',
                ], 404);
            }
    
            if (!$request->hasFile('dokumentasi') || !is_array($request->file('dokumentasi'))) {
                \Log::warning('Tidak ada file dokumentasi yang diupload', ['aktivitas_id' => $id]);
                return response()->json([
                    'message' => 'Tidak ada file dokumentasi yang diupload.',
                ], 400);
            }
    
            $validatedData = $request->validate([
                'dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', 
            ]);
    
            $filePaths = [];
            foreach ($request->file('dokumentasi') as $file) {
                $filePath = $file->store('dokumentasi', 'public');
                $dokumentasi = new Dokumentasi();
                $dokumentasi->aktivitas_id = $aktivitas->id;  
                $dokumentasi->file_path = $filePath; 
                $dokumentasi->save();
                $filePaths[] = $filePath; 
            }
    
            \Log::info('Dokumentasi berhasil ditambahkan', ['files' => $filePaths]);
    
            return response()->json([
                'message' => 'Dokumentasi berhasil ditambahkan!',
                'data' => $filePaths, 
            ], 200);
    
        } catch (\Exception $e) {
            \Log::error('Error saat menambah dokumentasi', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat menambah dokumentasi.',
            ], 500);
        }
    }
    
    
/**
     * Hapus dokumentasi berdasarkan aktivitasId dan documentId.
     *
     * @param  int  $aktivitasId
     * @param  int  $documentId
     * @return \Illuminate\Http\Response
     */
    public function hapusDokumentasi($aktivitasId, $documentId)
    {
        Log::info('Memulai proses menghapus dokumentasi', ['aktivitas_id' => $aktivitasId, 'document_id' => $documentId]);

        try {
            $aktivitas = Aktivitas::find($aktivitasId);
            if (!$aktivitas) {
                Log::warning('Aktivitas tidak ditemukan', ['aktivitas_id' => $aktivitasId]);
                return response()->json([
                    'message' => 'Aktivitas tidak ditemukan!',
                ], 404);
            }

            $dokumentasi = Dokumentasi::where('aktivitas_id', $aktivitasId)->find($documentId);
            if (!$dokumentasi) {
                Log::warning('Dokumentasi tidak ditemukan', ['document_id' => $documentId]);
                return response()->json([
                    'message' => 'Dokumentasi tidak ditemukan!',
                ], 404);
            }

            // Hapus file dari storage
            Storage::delete($dokumentasi->file_path);

            // Hapus record dokumentasi dari database
            $dokumentasi->delete();

            Log::info('Dokumentasi berhasil dihapus', ['document_id' => $documentId]);

            return response()->json([
                'message' => 'Dokumentasi berhasil dihapus!',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error saat menghapus dokumentasi', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Terjadi kesalahan saat menghapus dokumentasi.'], 500);
        }
    }
    
    public function index(Request $request)
{
    $user = JWTAuth::user();

    if (in_array($user->role, ['admin', 'manager', 'unit head'])) {
        $aktivitas = Aktivitas::with('nasabah')->get();
    } else {
        $aktivitas = Aktivitas::with('nasabah')->where('created_by', $user->id)->get();
    }

    $aktivitasData = $aktivitas->map(function ($item) {
        return [
            'id' => $item->id,
            'nasabah_id' => $item->nasabah_id,
            'nama_nasabah' => $item->nasabah->nama ?? 'Tidak Ditemukan', 
            'aktivitas' => $item->aktivitas,
            'tipe_nasabah' => $item->tipe_nasabah,
            'prospek' => $item->prospek,
            'nominal_prospek' => $item->nominal_prospek,
            'closing' => $item->closing,
            'status_aktivitas' => $item->status_aktivitas,
            'aktivitas_sales' => $item->aktivitas_sales,
            'keterangan_aktivitas' => $item->keterangan_aktivitas,
            'dokumentasi' => $item->dokumentasi,
            'created_by' => $item->created_by,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    });
    
    return response()->json([
        'message' => 'Data aktivitas berhasil ditampilkan!',
        'data' => $aktivitasData
    ], 200);
}
    
public function getAktivitasDitunda(Request $request)
{
    try {
        $user = $request->user();  // Ambil data pengguna dari token yang dikirim

        if (!$user) {
            return response()->json([
                'message' => 'User tidak terautentikasi!',
            ], 401);
        }

        // Cek role pengguna
        if ($user->jabatan == 'staff') {
            // Jika pengguna adalah staff, hanya ambil aktivitas yang diinput oleh staff tersebut
            $aktivitasDitunda = Aktivitas::where('status_aktivitas', 'ditunda')
                ->where('created_by', $user->id)  // Filter berdasarkan created_by yang sesuai dengan id staff yang login
                ->with('nasabah', 'user')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nasabah_id' => $item->nasabah_id,
                        'nama_nasabah' => $item->nasabah->nama ?? null,
                        'nama_user' => $item->user->nama ?? null,
                        'aktivitas' => $item->aktivitas,
                        'tipe_nasabah' => $item->tipe_nasabah,
                        'prospek' => $item->prospek,
                        'nominal_prospek' => $item->nominal_prospek,
                        'closing' => $item->closing,
                        'status_aktivitas' => $item->status_aktivitas,
                        'aktivitas_sales' => $item->aktivitas_sales,
                        'keterangan_aktivitas' => $item->keterangan_aktivitas,
                        'dokumentasi' => $item->dokumentasi,
                        'created_by' => $item->created_by,
                        'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                        'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'),
                    ];
                });
        } else {
            // Jika pengguna adalah unit head, admin, atau manajer, tampilkan semua aktivitas ditunda
            $aktivitasDitunda = Aktivitas::where('status_aktivitas', 'ditunda')
                ->with('nasabah', 'user')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'nasabah_id' => $item->nasabah_id,
                        'nama_nasabah' => $item->nasabah->nama ?? null,
                        'nama_user' => $item->user->nama ?? null,
                        'aktivitas' => $item->aktivitas,
                        'tipe_nasabah' => $item->tipe_nasabah,
                        'prospek' => $item->prospek,
                        'nominal_prospek' => $item->nominal_prospek,
                        'closing' => $item->closing,
                        'status_aktivitas' => $item->status_aktivitas,
                        'aktivitas_sales' => $item->aktivitas_sales,
                        'keterangan_aktivitas' => $item->keterangan_aktivitas,
                        'dokumentasi' => $item->dokumentasi,
                        'created_by' => $item->created_by,
                        'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                        'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'),
                    ];
                });
        }

        // Mengembalikan response dengan data aktivitas ditunda
        return response()->json([
            'message' => 'Data aktivitas berhasil ditampilkan!',
            'data' => $aktivitasDitunda
        ]);

    } catch (\Exception $e) {
        // Jika terjadi kesalahan, mengembalikan response error
        return response()->json([
            'message' => 'Gagal mengambil data aktivitas!',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function getAktivitasDitundaStaff()
{
    try {
        // Mengambil ID user yang sedang login
        $userId = auth()->user()->id;

        // Ambil aktivitas yang statusnya 'ditunda' dan milik user yang sedang login
        $aktivitasDitunda = Aktivitas::where('status_aktivitas', 'ditunda')
            ->where('user_id', $userId)  // Memastikan aktivitas terkait dengan user yang login
            ->with('nasabah', 'user')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nasabah_id' => $item->nasabah_id,
                    'nama_nasabah' => $item->nasabah->nama ?? null,
                    'nama_user' => $item->user->nama ?? null,
                    'aktivitas' => $item->aktivitas,
                    'tipe_nasabah' => $item->tipe_nasabah,
                    'prospek' => $item->prospek,
                    'nominal_prospek' => $item->nominal_prospek,
                    'closing' => $item->closing,
                    'status_aktivitas' => $item->status_aktivitas,
                    'aktivitas_sales' => $item->aktivitas_sales,
                    'keterangan_aktivitas' => $item->keterangan_aktivitas,
                    'dokumentasi' => $item->dokumentasi,
                    'created_by' => $item->created_by,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                    'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'),
                ];
            });

        return response()->json([
            'message' => 'Data aktivitas berhasil ditampilkan!',
            'data' => $aktivitasDitunda
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil data aktivitas!',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function getAktivitasSelesai()
    {
        $aktivitasSelesai = Aktivitas::where('status_aktivitas', 'selesai')
            ->with('nasabah', 'user')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nasabah_id' => $item->nasabah_id,
                    'nama_nasabah' => $item->nasabah->nama ?? null,
                    'nama_user' => $item->user->nama ?? null,
                    'aktivitas' => $item->aktivitas,
                    'tipe_nasabah' => $item->tipe_nasabah,
                    'prospek' => $item->prospek,
                    'nominal_prospek' => $item->nominal_prospek,
                    'closing' => $item->closing,
                    'status_aktivitas' => $item->status_aktivitas,
                    'aktivitas_sales' => $item->aktivitas_sales,
                    'keterangan_aktivitas' => $item->keterangan_aktivitas,
                    'dokumentasi' => $item->dokumentasi,
                    'created_by' => $item->created_by,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                    'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'), 
                ];
            });

        return response()->json([
            'message' => 'Data aktivitas berhasil ditampilkan!',
            'data' => $aktivitasSelesai
        ]);
    }

    public function getAktivitasHarian()
    {
        $today = Carbon::today();

        $aktivitasHarian = Aktivitas::whereDate('created_at', $today) 
            ->with('nasabah', 'user')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nasabah_id' => $item->nasabah_id,
                    'nama_nasabah' => $item->nasabah->nama ?? null,
                    'nama_user' => $item->user->nama ?? null, 
                    'aktivitas' => $item->aktivitas,
                    'tipe_nasabah' => $item->tipe_nasabah,
                    'prospek' => $item->prospek,
                    'nominal_prospek' => $item->nominal_prospek,
                    'closing' => $item->closing,
                    'status_aktivitas' => $item->status_aktivitas,
                    'aktivitas_sales' => $item->aktivitas_sales,
                    'keterangan_aktivitas' => $item->keterangan_aktivitas,
                    'dokumentasi' => $item->dokumentasi,
                    'created_by' => $item->created_by,
                    'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                    'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'), 
                ];
            });

        return response()->json([
            'message' => 'Aktivitas harian berhasil ditampilkan!',
            'data' => $aktivitasHarian
        ]);
    }

    public function getAktivitasBulananDetail(Request $request)
    {
        // Validasi input bulan dan tahun
        $request->validate([
            'bulan' => 'required|string|in:Januari,Februari,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember', // Bulan dalam nama
            'tahun' => 'required|integer|min:1900|max:2099', // Tahun valid antara 1900 dan 2099
        ]);
    
        $bulan = $request->bulan;
        $tahun = $request->tahun;
    
        // Mengonversi nama bulan menjadi angka
        $bulanMapping = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12,
        ];
    
        $bulanAngka = $bulanMapping[$bulan]; // Mendapatkan angka bulan berdasarkan nama
    
        // Ambil data aktivitas berdasarkan bulan dan tahun yang dipilih
        $aktivitas = Aktivitas::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanAngka)
            ->with('nasabah', 'user', 'dokumentasi') // Termasuk relasi nasabah dan user
            ->get();
    
        // Jika tidak ada aktivitas pada bulan dan tahun tersebut
        if ($aktivitas->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada aktivitas untuk bulan ' . $bulan . ' tahun ' . $tahun,
                'data' => []
            ], 200);
        }
    
        // Format data aktivitas agar sesuai dengan frontend yang diinginkan
        $aktivitasData = $aktivitas->map(function ($item) {
            return [
                'id' => $item->id,
                'nasabah_id' => $item->nasabah_id,
                'aktivitas' => $item->aktivitas,
                'nama_nasabah' => $item->nasabah->nama ?? null,
                'nama_user' => $item->user->nama ?? null,
                'tipe_nasabah' => $item->tipe_nasabah,
                'prospek' => $item->prospek,
                'nominal_prospek' => $item->nominal_prospek,
                'closing' => $item->closing,
                'status_aktivitas' => $item->status_aktivitas,
                'aktivitas_sales' => $item->aktivitas_sales,
                'keterangan_aktivitas' => $item->keterangan_aktivitas,
                'dokumentasi' => $item->dokumentasi->map(function($doc) {
                    return [
                        'file_path' => asset('storage/' . $doc->file_path) // Convert file path to full URL
                    ];
                }), // Menyertakan dokumentasi yang relevan
                'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'),
            ];
        });
    
        return response()->json([
            'message' => 'Aktivitas untuk bulan ' . $bulan . ' tahun ' . $tahun . ' berhasil ditampilkan.',
            'data' => $aktivitasData
        ]);
    }
    

    public function getTotalAktivitasBulanan()
    {
        try {
            $currentYear = Carbon::now()->year;
            $monthlyAktivitas = [];
    
            // Daftar bulan
            $months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
    
            // Inisialisasi data untuk bulan
            foreach ($months as $month) {
                $monthlyAktivitas[$month] = 0;
            }
    
            // Ambil jumlah aktivitas berdasarkan bulan dari database
            $aktivitas = DB::table('aktivitas')
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->whereYear('created_at', $currentYear)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get();
    
            // Menyusun hasil berdasarkan bulan
            foreach ($aktivitas as $act) {
                $monthName = Carbon::createFromFormat('m', $act->month)->format('F');
                $monthlyAktivitas[$monthName] = $act->total;
            }
    
            // Kembalikan hasil dalam format JSON
            return response()->json([
                'message' => 'Total aktivitas bulanan berhasil dihitung.',
                'data' => $monthlyAktivitas
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error in getTotalAktivitasBulanan: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal mengambil data aktivitas bulanan.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    


public function resetAktivitasTahunan()
{
    $currentYear = Carbon::now()->year;

    AktivitasBulanan::where('tahun', $currentYear)->delete();

    return response()->json([
        'message' => 'Data aktivitas tahunan berhasil direset!',
    ], 200);
}

public function getAktivitasBulanan(Request $request)
{
    // Validasi tahun yang diterima dari request
    $request->validate([
        'tahun' => 'required|integer|min:1900|max:2099',
    ]);

    $tahun = $request->tahun;

    // Array untuk memetakan angka bulan ke nama bulan
    $namaBulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // Ambil data aktivitas berdasarkan tahun yang dipilih, dikelompokkan per bulan
    $aktivitasPerBulan = Aktivitas::whereYear('created_at', $tahun)
        ->selectRaw('MONTH(created_at) as bulan, count(*) as jumlah_aktivitas')
        ->groupBy('bulan')
        ->orderBy('bulan', 'asc')
        ->get();

    // Jika tidak ada aktivitas pada tahun tersebut
    if ($aktivitasPerBulan->isEmpty()) {
        return response()->json([
            'message' => 'Tidak ada aktivitas untuk tahun ' . $tahun,
            'data' => []
        ], 200);
    }

    // Menyiapkan data aktivitas berdasarkan bulan (nama bulan)
    $aktivitasPerBulanData = [];
    for ($bulan = 1; $bulan <= 12; $bulan++) {
        $aktivitasPerBulanData[$namaBulan[$bulan]] = 0;
    }

    // Mengisi data aktivitas per bulan menggunakan nama bulan
    foreach ($aktivitasPerBulan as $aktivitas) {
        $aktivitasPerBulanData[$namaBulan[$aktivitas->bulan]] = $aktivitas->jumlah_aktivitas;
    }

    // Mendapatkan tahun-tahun yang memiliki aktivitas untuk dropdown
    $tahunTersedia = Aktivitas::selectRaw('YEAR(created_at) as tahun')
        ->groupBy('tahun')
        ->orderBy('tahun', 'desc')
        ->get()
        ->pluck('tahun'); // Ambil hanya tahun saja

    return response()->json([
        'message' => 'Aktivitas untuk tahun ' . $tahun . ' berhasil ditampilkan.',
        'data' => [
            'aktivitas_per_bulan' => $aktivitasPerBulanData,
            'tahun_tersedia' => $tahunTersedia,
        ]
    ]);
}





public function getTotalAktivitasBulanIni()
{
    $currentMonth = Carbon::now()->month;
    $currentYear = Carbon::now()->year;

    $totalAktivitasBulanIni = Aktivitas::whereMonth('created_at', $currentMonth)
        ->whereYear('created_at', $currentYear)
        ->count();

    return response()->json([
        'message' => 'Total aktivitas bulan ini berhasil dihitung.',
        'data' => [
            'total_aktivitas_bulan_ini' => $totalAktivitasBulanIni
        ]
    ]);
}

public function detailAktivitas($id)
{
    $aktivitas = Aktivitas::with('nasabah', 'user')->find($id);

    if (!$aktivitas) {
        return response()->json([
            'message' => 'Aktivitas tidak ditemukan!'
        ], 404);
    }

    $aktivitasData = [
        'id' => $aktivitas->id,
        'nasabah_id' => $aktivitas->nasabah_id,
        'nama_nasabah' => $aktivitas->nasabah->nama ?? 'Tidak Ditemukan',
        'alamat' => $aktivitas->nasabah->alamat ?? 'Tidak Ditemukan',  // Menambahkan alamat nasabah
        'nomor_hp_nasabah' => $aktivitas->nasabah->nomor_telepon ?? 'Tidak Ditemukan',  // Menambahkan nomor hp nasabah
        'nama_user' => $aktivitas->user->nama ?? 'Tidak Ditemukan',
        'aktivitas' => $aktivitas->aktivitas,
        'tipe_nasabah' => $aktivitas->tipe_nasabah,
        'prospek' => $aktivitas->prospek,
        'nominal_prospek' => $aktivitas->nominal_prospek,
        'closing' => $aktivitas->closing,
        'status_aktivitas' => $aktivitas->status_aktivitas,
        'aktivitas_sales' => $aktivitas->aktivitas_sales,
        'keterangan_aktivitas' => $aktivitas->keterangan_aktivitas,
        'dokumentasi' => $aktivitas->dokumentasi,
        'created_by' => $aktivitas->created_by,
        'created_at' => $aktivitas->created_at->format('Y-m-d'),
        'updated_at' => $aktivitas->updated_at->format('Y-m-d'),
    ];

    return response()->json([
        'message' => 'Detail aktivitas berhasil ditampilkan.',
        'data' => $aktivitasData
    ], 200);
}


public function getTotalMingguan(Request $request)
{
    try {
        $currentYear = now()->year;
        $data = [];

        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];

        // Inisialisasi data bulanan untuk setiap bulan dan minggu
        foreach ($months as $englishMonth => $indonesianMonth) {
            $data[$indonesianMonth] = [];
            for ($week = 1; $week <= 5; $week++) {
                $data[$indonesianMonth]["minggu $week"] = ['jumlah' => 0];
            }
        }

        // Ambil data aktivitas berdasarkan tahun ini dan minggu dalam bulan
        $aktivitas = DB::table('aktivitas')
            ->selectRaw('MONTH(created_at) as month, WEEK(created_at, 1) as week_of_month, COUNT(*) as total')
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at), WEEK(created_at, 1)'))
            ->get();

        // Menyusun data berdasarkan bulan dan minggu
        foreach ($aktivitas as $act) {
            $monthNameIndonesian = $months[Carbon::createFromFormat('m', $act->month)->format('F')];
            $weekKey = "minggu {$act->week_of_month}";

            // Cek apakah minggu ini valid di bulan tersebut
            if (isset($data[$monthNameIndonesian][$weekKey])) {
                $data[$monthNameIndonesian][$weekKey]['jumlah'] = $act->total;
            }
        }

        // Kembalikan hasil dalam format JSON
        return response()->json(['data' => $data], 200);
    } catch (\Exception $e) {
        \Log::error('Error in getTotalMingguan: ' . $e->getMessage());
        return response()->json([
            'error' => 'Gagal mengambil data aktivitas mingguan.',
            'details' => $e->getMessage()
        ], 500);
    }
}




public function getAktivitasMingguan(Request $request)
{
    try {
        // Get bulan, minggu, and tahun from query params
        $bulan = $request->query('bulan');
        $minggu = $request->query('minggu');
        $tahun = $request->query('tahun');

        // Validate bulan, minggu, and tahun
        if (!in_array($bulan, range(1, 12))) {
            return response()->json(['error' => 'Bulan tidak valid.'], 400);
        }

        if (!in_array($minggu, range(1, 5))) {
            return response()->json(['error' => 'Minggu tidak valid.'], 400);
        }

        if (!is_numeric($tahun) || strlen($tahun) != 4) {
            return response()->json(['error' => 'Tahun tidak valid.'], 400);
        }

        // Calculate the start date of the week
        $startOfWeek = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->addWeeks($minggu - 1);
        $endOfWeek = $startOfWeek->copy()->endOfWeek();

        // Fetch activities for the given week and month
        $aktivitas = Aktivitas::whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->get();

        // Format the activities data
        $aktivitasMingguanFormatted = $aktivitas->map(function($aktivitas) {
            return [
                'id' => $aktivitas->id,
                'nasabah_id' => $aktivitas->nasabah_id,
                'nama_nasabah' => $aktivitas->nasabah->nama ?? 'Tidak Ditemukan',
                'nama_user' => $aktivitas->user->nama ?? 'Tidak Ditemukan',
                'aktivitas' => $aktivitas->aktivitas,
                'tipe_nasabah' => $aktivitas->tipe_nasabah,
                'prospek' => $aktivitas->prospek,
                'nominal_prospek' => $aktivitas->nominal_prospek,
                'closing' => $aktivitas->closing,
                'status_aktivitas' => $aktivitas->status_aktivitas,
                'aktivitas_sales' => $aktivitas->aktivitas_sales,
                'keterangan_aktivitas' => $aktivitas->keterangan_aktivitas,
                'dokumentasi' => $aktivitas->dokumentasi,
                'created_by' => $aktivitas->created_by,
                'created_at' => $aktivitas->created_at->format('Y-m-d'),
                'updated_at' => $aktivitas->updated_at->format('Y-m-d'),
            ];
        });

        // Return the response with formatted activities
        return response()->json(['data' => $aktivitasMingguanFormatted], 200);

    } catch (\Exception $e) {
        // Return error if something goes wrong
        return response()->json(['error' => 'Terjadi kesalahan dalam mengambil data aktivitas.'], 500);
    }
}



    public function getAktivitasNip($nip)
    {
        $user = User::where('nip', $nip)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Pengguna dengan NIP ' . $nip . ' tidak ditemukan!'
            ], 404);
        }

        $aktivitas = Aktivitas::with('nasabah')
            ->where('created_by', $user->id)
            ->get();

        $aktivitasData = $aktivitas->map(function ($item) {
            return [
                'id' => $item->id,
                'nasabah_id' => $item->nasabah_id,
                'nama_nasabah' => $item->nasabah->nama ?? 'Tidak Ditemukan',
                'aktivitas' => $item->aktivitas,
                'tipe_nasabah' => $item->tipe_nasabah,
                'prospek' => $item->prospek,
                'nominal_prospek' => $item->nominal_prospek,
                'closing' => $item->closing,
                'status_aktivitas' => $item->status_aktivitas,
                'aktivitas_sales' => $item->aktivitas_sales,
                'keterangan_aktivitas' => $item->keterangan_aktivitas,
                'dokumentasi' => $item->dokumentasi,
                'created_by' => $item->created_by,
                'created_at' => Carbon::parse($item->created_at)->format('Y-m-d'),
                'updated_at' => Carbon::parse($item->updated_at)->format('Y-m-d'), 
            ];
        });

        return response()->json([
            'message' => 'Data aktivitas berhasil ditampilkan!',
            'data' => $aktivitasData
        ], 200);
    }
    
    public function getRecentData()
    {
        $aktivitas = Aktivitas::with(['user', 'nasabah'])
            ->orderBy('created_at', 'DESC')
            ->take(100)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_user' => $item->user ? $item->user->nama : 'Tidak Diketahui',
                    'tanggal_aktivitas' => $item->created_at->format('Y-m-d'),
                    'nama_nasabah' => $item->nasabah ? $item->nasabah->nama : 'Tidak Diketahui',
                    'id_nasabah' => $item->nasabah->id,
                    'nama_aktivitas' => $item->aktivitas,
                    'prospek' => $item->prospek,
                    'aktivitas_sales' => $item->aktivitas_sales
                ];
            });


        return response()->json([
            'status' => 'success',
            'data' => $aktivitas
        ]);
    }

    public function totalAktivitas(Request $request)
{
    \Log::info('Memulai proses menampilkan aktivitas yang paling banyak dilakukan');

    try {
        // Ambil data aktivitas yang paling banyak dilakukan
        $aktivitas = Aktivitas::select('aktivitas', \DB::raw('count(*) as total_aktivitas'))
            ->groupBy('aktivitas')
            ->orderByDesc('total_aktivitas')
            ->get();

        if ($aktivitas->isEmpty()) {
            \Log::info('Tidak ada aktivitas yang tercatat');
            return response()->json([
                'message' => 'Tidak ada aktivitas yang tercatat.',
            ], 404);
        }

        \Log::info('Aktivitas paling banyak ditemukan', ['aktivitas_count' => $aktivitas->count()]);

        return response()->json([
            'message' => 'Data aktivitas yang paling banyak dilakukan berhasil ditemukan.',
            'data' => $aktivitas,
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Error saat menampilkan aktivitas yang paling banyak dilakukan', ['error' => $e->getMessage()]);
        return response()->json([
            'message' => 'Terjadi kesalahan saat menampilkan aktivitas.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

}

