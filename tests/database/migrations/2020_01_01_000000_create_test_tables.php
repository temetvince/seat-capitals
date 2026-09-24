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
 * SQLite stand-ins for the upstream tables the plugin reads.
 *
 * Upstream's own migrations target MySQL, so tests recreate just the columns
 * the plugin and its fixtures touch, with the same names and types as the
 * real schema. The plugin's own table comes from its real migration.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('name')->unique();
            $table->string('email')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('admin')->default(false);
            $table->bigInteger('main_character_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->bigInteger('character_id')->primary();
            $table->unsignedSmallInteger('version');
            $table->bigInteger('user_id');
            $table->mediumText('refresh_token');
            $table->longText('scopes');
            $table->dateTime('expires_on');
            $table->text('token');
            $table->string('character_owner_hash');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('character_infos', function (Blueprint $table) {
            $table->bigInteger('character_id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('birthday');
            $table->string('gender');
            $table->integer('race_id');
            $table->integer('bloodline_id');
            $table->double('security_status')->nullable();
            $table->timestamps();
        });

        Schema::create('character_affiliations', function (Blueprint $table) {
            $table->bigInteger('character_id')->primary();
            $table->bigInteger('corporation_id');
            $table->bigInteger('alliance_id')->nullable();
            $table->bigInteger('faction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('character_assets', function (Blueprint $table) {
            $table->bigInteger('item_id')->primary();
            $table->bigInteger('character_id');
            $table->integer('type_id');
            $table->integer('quantity');
            $table->bigInteger('location_id');
            $table->string('location_type');
            $table->string('location_flag');
            $table->boolean('is_singleton');
            $table->boolean('is_blueprint_copy')->nullable();
            $table->double('x')->nullable();
            $table->double('y')->nullable();
            $table->double('z')->nullable();
            $table->bigInteger('map_id')->nullable();
            $table->string('map_name')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('invTypes', function (Blueprint $table) {
            $table->integer('typeID')->primary();
            $table->integer('groupID');
            $table->string('typeName');
            $table->boolean('published')->default(true);
        });

        Schema::create('invGroups', function (Blueprint $table) {
            $table->integer('groupID')->primary();
            $table->integer('categoryID');
            $table->string('groupName');
        });

        Schema::create('solar_systems', function (Blueprint $table) {
            $table->integer('system_id')->primary();
            $table->integer('constellation_id');
            $table->integer('region_id');
            $table->string('name');
            $table->double('security');
        });

        Schema::create('universe_stations', function (Blueprint $table) {
            $table->integer('station_id')->primary();
            $table->integer('type_id')->nullable();
            $table->string('name');
            $table->integer('system_id');
        });

        Schema::create('universe_structures', function (Blueprint $table) {
            $table->bigInteger('structure_id')->primary();
            $table->string('name');
            $table->integer('solar_system_id');
            $table->integer('type_id')->nullable();
        });

        Schema::create('global_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->mediumText('value');
            $table->timestamps();
        });

        Schema::create('notification_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('group_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('notification_group_id');
            $table->string('alert');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'group_alerts', 'notification_groups', 'global_settings', 'universe_structures',
            'universe_stations', 'solar_systems', 'invGroups', 'invTypes', 'character_assets',
            'character_affiliations', 'character_infos', 'refresh_tokens', 'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
