<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * gallery: extra screenshots, loaded only when the details modal opens.
 * link_type: whether `url` is a live demo or a download.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('image');
            $table->string('link_type', 20)->default('demo')->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['gallery', 'link_type']);
        });
    }
};
