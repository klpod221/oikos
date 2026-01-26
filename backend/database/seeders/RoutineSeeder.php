<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Routine;
use App\Models\User;
use App\Models\WorkoutSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'klpod221@gmail.com')->first();

        if (!$user) {
            return;
        }

        // Get Exercises (Use Vietnamese names matching ExerciseSeeder)
        $pushUp = Exercise::where('name', 'Hít đất (Push-up)')->first();
        $squat = Exercise::where('name', 'Squat (Gánh tạ)')->first();
        $plank = Exercise::where('name', 'Plank')->first();
        $run = Exercise::where('name', 'Chạy bộ')->first();
        $pullUp = Exercise::where('name', 'Hít xà (Pull-up)')->first();

        if (!$pushUp) {
            return;
        }

        // Routine 1: Full Body Beginner
        $fullBody = Routine::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Toàn thân nhập môn',
            ],
            [
                'description' => 'Bài tập toàn thân đơn giản cho người mới bắt đầu.',
                'estimated_duration' => 45,
            ]
        );

        // Attach Exercises
        $fullBody->exercises()->detach();

        $fullBody->exercises()->attach([
            $pushUp->id => ['order' => 1, 'target_value' => 15, 'rest_time' => 60],
            $squat->id => ['order' => 2, 'target_value' => 20, 'rest_time' => 60],
            $pullUp->id => ['order' => 3, 'target_value' => 5, 'rest_time' => 90],
            $plank->id => ['order' => 4, 'target_value' => 60, 'rest_time' => 60], // 60 seconds
        ]);

        // Routine 2: Morning Cardio
        $cardio = Routine::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Cardio buổi sáng',
            ],
            [
                'description' => 'Bài tập tim mạch nhanh vào buổi sáng.',
                'estimated_duration' => 20,
            ]
        );

        $cardio->exercises()->detach();
        $cardio->exercises()->attach([
            $run->id => ['order' => 1, 'target_value' => 900, 'rest_time' => 0], // 15 mins (900s)
            $plank->id => ['order' => 2, 'target_value' => 120, 'rest_time' => 0],
        ]);

        // Seed Schedule
        WorkoutSchedule::updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'Lịch tập toàn thân tuần',
            ],
            [
                'routine_id' => $fullBody->id,
                'schedule_config' => [
                    'type' => 'weekly',
                    'days' => [1, 3, 5], // Mon, Wed, Fri
                ],
                'start_date' => Carbon::today(),
                'is_active' => true,
            ]
        );
    }
}
