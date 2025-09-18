<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Surat;
use App\Models\User;
use App\Models\Tracking;
use Faker\Factory as Faker;

class TrackingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $surats = Surat::all();
        $users = User::all();

        if ($surats->isEmpty() || $users->isEmpty()) {
            return; // No surat or users to track
        }

        foreach ($surats as $surat) {
            // Initial creation tracking
            Tracking::create([
                'surat_id' => $surat->id,
                'user_id' => $surat->created_by,
                'action' => 'created',
                'keterangan' => 'Surat dibuat oleh ' . $surat->createdBy->nama,
                'data_after' => $surat->toArray(),
                'ip_address' => $faker->ipv4(),
                'user_agent' => $faker->userAgent(),
                'created_at' => $surat->created_at,
                'updated_at' => $surat->created_at,
            ]);

            // Simulate some status changes
            $actions = ['submitted', 'reviewed', 'approved', 'rejected', 'needs_revision'];
            $numActions = $faker->numberBetween(0, 3);

            for ($i = 0; $i < $numActions; $i++) {
                $action = $faker->randomElement($actions);
                $user = $users->random();
                $timestamp = $faker->dateTimeBetween($surat->created_at, 'now');

                Tracking::create([
                    'surat_id' => $surat->id,
                    'user_id' => $user->id,
                    'action' => $action,
                    'keterangan' => 'Surat ' . $action . ' oleh ' . $user->nama . '.',
                    'data_after' => $surat->toArray(), // Simplified, in real app this would be specific changes
                    'ip_address' => $faker->ipv4(),
                    'user_agent' => $faker->userAgent(),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }
    }
}