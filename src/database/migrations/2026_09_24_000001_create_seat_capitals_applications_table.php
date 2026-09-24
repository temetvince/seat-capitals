<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * One row per capital build application. Applications are never deleted by
 * the plugin; a withdrawn or denied application keeps its row so the history
 * stays auditable.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('seat_capitals_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('character_id');
            $table->integer('type_id');
            $table->text('justification');
            $table->string('status', 16);
            $table->bigInteger('reviewer_user_id')->nullable();
            $table->text('decision_note')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('character_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_capitals_applications');
    }
};
