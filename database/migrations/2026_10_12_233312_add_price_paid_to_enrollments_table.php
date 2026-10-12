<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedInteger('price_paid')->nullable()->default(null)->after('course_id');
        });

        try {
            $transactions = DB::table('wallet_transactions')
                ->where('type', 'course_purchase')
                ->where('status', 'completed')
                ->get();

            foreach ($transactions as $tx) {
                DB::table('enrollments')
                    ->where('student_id', $tx->user_id)
                    ->where('course_id', $tx->reference_id)
                    ->update(['price_paid' => $tx->amount]);
            }
        } catch (Throwable $e) {
            // Ignore during fresh testing setup
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('price_paid');
        });
    }
};
