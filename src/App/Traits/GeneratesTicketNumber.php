<?php

namespace Paparee\BaleNawasara\App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait GeneratesTicketNumber
{
    /**
     * Boot trait agar nomor tiket otomatis terisi saat create.
     */
    public static function bootGeneratesTicketNumber()
    {
        static::creating(function ($model) {
            $model->ticket_number = self::generateTicketNumber($model);
        });
    }

    /**
     * Generate nomor tiket dengan format:
     * #PREFIX-YYYYMMDD-XXX
     */
    protected static function generateTicketNumber($model): string
    {
        $date = Carbon::now()->format('dmY');

        // Ambil prefix dari model, fallback ke "SSO"
        $prefix = property_exists($model, 'ticketPrefix')
            ? $model->ticketPrefix
            : 'SSO';

        // Hitung jumlah tiket pada hari ini
        $countToday = DB::table($model->getTable())
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Nomor urut dengan 3 digit (001, 002, dst)
        $sequence = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        return "#{$prefix}-{$date}-{$sequence}";
    }
}
