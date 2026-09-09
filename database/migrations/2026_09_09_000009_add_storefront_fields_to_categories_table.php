<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('menu_label', 100)->nullable()->after('name');
            $table->string('emoji', 16)->nullable()->after('menu_label');
            $table->text('description')->nullable()->after('emoji');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['menu_label', 'emoji', 'description']);
        });
    }
};
