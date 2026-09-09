<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->string('sowing_season', 100)->nullable()->after('description');
            $table->string('sunlight', 100)->nullable()->after('sowing_season');
            $table->string('germination_days', 100)->nullable()->after('sunlight');
            $table->string('harvest_days', 100)->nullable()->after('germination_days');
            $table->string('plant_spacing', 100)->nullable()->after('harvest_days');
            $table->string('sowing_depth', 100)->nullable()->after('plant_spacing');
            $table->string('growing_difficulty', 50)->nullable()->after('sowing_depth');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['sowing_season', 'sunlight', 'germination_days', 'harvest_days', 'plant_spacing', 'sowing_depth', 'growing_difficulty']);
        });
    }
};
