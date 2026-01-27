<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // Chest
            [
                'name' => 'Hít đất (Push-up)',
                'description' => 'Bài tập trọng lượng cơ thể cổ điển cho ngực, vai và tay sau.',
                'muscle_group' => 'Ngực',
                'type' => 'reps',
                'calories_per_unit' => 0.5, // ~0.5 kcal per rep
                'is_global' => true,
            ],
            [
                'name' => 'Đẩy ngực (Bench Press)',
                'description' => 'Bài tập phức hợp tăng sức mạnh thân trên.',
                'muscle_group' => 'Ngực',
                'type' => 'reps',
                'calories_per_unit' => 2.0, // varies heavily by weight
                'is_global' => true,
            ],
            // Back
            [
                'name' => 'Hít xà (Pull-up)',
                'description' => 'Bài tập kéo thân trên, phát triển cơ xô và tay trước.',
                'muscle_group' => 'Lưng',
                'type' => 'reps',
                'calories_per_unit' => 1.0,
                'is_global' => true,
            ],
            [
                'name' => 'Deadlift',
                'description' => 'Bài tập toàn thân, tập trung vào chuỗi cơ sau.',
                'muscle_group' => 'Lưng/Đùi sau',
                'type' => 'reps',
                'calories_per_unit' => 3.0,
                'is_global' => true,
            ],
            // Legs
            [
                'name' => 'Squat (Gánh tạ)',
                'description' => 'Bài tập chân cơ bản và quan trọng nhất.',
                'muscle_group' => 'Chân',
                'type' => 'reps',
                'calories_per_unit' => 1.5,
                'is_global' => true,
            ],
            [
                'name' => 'Chùng chân (Lunge)',
                'description' => 'Bài tập chân đơn (từng bên), cải thiện thăng bằng.',
                'muscle_group' => 'Chân',
                'type' => 'reps',
                'calories_per_unit' => 0.6,
                'is_global' => true,
            ],
            // Cardio
            [
                'name' => 'Chạy bộ',
                'description' => 'Bài tập tim mạch.',
                'muscle_group' => 'Cardio',
                'type' => 'time',
                'calories_per_unit' => 10.0, // ~10 kcal per minute
                'is_global' => true,
            ],
            [
                'name' => 'Plank',
                'description' => 'Bài tập giữ cố định cơ lõi (core).',
                'muscle_group' => 'Cơ lõi',
                'type' => 'time',
                'calories_per_unit' => 3.0, // ~3-4 kcal per minute
                'is_global' => true,
            ],
            [
                'name' => 'Bật nhảy (Jumping Jacks)',
                'description' => 'Bài tập khởi động toàn thân.',
                'muscle_group' => 'Cardio',
                'type' => 'time',
                'calories_per_unit' => 8.0,
                'is_global' => true,
            ],
        ];

        foreach ($exercises as $data) {
            $muscleGroup = $data['muscle_group'];
            unset($data['muscle_group']);

            // Append muscle group to description if valid
            $data['description'] = ($data['description'] ?? '') . " (Nhóm cơ: $muscleGroup)";

            Exercise::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
