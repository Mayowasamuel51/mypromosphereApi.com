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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id")->nullable();
            $table->integer("amount")->nullable();
            $table->string("TransactionID")->nullable();
            $table->integer("paymentMethod")->nullable();
            $table->string("status")->nullable(); // bilded , paid , void, 
            $table->dateTime("billed_dated")->nullable();
            $table->dateTime("paid_dated")->nullable();
            $table->foreign("user_id")->references()->on("users")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
