<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->string('focus_keyword', 100)->nullable();
            $table->boolean('allow_comments')->default(true);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('author_id');
        });

        // Add fulltext index only for MySQL/MariaDB
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->fullText(['title', 'content', 'excerpt']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
