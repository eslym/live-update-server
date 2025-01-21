<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('version_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();
            $table->string('platform');
            $table->string('app_version');
            $table->foreignId('version_id')
                ->nullable()
                ->constrained('versions')
                ->cascadeOnDelete();
            $table->boolean('needs_reindex')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'platform', 'app_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('version_resolutions');
    }
};
