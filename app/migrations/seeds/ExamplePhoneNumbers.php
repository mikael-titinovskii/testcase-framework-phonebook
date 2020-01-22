<?php


use Phinx\Seed\AbstractSeed;

class ExamplePhoneNumbers extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $data[] = [
                'first_name'    => $faker->firstName,
                'last_name'     => $faker->lastName,
                'phone_number'     => $faker->phoneNumber,
                'country_code'     => $faker->countryCode,
                'timezone_name'     => $faker->timezone,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];
        }


        $this->table('phone_book')->insert($data)->saveData();
    }
}
