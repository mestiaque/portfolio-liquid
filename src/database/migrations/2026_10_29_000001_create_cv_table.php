<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The CV at /cv: one row holding every section as JSON. It is also the source
 * the portfolio page reads its experience, education and skills from.
 * Section keys and shapes: Support\Cv\Sections.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv', function (Blueprint $table) {
            $table->id();

            // Layout and look
            $table->string('preset', 40)->default('software');
            $table->string('template', 30)->default('classic');
            $table->json('theme')->nullable();
            $table->json('sections')->nullable();      // order, on/off, EN/BN titles, web/print per section

            // Who can see /cv
            $table->string('visibility', 20)->default('public');   // public | unlisted | password | private
            $table->string('password')->nullable();                 // hashed, for "password"
            $table->boolean('noindex')->default(false);

            // Content, one column per section
            $table->json('personal')->nullable();
            $table->json('career')->nullable();
            $table->json('employment')->nullable();
            $table->json('education')->nullable();
            $table->json('training')->nullable();
            $table->json('certifications')->nullable();
            $table->json('registrations')->nullable();
            $table->json('skills')->nullable();
            $table->json('computer_skills')->nullable();
            $table->json('languages')->nullable();
            $table->json('projects')->nullable();
            $table->json('achievements')->nullable();
            $table->json('extracurricular')->nullable();
            $table->json('personal_details')->nullable();
            $table->json('references')->nullable();
            $table->json('declaration')->nullable();
            $table->json('custom_sections')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv');
    }
};
