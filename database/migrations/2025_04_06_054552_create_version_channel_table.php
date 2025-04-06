<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('version_channel', function (Blueprint $table) {
            $table->foreignId('version_id')
                ->constrained('versions')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->foreignId('channel_id')
                ->nullable()
                ->constrained('channels')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
            $table->unique(['version_id', 'channel_id']);
        });

        $records = DB::table('versions')->get(['id', 'channel_id'])
            ->map(fn($version) => [
                'version_id' => $version->id,
                'channel_id' => $version->channel_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        DB::table('version_channel')->insert($records->toArray());

        Schema::table('versions', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->dropColumn('channel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->foreignId('channel_id')
                ->after('project_id')
                ->nullable()
                ->constrained('channels')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
        });
        Schema::dropIfExists('version_channel');
    }
};
