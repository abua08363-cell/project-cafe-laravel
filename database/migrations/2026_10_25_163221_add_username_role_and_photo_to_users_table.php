<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 100)->nullable()->unique();
            $table->string('role', 50)->default('admin');
            $table->string('photo')->nullable();
        });

        DB::table('users')
            ->whereNull('username')
            ->orderBy('id')
            ->get(['id', 'name', 'email'])
            ->each(function (object $user): void {
                $baseUsername = Str::slug(Str::before($user->email, '@'));

                if ($baseUsername === '') {
                    $baseUsername = Str::slug($user->name) ?: 'user';
                }

                $baseUsername = substr($baseUsername, 0, 100);
                $username = $baseUsername;
                $suffix = 1;

                while (DB::table('users')->where('username', $username)->exists()) {
                    $suffixText = '-'.$suffix++;
                    $username = substr($baseUsername, 0, 100 - strlen($suffixText)).$suffixText;
                }

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['username' => $username]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'role', 'photo']);
        });
    }
};
