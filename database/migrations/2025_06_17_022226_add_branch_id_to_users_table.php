<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom ini akan menunjuk ke ID cabang
            $table->foreignId('branch_id')
                ->nullable() // User admin pusat mungkin tidak punya cabang
                ->constrained('branches')
                ->onDelete('set null') // Jika cabang dihapus, branch_id user jadi null
                ->after('email'); // Posisikan setelah 'email' atau sesuai keinginan
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
