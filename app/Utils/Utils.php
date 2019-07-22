<?php

namespace App\Utils;

use App\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Validator;
use function is_null;
use const ROLE_BOAT_MANAGER;

class Utils
{
    public static function autoIncrement()
    {
        for ($i = 0; $i < 1000; $i++) {
            yield $i;
        }
    }

    public static function getFakeStormEmail($username = null)
    {
        $progressive = self::autoIncrement();
        $faker = Faker::create();
        if (is_null($username)) {
            $username = $faker->userName;
        }

        do {
            $email = $username.$progressive->current().STORM_EMAIL_SUFFIX;
            // Must not already exist in the `email` column of `users` table
            $validator = Validator::make(['email' => $email], ['email' => 'unique:users']);
            $progressive->next();
        } while($validator->fails());

        return $email;
    }

    public static function getAllBoatManagers() {
        return User::permission(PERMISSION_BOAT_MANAGER)->get();
//        return User::role(ROLE_BOAT_MANAGER)->get();
    }
}
