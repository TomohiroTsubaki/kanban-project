<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Ditambahkan

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tasks')->insert([
            'name' => 'Develop Final Project',
            'detail' => 'Kanban project using PHP and Laravel',
            'due_date' => '2023-04-30',
            'status' => 'not_started',
        ]);

        DB::table('tasks')->insert([
            'name' => 'Lunch with Guru Domba',
            'detail' => 'Have Nasi Padang with Guru Domba',
            'due_date' => '2023-04-10',
            'status' => 'not_started',
        ]);

        DB::table('tasks')->insert([
            'name' => 'Learn Blade Templating',
            'detail' => 'Complete Blade Templating material on Progate',
            'due_date' => '2023-04-05',
            'status' => 'in_progress',
        ]);

        DB::table('tasks')->insert([
            'name' => 'Decide Plans for Lebaran holidays',
            'detail' => 'Trip with family?',
            'due_date' => '2023-04-21',
            'status' => 'in_progress',
        ]);

        DB::table('tasks')->insert([
            'name' => 'Develop a Laravel Project',
            'detail' => 'Develop a Kanban app and ask Guru Domba\'s review',
            'due_date' => '2023-04-30',
            'status' => 'in_review',
        ]);

        DB::table('tasks')->insert([
            'name' => 'Learn PHP Basics',
            'detail' => 'Complete PHP materials on Frontend Course',
            'due_date' => '2023-04-30',
            'status' => 'completed',
        ]);
    }
}
