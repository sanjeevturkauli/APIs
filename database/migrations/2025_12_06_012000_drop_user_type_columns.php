<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'user_type')) {
                $table->dropColumn('user_type');
            }

            if (Schema::hasColumn('users', 'team_name')) {
                $table->dropColumn('team_name');
            }

            if (Schema::hasColumn('users', 'member_id')) {
                $table->dropColumn('member_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_type')) {
                $table->enum('user_type', ['team', 'member'])->nullable()->after('password');
            }

            if (! Schema::hasColumn('users', 'team_name')) {
                $table->string('team_name')->nullable()->after('user_type');
            }

            if (! Schema::hasColumn('users', 'member_id')) {
                $table->string('member_id')->nullable()->after('team_name');
            }
        });
    }
};
