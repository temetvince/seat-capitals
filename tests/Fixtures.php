<?php

/*
 * This file is part of SeAT Capitals.
 *
 * This is free and unencumbered software released into the public domain.
 * For more information, please refer to <https://unlicense.org> or to the
 * LICENSE file distributed with this software.
 */

namespace temetvince\SeatCapitals\Tests;

use Illuminate\Support\Facades\DB;
use Seat\Eveapi\Models\Character\CharacterInfo;
use Seat\Web\Models\User;

/**
 * Row builders for the SQLite test schema.
 *
 * Every method inserts exactly the columns the plugin reads and returns the
 * model where a test needs one. Ids are chosen by the caller so assertions
 * can name them.
 */
trait Fixtures
{
    protected function makeUser(int $id, string $name, bool $admin = false, ?int $main_character_id = null): User
    {
        DB::table('users')->insert([
            'id' => $id,
            'name' => $name,
            'active' => true,
            'admin' => $admin,
            'main_character_id' => $main_character_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::findOrFail($id);
    }

    /**
     * A character with a live token on the given user, affiliated to a corporation.
     */
    protected function linkCharacter(int $character_id, string $name, int $user_id, int $corporation_id = 98000001): CharacterInfo
    {
        DB::table('character_infos')->insert([
            'character_id' => $character_id,
            'name' => $name,
            'birthday' => '2010-01-01 00:00:00',
            'gender' => 'male',
            'race_id' => 1,
            'bloodline_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('character_affiliations')->insert([
            'character_id' => $character_id,
            'corporation_id' => $corporation_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('refresh_tokens')->insert([
            'character_id' => $character_id,
            'version' => 2,
            'user_id' => $user_id,
            'refresh_token' => 'refresh',
            'scopes' => '[]',
            'expires_on' => now()->addHour(),
            'token' => 'access',
            'character_owner_hash' => 'hash-' . $user_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return CharacterInfo::findOrFail($character_id);
    }

    protected function makeGroup(int $group_id, string $name, int $category_id = 6): void
    {
        DB::table('invGroups')->insert([
            'groupID' => $group_id,
            'categoryID' => $category_id,
            'groupName' => $name,
        ]);
    }

    protected function makeType(int $type_id, int $group_id, string $name, bool $published = true): void
    {
        DB::table('invTypes')->insert([
            'typeID' => $type_id,
            'groupID' => $group_id,
            'typeName' => $name,
            'published' => $published,
        ]);
    }

    protected function makeSystem(int $system_id, string $name, float $security): void
    {
        DB::table('solar_systems')->insert([
            'system_id' => $system_id,
            'constellation_id' => 1,
            'region_id' => 1,
            'name' => $name,
            'security' => $security,
        ]);
    }

    protected function makeStation(int $station_id, string $name, int $system_id): void
    {
        DB::table('universe_stations')->insert([
            'station_id' => $station_id,
            'name' => $name,
            'system_id' => $system_id,
        ]);
    }

    protected function makeStructure(int $structure_id, string $name, int $system_id): void
    {
        DB::table('universe_structures')->insert([
            'structure_id' => $structure_id,
            'name' => $name,
            'solar_system_id' => $system_id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides  any `character_assets` column
     */
    protected function makeAsset(int $item_id, int $character_id, int $type_id, string $location_type, int $location_id, array $overrides = []): void
    {
        DB::table('character_assets')->insert(array_merge([
            'item_id' => $item_id,
            'character_id' => $character_id,
            'type_id' => $type_id,
            'quantity' => 1,
            'location_id' => $location_id,
            'location_type' => $location_type,
            'location_flag' => 'Hangar',
            'is_singleton' => true,
            'map_id' => 0,
            'map_name' => '',
            'name' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }
}
