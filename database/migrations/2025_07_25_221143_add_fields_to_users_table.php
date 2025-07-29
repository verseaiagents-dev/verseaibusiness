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
            $table->enum('role', ['user', 'admin'])->default('user')->after('password');
            $table->string('plan_type')->nullable()->after('role');
            $table->integer('token_balance')->default(50)->after('plan_type');
            $table->text('bio')->nullable()->after('token_balance');
            $table->string('sector')->nullable()->after('bio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'plan_type', 'token_balance', 'bio', 'sector']);
        });
    }
};
