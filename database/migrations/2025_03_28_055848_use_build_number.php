<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->previous()->down();
        Schema::table('versions', function (Blueprint $table) {
            $table->dropColumn('android_requirements');
            $table->dropColumn('ios_requirements');

            $table->boolean('android_available')
                ->after('signature')
                ->default(true);
            $table->unsignedInteger('android_min')->after('android_available')->nullable();
            $table->unsignedInteger('android_max')->after('android_min')->nullable();

            $table->boolean('ios_available')
                ->after('android_max')
                ->default(true);
            $table->unsignedInteger('ios_min')->after('ios_available')->nullable();
            $table->unsignedInteger('ios_max')->after('ios_min')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('versions', function (Blueprint $table) {
            $table->dropColumn('android_min');
            $table->dropColumn('android_max');

            $table->dropColumn('ios_min');
            $table->dropColumn('ios_max');

            $table->string('android_requirements')
                ->after('signature')
                ->nullable();
            $table->string('ios_requirements')
                ->after('android_requirements')
                ->nullable();
        });
        $this->previous()->up();
    }

    private function previous(): Migration
    {
        $class = include __DIR__ . '/2025_01_21_051431_create_version_resolutions_table.php';
        return new $class();
    }
};
