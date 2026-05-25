<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class UserReportSeeder extends Seeder
{
    /**
     * City options keyed by country code.
     *
     * @var array<string, list<string>>
     */
    private const CITIES_BY_COUNTRY = [
        'US' => ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'],
        'UK' => ['London', 'Manchester', 'Birmingham', 'Leeds', 'Glasgow', 'Sheffield', 'Bradford', 'Liverpool', 'Edinburgh', 'Bristol'],
        'KH' => ['Phnom Penh', 'Siem Reap', 'Battambang', 'Sihanoukville', 'Kampong Cham', 'Krong Preah Sihanouk', 'Kampot', 'Takeo', 'Pursat', 'Kep'],
        'TH' => ['Bangkok', 'Chiang Mai', 'Phuket', 'Pattaya', 'Khon Kaen', 'Hat Yai', 'Nakhon Ratchasima', 'Udon Thani', 'Chiang Rai', 'Rayong'],
        'SG' => ['Singapore', 'Jurong East', 'Tampines', 'Woodlands', 'Bedok', 'Queenstown', 'Geylang', 'Toa Payoh', 'Yishun', 'Ang Mo Kio'],
        'AU' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth', 'Adelaide', 'Gold Coast', 'Canberra', 'Newcastle', 'Wollongong', 'Hobart'],
        'JP' => ['Tokyo', 'Osaka', 'Nagoya', 'Sapporo', 'Fukuoka', 'Kobe', 'Kyoto', 'Kawasaki', 'Saitama', 'Hiroshima'],
        'DE' => ['Berlin', 'Hamburg', 'Munich', 'Cologne', 'Frankfurt', 'Stuttgart', 'Dusseldorf', 'Leipzig', 'Dortmund', 'Essen'],
        'FR' => ['Paris', 'Marseille', 'Lyon', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille'],
        'CA' => ['Toronto', 'Montreal', 'Vancouver', 'Calgary', 'Edmonton', 'Ottawa', 'Winnipeg', 'Quebec City', 'Hamilton', 'Kitchener'],
    ];

    /**
     * Approximate geo bounding boxes [lat_min, lat_max, lng_min, lng_max] per country.
     *
     * @var array<string, array{float, float, float, float}>
     */
    private const GEO_BOUNDS = [
        'US' => [24.5,  49.0,  -124.8, -66.9],
        'UK' => [49.9,  58.7,  -8.2,     1.8],
        'KH' => [10.4,  14.7,  102.3,  107.6],
        'TH' => [5.6,   20.5,   97.3,  105.6],
        'SG' => [1.1,    1.5,  103.6,  104.1],
        'AU' => [-43.6, -10.7, 113.3,  153.6],
        'JP' => [24.3,   45.5, 122.9,  153.0],
        'DE' => [47.3,   55.1,   5.9,   15.0],
        'FR' => [41.3,   51.1,  -5.1,    9.6],
        'CA' => [42.0,   83.1, -141.0,  -52.6],
    ];

    /**
     * Signup source options.
     *
     * @var list<string>
     */
    private const SIGNUP_SOURCES = ['google', 'facebook', 'twitter', 'direct', 'referral'];

    /**
     * Country options.
     *
     * @var list<string>
     */
    private const COUNTRIES = ['US', 'UK', 'KH', 'TH', 'SG', 'AU', 'JP', 'DE', 'FR', 'CA'];

    public function run(): void
    {
        $faker = Faker::create();

        $usedUsernames = [];
        $usedEmails    = [];

        $users = [];

        for ($i = 0; $i < 200; $i++) {
            // Unique username
            do {
                $username = $faker->userName() . $faker->randomNumber(3);
            } while (in_array($username, $usedUsernames, true));
            $usedUsernames[] = $username;

            // Unique email
            do {
                $email = $faker->unique()->safeEmail();
            } while (in_array($email, $usedEmails, true));
            $usedEmails[] = $email;

            // Country and city
            $country = self::COUNTRIES[array_rand(self::COUNTRIES)];
            $cities  = self::CITIES_BY_COUNTRY[$country];
            $city    = $cities[array_rand($cities)];

            // Device type — weighted: 60% mobile, 30% desktop, 10% tablet
            $deviceRoll = $faker->numberBetween(1, 100);
            if ($deviceRoll <= 60) {
                $deviceType = 'mobile';
            } elseif ($deviceRoll <= 90) {
                $deviceType = 'desktop';
            } else {
                $deviceType = 'tablet';
            }

            // Avatar — 70% chance
            $avatar = $faker->boolean(70)
                ? "https://i.pravatar.cc/150?u={$email}"
                : null;

            // Geo coordinates — 80% chance
            $geoLat  = null;
            $geoLong = null;
            if ($faker->boolean(80)) {
                [$latMin, $latMax, $lngMin, $lngMax] = self::GEO_BOUNDS[$country];
                $geoLat  = round($faker->randomFloat(6, $latMin, $latMax), 6);
                $geoLong = round($faker->randomFloat(6, $lngMin, $lngMax), 6);
            }

            // last_login_at — 10% never logged in
            $lastLoginAt = $faker->boolean(90)
                ? $faker->dateTimeBetween('-60 days', 'now')->format('Y-m-d H:i:s')
                : null;

            // created_at — random in past 2 years
            $createdAt = $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s');

            $users[] = [
                'name'          => $faker->name(),
                'username'      => $username,
                'email'         => $email,
                'password'      => Hash::make('password'),
                'country'       => $country,
                'city'          => $city,
                'device_type'   => $deviceType,
                'signup_source' => self::SIGNUP_SOURCES[array_rand(self::SIGNUP_SOURCES)],
                'avatar'        => $avatar,
                'geo_lat'       => $geoLat,
                'geo_long'      => $geoLong,
                'last_login_at' => $lastLoginAt,
                'created_at'    => $createdAt,
                'updated_at'    => $createdAt,
            ];
        }

        // Insert in chunks to avoid large query strings
        foreach (array_chunk($users, 50) as $chunk) {
            DB::table('users')->insert($chunk);
        }
    }
}
