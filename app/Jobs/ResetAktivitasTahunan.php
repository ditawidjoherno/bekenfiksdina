<?php

namespace App\Jobs;

use App\Models\AktivitasBulanan;
use Carbon\Carbon;

class ResetAktivitasTahunan extends Job
{
    public function handle()
    {
        $currentYear = Carbon::now()->year;
        AktivitasBulanan::where('tahun', $currentYear)->delete();
    }
}
