<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Everything the public project card and details modal show, so projects can be
 * managed entirely from the admin panel instead of config/portfolio.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('summary', 500)->nullable()->after('title');
            $table->json('categories')->nullable()->after('technologies');
            $table->json('features')->nullable()->after('categories');
            $table->text('problem')->nullable()->after('features');
            $table->text('solution')->nullable()->after('problem');
            $table->text('challenges')->nullable()->after('solution');
            $table->string('icon', 60)->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['summary', 'categories', 'features', 'problem', 'solution', 'challenges', 'icon']);
        });
    }
};
