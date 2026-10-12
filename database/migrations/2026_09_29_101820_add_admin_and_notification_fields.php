<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('status', 30)->default('new');
            $table->string('notification_status', 30)->default('not_configured');
            $table->timestamp('notified_at')->nullable();
            $table->text('notification_error')->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropColumn(['status', 'notification_status', 'notified_at', 'notification_error', 'notes']);
        });
    }
};
