<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        DB::table('users')
            ->whereNotNull('role')
            ->update([
                'role' => DB::raw('LOWER(role)'),
            ]);
    }

    public function down(): void
    {
        // Tidak ada rollback karena normalisasi ini bersifat perbaikan data permanen.
    }
};

