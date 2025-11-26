<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to comments table
        Schema::table('comments', function (Blueprint $table): void {
            $table->string('author_url')->nullable()->after('author_email');
            $table->boolean('is_notify_replies')->default(false)->after('user_agent');
            $table->timestamp('approved_at')->nullable()->after('is_notify_replies');
            $table->index('author_email');
        });

        // Add comments_count to posts table
        Schema::table('posts', function (Blueprint $table): void {
            $table->unsignedInteger('comments_count')->default(0)->after('view_count');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex(['author_email']);
            $table->dropColumn(['author_url', 'is_notify_replies', 'approved_at']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('comments_count');
        });
    }
};
