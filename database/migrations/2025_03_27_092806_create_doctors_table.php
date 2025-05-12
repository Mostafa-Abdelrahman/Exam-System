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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id(); // Laravel default primary key
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('major_id')->nullable()->constrained('majors')->onDelete('set null');
            $table->string('specialization'); // Fixed typo from specilazation to specialization
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
