<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->id();

            // Название альбома
            $table->string('title');

            // Исполнитель (может быть пустым т.к. будет браться из API)
            $table->string('artist')->nullable();

            // Описание
            $table->text('description')->nullable();

            // Ссылка на обложку
            $table->string('cover_url')->nullable();

            // Кто создал запись (связь с users, если пользователь удаляется, то должны удаляться и его альбомы)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('albums');
    }
};
