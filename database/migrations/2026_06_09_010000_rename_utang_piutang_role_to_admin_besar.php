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
            ->whereRaw('LOWER(role) = ?', ['utang_piutang'])
            ->update(['role' => 'admin_besar']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'role')) {
            return;
        }

        DB::table('users')
            ->whereRaw('LOWER(role) = ?', ['admin_besar'])
            ->update(['role' => 'utang_piutang']);
    }
};
