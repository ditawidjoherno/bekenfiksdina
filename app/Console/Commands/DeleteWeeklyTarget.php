<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TargetMingguan;

class DeleteWeeklyTarget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'target:clear-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus semua target mingguan dari database setiap minggu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Hapus semua data di tabel target_mingguan
            TargetMingguan::truncate();

            $this->info('Semua target mingguan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->error('Gagal menghapus target mingguan: ' . $e->getMessage());
        }
    }
}
