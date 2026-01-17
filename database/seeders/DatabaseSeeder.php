<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $user = User::create([
            'email'=>'123@gmail.com',
            'password'=>bcrypt('123'),
            'role'=>'admin',
        ]);
        $user->profile()->create([
            'name'=>'Admin User',
        ]);
        // User::factory(10)->create();
        // User::factory()
        //     ->workerRole()
        //     ->hasProfile()
        //     ->hasPosts(8)
        //     ->hasLikes(5)
        //     ->hasComments(4)
        //     ->hasWorker()
        //     ->hasReplies(5)
        //     ->hasReports(2)
        //     ->count(10)
        //     ->create();
        // User::factory()
        //     ->managerRole()
        //     ->hasProfile()
        //     ->hasReports(2)
        //     ->hasPosts(8)
        //     ->hasComments(4)
        //     ->hasLikes(5)
        //     ->hasReplies(5)
        //     ->count(10)
        //     ->create();
    }
}
