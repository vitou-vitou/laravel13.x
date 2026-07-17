<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        foreach (DB::table('tunnels')->orderBy('id')->get(['id', 'domain']) as $tunnel) {
            if (isset($seen[$tunnel->domain])) {
                DB::table('tunnels')->where('id', $tunnel->id)->update([
                    'domain' => $tunnel->domain.'-'.$tunnel->id,
                ]);

                continue;
            }

            $seen[$tunnel->domain] = true;
        }

        Schema::table('tunnels', function (Blueprint $table) {
            $table->unique('domain');
        });
    }

    public function down(): void
    {
        Schema::table('tunnels', function (Blueprint $table) {
            $table->dropUnique(['domain']);
        });
    }
};
