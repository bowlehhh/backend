<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AdminBesarCache
{
    public static function forgetToday(?Carbon $date = null): void
    {
        $dateString = ($date ?? now())->toDateString();

        foreach (self::keysForDate($dateString) as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @return array<int, string>
     */
    private static function keysForDate(string $dateString): array
    {
        return [
            "admin-besar:dashboard:stats:{$dateString}",
            "admin-besar:dashboard:recent-transactions:{$dateString}",
            "admin-besar:dashboard:company-recap:{$dateString}",
            "admin-besar:history:sales:{$dateString}",
            "admin-besar:history:returns:{$dateString}",
            "admin-besar:history:installment-paid:{$dateString}",
            "admin-besar:history:ptcv:{$dateString}",
        ];
    }
}
