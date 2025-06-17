<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Kolom ini akan menunjuk ke ID cabang
            $table->foreignId('branch_id')
                ->nullable() // Atur nullable jika transaksi lama tidak punya cabang
                ->constrained('branches')
                ->onDelete('restrict') // Hindari penghapusan cabang yang masih punya transaksi
                ->after('id'); // Posisikan setelah kolom 'id'
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
