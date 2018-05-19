<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(HotelsTableSeeder::class);
        $this->call(UsersRatingHotelsSeeder::class);
        $this->call(VisitedHotelsSeeder::class);
        $this->call(AttractionsTableSeeder::class);
        $this->call(UsersRatingAttractionsSeeder::class);
        $this->call(VisitedAttractionsSeeder::class);
    }
}
