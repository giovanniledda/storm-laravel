<?php

namespace App\Utils;

use App\User;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Validator;
use function is_null;
use const PERMISSION_ADMIN;
use const PERMISSION_WORKER;
use const ROLE_ADMIN;
use const ROLE_BOAT_MANAGER;
use const ROLE_WORKER;
use Webpatser\Countries\Countries;

class Utils
{
    /**
     * Get an incremental ID for factories use
     *
     * @return \Generator
     */
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

    public static function getAdmins() {
        return User::permission(PERMISSION_ADMIN)->get();
//        return User::role(ROLE_ADMIN)->get();
    }

    public static function getAllBoatManagers() {
        return User::permission(PERMISSION_BOAT_MANAGER)->get();
//        return User::role(ROLE_BOAT_MANAGER)->get();
    }

    public static function getAllWorkers() {
        return User::permission(PERMISSION_WORKER)->get();
//        return User::role(ROLE_WORKER)->get();
    }

    /**
     * An alias for the strstr function
     *
     * @param string $string
     * @param array $placeholders
     * @return string
     */
    public static function replacePlaceholders($string = '', $placeholders = [])
    {
        return strtr($string, $placeholders);
    }

    /**
     * Returns a Response with JSONAPI header
     *
     */
    public static function renderStandardJsonapiResponse($data, $code)
    {
        $resp = Response($data, $code);
        $resp->header('Content-Type', 'application/vnd.api+json');
        return $resp;
    }

    /**
     * Get the list of countries for @countries component
     *
     */
    public static function getCountriesList()
    {
        return Countries::orderBy('name')
            ->whereNotNull('name')
            ->pluck('name', 'iso_3166_2');
    }

    /**
     * Get the list of telephone types defined in constants
     *
     */
    public static function getPhoneTypes()
    {
        return USER_PHONE_TYPES;
    }
}
