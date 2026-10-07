<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('billing_term', 30)->nullable()->after('package_id');
        });

        DB::table('memberships')
            ->join('packages', 'packages.id', '=', 'memberships.package_id')
            ->update(['memberships.billing_term' => DB::raw('packages.term')]);
    }

    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn('billing_term');
        });
    }
};
