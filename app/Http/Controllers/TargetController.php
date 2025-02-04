<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\TargetTahunan;
use App\Models\NilaiKpi;
use App\Models\TargetMingguan;
use App\Models\TargetHarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;


class TargetController extends Controller
{
    public function addTargetTahunan(Request $request, $user_id)
    {
        try {
            $user = User::find($user_id);
    
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan!'], 404);
            }
    
            $currentYear = date('Y');
    
            // Periksa apakah target untuk tahun ini sudah ada
            $existingTarget = TargetTahunan::where('user_id', $user->id)
                ->where('tahun', $currentYear)
                ->first();
    
            if ($existingTarget) {
                return response()->json(['message' => 'Target untuk tahun ini sudah ada!'], 409);
            }
    
            // Validasi input
            $validated = $request->validate([
                'target_kpi' => 'required|array',
                'target_kpi.*.nama_kpi' => 'required|string',
                'target_kpi.*.bobot_penilaian' => 'required|numeric|min:0|max:100',
                'target_kpi.*.indikator' => 'required|string',
                'target_kpi.*.target' => 'required|array|min:12|max:12', // Validasi untuk 12 nilai target
                'target_kpi.*.target.*' => 'required|numeric|min:0', // Validasi untuk setiap nilai dalam array
            ]);
    
            $totalTarget = 0;
            $targetKpiData = [];
            $realisasiData = [];
            $pencapaianData = [];
            $nilaiKpiData = [];
    
            // Loop untuk memproses target KPI
            foreach ($validated['target_kpi'] as $kpi) {
                $targetArray = $kpi['target'];
    
                $realisasiArray = array_fill(0, 12, 0);
                $pencapaianArray = array_fill(0, 12, 0);
                $nilaiKpiArray = array_fill(0, 12, 0);
    
                $targetKpiData[] = [
                    'nama_kpi' => $kpi['nama_kpi'],
                    'bobot_penilaian' => $kpi['bobot_penilaian'],
                    'indikator' => $kpi['indikator'],
                    'target' => json_encode($targetArray),
                ];
    
                $realisasiData[] = json_encode($realisasiArray);
                $pencapaianData[] = json_encode($pencapaianArray);
                $nilaiKpiData[] = json_encode($nilaiKpiArray);
    
                $totalTarget += array_sum($targetArray);
            }
    
            // Simpan data ke dalam tabel `TargetTahunan` untuk staff
            $targetData = [
                'user_id' => $user->id,
                'tahun' => $currentYear,
                'target_kpi' => json_encode($targetKpiData),
                'total_target' => $totalTarget,
            ];
    
            $targetTahunan = TargetTahunan::create($targetData);
    
            // Simpan data KPI ke tabel `NilaiKpi`
            foreach ($targetKpiData as $index => $kpi) {
                NilaiKpi::create([
                    'target_tahunan_id' => $targetTahunan->id,
                    'nama_kpi' => $kpi['nama_kpi'],
                    'realisasi' => $realisasiData[$index],
                    'pencapaian' => $pencapaianData[$index],
                    'nilai_kpi' => $nilaiKpiData[$index],
                ]);
            }
    
            // ** Tambahkan target untuk unit head **
            $unitHead = User::where('jabatan', 'unit head')->first(); // Ambil unit head secara global
    
            if ($unitHead) {
                $unitHeadTarget = TargetTahunan::firstOrCreate(
                    ['user_id' => $unitHead->id, 'tahun' => $currentYear],
                    ['target_kpi' => '[]', 'total_target' => 0]
                );
    
                // Tambahkan target staff ke target unit head
                $existingTargets = json_decode($unitHeadTarget->target_kpi, true);
                $updatedTargets = array_merge($existingTargets, $targetKpiData);
                $unitHeadTarget->update([
                    'target_kpi' => json_encode($updatedTargets),
                    'total_target' => $unitHeadTarget->total_target + $totalTarget,
                ]);
            }
    
            return response()->json([
                'message' => 'Target tahunan berhasil ditambahkan!',
                'data' => $targetTahunan->fresh(),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menambahkan target tahunan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function updateTargetTahunan(Request $request, $user_id, $kpi_name)
    {
        try {
            // Cari pengguna berdasarkan user_id
            $user = User::find($user_id);
    
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan!'], 404);
            }
    
            // Ambil tahun saat ini
            $currentYear = date('Y');
    
            // Periksa apakah target tahunan untuk tahun ini ada
            $existingTarget = TargetTahunan::where('user_id', $user->id)
                ->where('tahun', $currentYear)
                ->first();
    
            if (!$existingTarget) {
                return response()->json(['message' => 'Target untuk tahun ini tidak ditemukan!'], 404);
            }
    
            // Ambil data target_kpi yang sudah ada
            $existingTargetData = json_decode($existingTarget->target_kpi, true);
    
            // Cari KPI berdasarkan nama_kpi
            $kpi = null;
            $kpiIndex = null;
            foreach ($existingTargetData as $index => $kpiItem) {
                if (isset($kpiItem['nama_kpi']) && $kpiItem['nama_kpi'] === $kpi_name) {
                    $kpi = $kpiItem;
                    $kpiIndex = $index; // Menyimpan indeks KPI yang ditemukan
                    break;
                }
            }
    
            if (!$kpi) {
                return response()->json(['message' => 'KPI dengan nama tersebut tidak ditemukan!'], 404);
            }
    
            // Validasi input dari request untuk bulan yang akan diperbarui
            $validated = $request->validate([
                'month_index' => 'required|integer|min:0|max:11', // Validasi bulan (0-11)
                'target' => 'required|numeric|min:0', // Validasi nilai target
            ]);
    
            // Periksa apakah bulan yang diminta valid
            $month_index = $validated['month_index'];
            $new_target = $validated['target'];
    
            // Update nilai target untuk bulan yang diminta
            $targetArray = json_decode($kpi['target']); // Ambil array target
            $targetArray[$month_index] = $new_target; // Perbarui target bulan yang sesuai
    
            // Update kembali nilai target pada KPI
            $existingTargetData[$kpiIndex]['target'] = json_encode($targetArray);
    
            // Hitung kembali total target setelah pembaruan
            $totalTarget = 0;
            foreach ($existingTargetData as $kpi) {
                $totalTarget += array_sum(json_decode($kpi['target']));
            }
    
            // Update data target tahunan dengan data yang sudah diperbarui
            $targetData = [
                'target_kpi' => json_encode($existingTargetData), // Simpan data KPI yang diperbarui
                'total_target' => $totalTarget, // Hitung total target tahunan
            ];
    
            // Update data target tahunan yang ada
            $existingTarget->update($targetData);
    
            // Mengembalikan response sukses
            return response()->json([
                'message' => 'Target bulan berhasil diperbarui!',
                'data' => $existingTarget->fresh(),
            ], 200);
    
        } catch (\Exception $e) {
            // Tangani error jika ada masalah
            return response()->json([
                'message' => 'Gagal memperbarui target bulan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    
  /**
     * Mengambil data target tahunan untuk user yang sedang login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTargetTahunan(Request $request)
    {
        try {
            $user = Auth::user();
    
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak terautentikasi!',
                ], 401);
            }
    
            $targetTahunanQuery = TargetTahunan::with(['nilaiKpi' => function ($query) {
                $query->select('id', 'target_tahunan_id', 'realisasi', 'pencapaian', 'nilai_kpi', 'nama_kpi');
            }]);
    
            // Jika jabatan adalah staff atau unit head, hanya ambil data untuk user_id yang sesuai
            if (in_array($user->jabatan, ['staff'])) {
                $targetTahunanQuery->where('user_id', $user->id);
            } elseif (in_array($user->jabatan, ['unit head', 'admin', 'manajer'])) {
                // Untuk unit head, admin, dan manajer, ambil semua data tanpa filter user_id
            }
    
            $targetTahunan = $targetTahunanQuery->get();
    
            if ($targetTahunan->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data target tahunan untuk user ini',
                ], 404);
            }
    
            // Proses data target
            $targetTahunanData = $targetTahunan->map(function ($target) {
                // Decode data target KPI
                $targetKpiData = collect(json_decode($target->target_kpi, true))->map(function ($kpi) use ($target) {
                    // Decode nilai target jika masih berupa string
                    $monthlyTargets = is_string($kpi['target']) ? json_decode($kpi['target'], true) : $kpi['target'];
    
                    if (!$monthlyTargets || !is_array($monthlyTargets)) {
                        Log::error('Failed to decode monthly targets', ['kpi' => $kpi]);
                        $monthlyTargets = [];
                    }
    
                    // Find nilaiKpi yang sesuai dengan KPI
                    $nilaiKpi = $target->nilaiKpi->firstWhere('nama_kpi', $kpi['nama_kpi']);
    
                    // Decode realisasi, pencapaian, dan nilai_kpi per bulan
                    $monthlyRealisasi = $nilaiKpi ? json_decode($nilaiKpi->realisasi, true) : [];
                    $monthlyPencapaian = $nilaiKpi ? json_decode($nilaiKpi->pencapaian, true) : [];
                    $monthlyNilaiKpi = $nilaiKpi ? json_decode($nilaiKpi->nilai_kpi, true) : [];
    
                    // Format data menjadi per bulan

return [
    'nama_kpi' => $kpi['nama_kpi'],
    'bobot_penilaian' => $kpi['bobot_penilaian'],
    'indikator' => $kpi['indikator'],
    'target' => $this->mapMonthlyData($this->convertArrayToMonthly($monthlyTargets)),
    'realisasi' => $this->mapMonthlyData($this->convertArrayToMonthly($monthlyRealisasi)),
    'pencapaian' => $this->mapMonthlyData($this->convertArrayToMonthly($monthlyPencapaian)),
    'nilai_kpi' => $this->mapMonthlyDataWithLimit($this->convertArrayToMonthly($monthlyNilaiKpi)),
];

                });
    
                return [
                    'id' => $target->id,
                    'user_id' => $target->user_id,
                    'tahun' => $target->tahun,
                    'target_kpi' => $targetKpiData,
                    'total_target' => $target->total_target,
                ];
            });
    
            return response()->json([
                'data' => $targetTahunanData,
                'message' => 'Data target tahunan berhasil diambil',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data target tahunan!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Membantu memetakan data bulanan.
     *
     * @param  array  $monthlyData
     * @return array
     */
    private function mapMonthlyData($monthlyData)
    {
        $months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
            'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
    
        // Inisialisasi data bulan dengan nilai 0
        $mappedData = array_fill_keys($months, 0);
    
        // Pastikan $monthlyData adalah array
        if (is_array($monthlyData)) {
            foreach ($months as $index => $month) {
                if (isset($monthlyData[$month])) {
                    $value = $monthlyData[$month];
                    $mappedData[$month] = is_numeric($value) ? (float) $value : 0;
                }
            }
        }
    
        return $mappedData;
    }
    
    /**
     * Konversi array angka ke format bulanan.
     *
     * @param  array  $data
     * @return array
     */
    private function convertArrayToMonthly($data)
    {
        $months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        // Inisialisasi data bulan dengan nilai 0
        $mappedData = array_fill_keys($months, 0);

        foreach ($months as $index => $month) {
            if (isset($data[$index])) {
                $mappedData[$month] = $data[$index];
            }
        }

        return $mappedData;
    }

    /**
 * Membantu memetakan data bulanan dengan pembatasan 130% untuk nilai KPI.
 *
 * @param  array  $monthlyData
 * @return array
 */
private function mapMonthlyDataWithLimit($monthlyData)
{
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
        'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // Inisialisasi data bulan dengan nilai 0
    $mappedData = array_fill_keys($months, 0);

    // Pastikan $monthlyData adalah array
    if (is_array($monthlyData)) {
        foreach ($months as $index => $month) {
            if (isset($monthlyData[$month])) {
                $value = $monthlyData[$month];
                // Pembatasan nilai KPI agar tidak lebih dari 130%
                $mappedData[$month] = is_numeric($value) ? min((float) $value, 130) : 0;
            }
        }
    }

    return $mappedData;
}


        /**
     * Get Nama KPI berdasarkan user_id.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKpiByUser()
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Ambil data target tahunan berdasarkan user_id dan tahun saat ini
        $tahun = date('Y');
        $targetTahunan = TargetTahunan::where('user_id', $user->id)
                                      ->where('tahun', $tahun)
                                      ->first();

        if (!$targetTahunan) {
            return response()->json([
                'message' => 'Target tahunan tidak ditemukan untuk user ini.'
            ], 404);
        }

        // Ambil nama KPI dari target_kpi
        $targetKpi = json_decode($targetTahunan->target_kpi, true);

        // Ambil nama KPI beserta ID dan format data sesuai kebutuhan
        $kpiData = collect($targetKpi)->map(function ($kpi, $index) {
            return [
                'id' => $index + 1, // Menambahkan ID berurutan berdasarkan index
                'nama_kpi' => $kpi['nama_kpi']
            ];
        });

        // Response format yang diinginkan
        return response()->json([
            'message' => 'Data nama kpi berhasil ditampilkan!',
            'data' => $kpiData
        ]);
    }

    public function getKpiByUserAdmin(Request $request, $userId)
    {
        // Ambil user yang sedang login
        $user = Auth::user();
    
        // Cek apakah user memiliki jabatan "admin"
        if (!$user || $user->jabatan !== 'admin') {
            // Jika bukan admin, kembalikan response unauthorized
            return response()->json([
                'message' => 'Unauthorized. Only admins can access this data.'
            ], 403);
        }
    
        // Ambil target tahunan untuk user tertentu
        $tahun = date('Y');
        $targetTahunan = TargetTahunan::where('user_id', $userId)
                                      ->where('tahun', $tahun)
                                      ->first();
    
        if (!$targetTahunan) {
            return response()->json([
                'message' => 'Target tahunan tidak ditemukan untuk user ini.'
            ], 404);
        }
    
        // Ambil nama KPI dari target_kpi
        $targetKpi = json_decode($targetTahunan->target_kpi, true);
    
        // Format data KPI sesuai kebutuhan
        $kpiData = collect($targetKpi)->map(function ($kpi, $index) {
            return [
                'id' => $index + 1, // Menambahkan ID berurutan
                'nama_kpi' => $kpi['nama_kpi'] // Nama KPI
            ];
        });
    
        // Kembalikan response dengan data KPI
        return response()->json([
            'message' => 'Data nama KPI berhasil ditampilkan!',
            'data' => $kpiData
        ]);
    }
    

    
    public function getTotalNilaiKpi($nip)
    {
        try {
            $user = User::where('nip', $nip)->first();
    
            if (!$user) {
                return response()->json([
                    'message' => 'User dengan NIP tersebut tidak ditemukan!',
                ], 404);
            }
    
            $currentYear = date('Y');
    
            $targetTahunan = TargetTahunan::where('user_id', $user->id)
                                          ->where('tahun', $currentYear)
                                          ->first();
    
            if (!$targetTahunan) {
                return response()->json([
                    'message' => 'Tidak ada data target tahunan untuk user ini di tahun ini!',
                ], 404);
            }
    
            $allKpis = NilaiKpi::where('target_tahunan_id', $targetTahunan->id)->get();
    
            if ($allKpis->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data KPI untuk user ini di tahun ini!',
                ], 404);
            }
    
            $totalKpiPerBulan = [];
            $jumlahBulanDenganData = 0;
            $totalKpiTahunan = 0;
    
            foreach ($allKpis as $kpi) {
                $nilaiKpiPerBulan = json_decode($kpi->nilai_kpi, true);
    
                foreach ($nilaiKpiPerBulan as $month => $nilai) {
                    $numericValue = floatval(str_replace('%', '', $nilai));
    
                    if (!isset($totalKpiPerBulan[$month])) {
                        $totalKpiPerBulan[$month] = 0;
                    }
    
                    $totalKpiPerBulan[$month] += $numericValue;
                }
            }
    
            // Batasi nilai KPI bulanan maksimum 130% dan hitung total tahunan
            foreach ($totalKpiPerBulan as $month => $total) {
                $totalKpiPerBulan[$month] = min($total, 130);
                $totalKpiTahunan += $totalKpiPerBulan[$month];
                $jumlahBulanDenganData++;
            }
    
            // Hitung rata-rata nilai KPI tahunan
            $rataRataKpiTahunan = $jumlahBulanDenganData > 0 
                ? round($totalKpiTahunan / $jumlahBulanDenganData, 2) 
                : 0;
    
            return response()->json([
                'message' => 'Berhasil mengambil total nilai KPI per bulan dan rata-rata KPI tahunan untuk user!',
                'data' => [
                    'total_nilai_kpi' => $totalKpiPerBulan,
                    'rata_rata_kpi' => min($rataRataKpiTahunan, 130), // Batasi rata-rata maksimum 130%
                ],
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil total nilai KPI!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    public function getRataRataNilaiKpiUserLogin()
{
    try {
        // Ambil user yang sedang login
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'User belum login!',
            ], 401);
        }

        $currentYear = date('Y');

        $targetTahunan = TargetTahunan::where('user_id', $user->id)
                                      ->where('tahun', $currentYear)
                                      ->first();

        if (!$targetTahunan) {
            return response()->json([
                'message' => 'Tidak ada data target tahunan untuk user ini di tahun ini!',
            ], 404);
        }

        $allKpis = NilaiKpi::where('target_tahunan_id', $targetTahunan->id)->get();

        if ($allKpis->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data KPI untuk user ini di tahun ini!',
            ], 404);
        }

        $totalKpiPerBulan = [];

        // Loop untuk menghitung total KPI per bulan
        foreach ($allKpis as $kpi) {
            $nilaiKpiPerBulan = json_decode($kpi->nilai_kpi, true);

            foreach ($nilaiKpiPerBulan as $month => $nilai) {
                $numericValue = floatval(str_replace('%', '', $nilai));
                if (!isset($totalKpiPerBulan[$month])) {
                    $totalKpiPerBulan[$month] = 0;
                }
                $totalKpiPerBulan[$month] += $numericValue;
            }
        }

        // Batasi total KPI per bulan jika lebih dari 130
        foreach ($totalKpiPerBulan as $month => $total) {
            if ($total > 130) {
                $totalKpiPerBulan[$month] = 130;
            }
        }

        // Hitung rata-rata KPI
        $totalNilai = array_sum($totalKpiPerBulan);
        $jumlahBulan = count($totalKpiPerBulan);
        $rataRata = $jumlahBulan > 0 ? round($totalNilai / $jumlahBulan, 2) : 0;

        // Batasi rata-rata KPI jika lebih dari 130
        if ($rataRata > 130) {
            $rataRata = 130;
        }

        // Format hasil untuk setiap bulan
        $totalKpiPerBulan = array_map(fn($value) => round($value, 2), $totalKpiPerBulan);

        return response()->json([
            'message' => 'Berhasil mengambil rata-rata nilai KPI untuk user login!',
            'data' => [
                'total_nilai_kpi' => $totalKpiPerBulan,
                'rata_rata_kpi' => $rataRata,
            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengambil rata-rata nilai KPI!',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
    
    public function getTargetTahunanByNip(Request $request)
    {
        try {
            $nip = $request->query('nip');
        
            if (!$nip) {
                return response()->json([
                    'message' => 'NIP tidak ditemukan dalam parameter!',
                ], 400);
            }
        
            $user = User::where('nip', $nip)->first();
        
            if (!$user) {
                return response()->json([
                    'message' => 'User dengan NIP tersebut tidak ditemukan!',
                ], 404);
            }
        
            $currentYear = date('Y');
        
            $targetTahunan = TargetTahunan::with('nilaiKpi')
                ->where('user_id', $user->id)
                ->where('tahun', $currentYear)
                ->get();
        
                if ($targetTahunan->isEmpty()) {
                    return response()->json([
                        'message' => 'Staff ini belum memiliki target tahunan',
                    ], 404);
                }
        
            $targetTahunanData = $targetTahunan->map(function ($target) use ($user) {
                $nilaiKpiData = $target->nilaiKpi->map(function ($nilaiKpi) use ($target) {
                    return [
                        'nama_kpi' => $nilaiKpi->kpi_nama,
                        'target' => $this->extractMonthlyTargets($target),
                    ];
                });
        
                return [
                    'nama' => $user->nama,
                    'nip' => $user->nip,
                    'id' => $target->id,
                    'user_id' => $target->user_id,
                    'tahun' => $target->tahun,
                    'target_kpi' => $nilaiKpiData,
                ];
            });
            return response()->json([
                'data' => $targetTahunanData,
                'message' => 'Login successful'
            ], 200);
        
        
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }
    
    private function extractMonthlyTargets(TargetTahunan $target)
    {
        $monthlyTarget = [];
    
        foreach ($target->target_kpi as $kpi) {
            $decodedTarget = is_array($kpi['target']) ? $kpi['target'] : json_decode($kpi['target'], true);
            
            if ($decodedTarget) {
                foreach ($decodedTarget as $month => $targetValue) {
                    $monthlyTarget[$month] = $targetValue;
                }
            }
        }
    
        return $monthlyTarget;
    }
    
    public function addTargetHarian(Request $request, $nip)
{
    // Validasi input
    $request->validate([
        'target_harian' => 'required|array',
        'target_harian.*' => 'required|string|max:255',
    ], [
        'target_harian.required' => 'Target harian harus diisi.',
        'target_harian.array' => 'Target harian harus berupa array.',
        'target_harian.*.required' => 'Setiap target harian harus diisi.',
        'target_harian.*.string' => 'Setiap target harian harus berupa teks.',
        'target_harian.*.max' => 'Setiap target harian tidak boleh lebih dari 255 karakter.',
    ]);

    // Cari pengguna berdasarkan NIP
    $user = User::where('nip', $nip)->first();

    if (!$user) {
        return response()->json([
            'message' => 'Pengguna tidak ditemukan'
        ], 404);
    }

    // Cek apakah target harian sudah diisi untuk hari ini
    $today = now()->format('Y-m-d');

    $existingTarget = TargetHarian::where('nip', $nip)
        ->whereDate('created_at', $today)
        ->exists();

    if ($existingTarget) {
        return response()->json([
            'message' => 'Anda sudah mengisi target harian untuk hari ini. Anda tidak dapat mengisi lagi.'
        ], 400);
    }

    try {
        // Simpan target harian
        foreach ($request->target_harian as $target) {
            TargetHarian::create([
                'nip' => $nip,
                'target' => $target,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Target Harian berhasil disimpan'
        ], 201);
    } catch (\Exception $e) {
        // Tangani kesalahan saat menyimpan data
        return response()->json([
            'message' => 'Terjadi kesalahan saat menyimpan Target Harian: ' . $e->getMessage()
        ], 500);
    }
}

    
    public function addTargetMingguan(Request $request, $nip)
    {
        // Validasi input
        $request->validate([
            'target_mingguan' => 'required|array',
            'target_mingguan.*' => 'required|string|max:255',
        ], [
            'target_mingguan.required' => 'Target mingguan harus diisi.',
            'target_mingguan.array' => 'Target mingguan harus berupa array.',
            'target_mingguan.*.required' => 'Setiap target mingguan harus diisi.',
            'target_mingguan.*.string' => 'Setiap target mingguan harus berupa teks.',
            'target_mingguan.*.max' => 'Setiap target mingguan tidak boleh lebih dari 255 karakter.',
        ]);
    
        // Cari pengguna berdasarkan NIP
        $user = User::where('nip', $nip)->first();
    
        if (!$user) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan'
            ], 404);
        }
    
        // Cek apakah target mingguan sudah diisi untuk minggu ini
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');
        
        $existingTarget = TargetMingguan::where('nip', $nip)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->exists();
    
        if ($existingTarget) {
            return response()->json([
                'message' => 'Anda sudah mengisi target mingguan untuk minggu ini. Anda tidak dapat mengisi lagi.'
            ], 400);
        }
    
        try {
            // Simpan target mingguan
            foreach ($request->target_mingguan as $target) {
                TargetMingguan::create([
                    'nip' => $nip,
                    'target' => $target,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            return response()->json([
                'message' => 'Target Mingguan berhasil disimpan'
            ], 201);
        } catch (\Exception $e) {
            // Tangani kesalahan saat menyimpan data
            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan Target Mingguan: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function getTargetMingguan()
    {
        $currentUser = Auth::user();
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');
    
        // Hapus data target mingguan yang tidak termasuk minggu berjalan
        TargetMingguan::whereDate('created_at', '<', $startOfWeek)->delete();
    
        // Ambil data sesuai jabatan
        if ($currentUser->jabatan === 'unit head') {
            $targets = TargetMingguan::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
        } else {
            $targets = TargetMingguan::where('nip', $currentUser->nip)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->get();
        }
    
        return response()->json([
            'status' => 'success',
            'data' => $targets,
        ]);
    }
    


    public function tambahAktivitas(Request $request)
    {
        \Log::info('Memulai proses tambah aktivitas');
        
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'nama_nasabah' => 'required|string',
            'alamat' => 'required|string',
            'nomor_hp_nasabah' => 'required|string',
            'aktivitas' => 'required|string|in:Tabungan,Depo Ritel,NTB - PBO,NOA BTN Move,Transaksi Teller,Transaksi CRM,Operasional MKK,QRIS,EDC,Agen,Kuadran Agen,NOA Payroll,VOA Payroll,NOA Pensiun,VOA Pensiun,VOA E-Batarapos,NOA Giro,Akuisi Satker,CMS,Jumlah PKS PPO,DPK Lembaga',
            'tipe_nasabah' => 'required|string',
            'prospek' => 'required|string',
            'nominal_prospek' => 'required|integer',
            'closing' => 'required|integer',
            'status_aktivitas' => 'required|string',
            'aktivitas_sales' => 'required|string',
            'keterangan_aktivitas' => 'required|string',
            'created_at' => 'nullable|date',
            'dokumentasi.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'nama_nasabah.required' => 'Nama nasabah harus diisi.',
            'alamat.required' => 'Alamat harus diisi.',
            'nomor_hp_nasabah.required' => 'Nomor HP nasabah harus diisi.',
            'aktivitas.required' => 'Aktivitas harus dipilih.',
            'aktivitas.in' => 'Aktivitas tidak valid.',
            'tipe_nasabah.required' => 'Tipe nasabah harus diisi.',
            'prospek.required' => 'Prospek harus diisi.',
            'nominal_prospek.required' => 'Nominal prospek harus diisi.',
            'nominal_prospek.integer' => 'Nominal prospek harus berupa angka.',
            'closing.required' => 'Closing harus diisi.',
            'closing.integer' => 'Closing harus berupa angka.',
            'status_aktivitas.required' => 'Status aktivitas harus diisi.',
            'aktivitas_sales.required' => 'Aktivitas sales harus diisi.',
            'keterangan_aktivitas.required' => 'Keterangan aktivitas harus diisi.',
            'created_at.date' => 'Tanggal aktivitas harus berupa tanggal yang valid.',
            'dokumentasi.*.mimes' => 'Dokumentasi harus berupa file dengan format jpg, jpeg, png, pdf.',
            'dokumentasi.*.max' => 'Ukuran file dokumentasi tidak boleh lebih dari 2MB.',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Ada kesalahan pada input data.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        \Log::info('Validasi berhasil');
        
        try {
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
    
            // Siapkan data aktivitas
            $aktivitas = new Aktivitas();
            $aktivitas->nasabah_id = $nasabah->id;
            $aktivitas->aktivitas = $request->aktivitas;
            $aktivitas->tipe_nasabah = $request->tipe_nasabah;
            $aktivitas->prospek = $request->prospek;
            $aktivitas->nominal_prospek = $request->nominal_prospek;
            $aktivitas->closing = $request->closing;
            $aktivitas->status_aktivitas = $request->status_aktivitas;
            $aktivitas->aktivitas_sales = $request->aktivitas_sales;
            $aktivitas->keterangan_aktivitas = $request->keterangan_aktivitas;
            $aktivitas->created_by = $user->id;
    
            // Jika created_at diinputkan, gunakan itu, jika tidak, otomatis menggunakan waktu sekarang
            $aktivitas->created_at = $request->created_at ? $request->created_at : now();
    
            \Log::info('Aktivitas disiapkan untuk disimpan', $aktivitas->toArray());
            $aktivitas->save();
    
            // Proses dokumentasi jika ada
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
            return response()->json(['message' => 'Terjadi kesalahan saat menambahkan aktivitas.'], 500);
        }
    }
    
    
    
    
    public function getTargetHarian()
    {
        $currentUser = Auth::user();
        $today = now()->format('Y-m-d');
    
        // Hapus data target harian yang bukan dari hari ini
        TargetHarian::whereDate('created_at', '<', $today)->delete();
    
        // Ambil data sesuai jabatan
        if ($currentUser->jabatan === 'unit head') {
            $targets = TargetHarian::whereDate('created_at', $today)->get();
        } else {
            $targets = TargetHarian::where('nip', $currentUser->nip)
                ->whereDate('created_at', $today)
                ->get();
        }
    
        return response()->json([
            'status' => 'success',
            'data' => $targets,
        ]);
    }
    

    public function updateTargetHarian(Request $request, $nip)
    {
        $request->validate([
            'target_harian' => 'required|array',
            'target_harian.*' => 'required|string|max:255', // Validasi setiap target harian
        ], [
            'target_harian.required' => 'Target harian harus diisi.',
            'target_harian.array' => 'Target harian harus berupa array.',
            'target_harian.*.required' => 'Setiap target harian harus diisi.',
            'target_harian.*.string' => 'Setiap target harian harus berupa teks.',
            'target_harian.*.max' => 'Setiap target harian tidak boleh lebih dari 255 karakter.',
        ]);
    
        // Cari user berdasarkan NIP
        $user = User::where('nip', $nip)->first();
        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }
    
        // Cek apakah target harian sudah diisi sebelumnya untuk pengguna pada hari ini
        $existingTarget = TargetHarian::where('nip', $nip)
            ->whereDate('created_at', now()->format('Y-m-d'))
            ->exists();
    
        if (!$existingTarget) {
            return response()->json([
                'message' => 'Tidak ada target harian untuk diperbarui pada hari ini.'
            ], 400);
        }
    
        try {
            // Hapus target harian lama untuk user ini pada hari ini
            TargetHarian::where('nip', $nip)
                ->whereDate('created_at', now()->format('Y-m-d'))
                ->delete();
    
            // Simpan target harian baru
            foreach ($request->target_harian as $target) {
                TargetHarian::create([
                    'nip' => $nip,
                    'target' => $target,
                    'created_at' => now()->format('Y-m-d'),
                ]);
            }
    
            return response()->json(['message' => 'Target Harian berhasil diperbarui'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui Target Harian: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function updateTargetMingguan(Request $request, $nip)
{
    $request->validate([
        'target_mingguan' => 'required|array',
        'target_mingguan.*' => 'required|string|max:255', // Validasi setiap target mingguan
    ], [
        'target_mingguan.required' => 'Target mingguan harus diisi.',
        'target_mingguan.array' => 'Target mingguan harus berupa array.',
        'target_mingguan.*.required' => 'Setiap target mingguan harus diisi.',
        'target_mingguan.*.string' => 'Setiap target mingguan harus berupa teks.',
        'target_mingguan.*.max' => 'Setiap target mingguan tidak boleh lebih dari 255 karakter.',
    ]);

    // Cari user berdasarkan NIP
    $user = User::where('nip', $nip)->first();
    if (!$user) {
        return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
    }

    // Tentukan minggu saat ini (misalnya berdasarkan tanggal mulai minggu ini)
    $startOfWeek = now()->startOfWeek()->format('Y-m-d');
    $endOfWeek = now()->endOfWeek()->format('Y-m-d');

    // Cek apakah target mingguan sudah diisi sebelumnya untuk pengguna pada minggu ini
    $existingTarget = TargetMingguan::where('nip', $nip)
        ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->exists();

    if (!$existingTarget) {
        return response()->json([
            'message' => 'Tidak ada target mingguan untuk diperbarui pada minggu ini.'
        ], 400);
    }

    try {
        // Hapus target mingguan lama untuk user ini pada minggu ini
        TargetMingguan::where('nip', $nip)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->delete();

        // Simpan target mingguan baru
        foreach ($request->target_mingguan as $target) {
            TargetMingguan::create([
                'nip' => $nip,
                'target' => $target,
                'created_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Target Mingguan berhasil diperbarui'], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat memperbarui Target Mingguan: ' . $e->getMessage()
        ], 500);
    }
}

}

