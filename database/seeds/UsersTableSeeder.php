<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() == 0) {
            $role = Role::where('name', 'admin')->firstOrFail();

            User::create([
                'name'           => 'Admin',
                'email'          => 'admin@admin.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
                'role_id'        => $role->id,
            ]);
        }

        $faker = Faker\Factory::create();

        for ($i = 0; $i < 300; $i++) {
            $role = Role::where('name', 'user')->firstOrFail();
            $name = $faker->unique()->name;
            User::create([
                'name' => $name,
                'email' => str_replace(' ', '', $name) . "@example.com",
                'password' => bcrypt('123456'),
                'role_id' => $role->id,
            ]);
        }
    }
}
