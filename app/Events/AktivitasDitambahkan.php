<?php

namespace App\Events;

use App\Models\Aktivitas;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AktivitasDitambahkan
{
    use Dispatchable, SerializesModels;

    public $aktivitas;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Aktivitas $aktivitas)
    {
        $this->aktivitas = $aktivitas;
    }
}
