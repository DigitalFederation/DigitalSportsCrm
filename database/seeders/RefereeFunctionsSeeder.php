<?php

namespace Database\Seeders;

use Domain\EvtEvents\Models\RefereeFunction;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Database\Seeder;

class RefereeFunctionsSeeder extends Seeder
{
    /**
     * Referee functions by sport.
     * Each sport has specific official functions required for competitions.
     *
     * @var array<string, array<int, array{name: string, code: string, description: string|null}>>
     */
    protected array $functionsBySport = [
        'Finswimming' => [
            ['name' => 'Chief Referee', 'code' => 'CHIEF_REF', 'description' => 'Overall event authority'],
            ['name' => 'Starter', 'code' => 'STARTER', 'description' => 'Controls race starts'],
            ['name' => 'Chief Timekeeper', 'code' => 'CHIEF_TIME', 'description' => 'Supervises all timekeepers'],
            ['name' => 'Timekeeper', 'code' => 'TIMEKEEPER', 'description' => 'Manual timing'],
            ['name' => 'Turn Judge', 'code' => 'TURN_JUDGE', 'description' => 'Monitors turns at pool ends'],
            ['name' => 'Stroke Judge', 'code' => 'STROKE_JUDGE', 'description' => 'Verifies swimming technique'],
            ['name' => 'Finish Judge', 'code' => 'FINISH_JUDGE', 'description' => 'Determines finish order'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
            ['name' => 'Announcer', 'code' => 'ANNOUNCER', 'description' => 'Public announcements'],
        ],
        'Freediving' => [
            ['name' => 'Chief Judge', 'code' => 'CHIEF_JUDGE', 'description' => 'Overall event authority'],
            ['name' => 'Surface Judge', 'code' => 'SURFACE_JUDGE', 'description' => 'Monitors surface protocol'],
            ['name' => 'Deep Judge', 'code' => 'DEEP_JUDGE', 'description' => 'Depth verification'],
            ['name' => 'Medical Officer', 'code' => 'MEDICAL', 'description' => 'Medical supervision'],
            ['name' => 'Safety Diver', 'code' => 'SAFETY_DIVER', 'description' => 'Underwater safety'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
            ['name' => 'Announcer', 'code' => 'ANNOUNCER', 'description' => 'Public announcements'],
        ],
        'Aquathlon' => [
            ['name' => 'Chief Referee', 'code' => 'CHIEF_REF', 'description' => 'Overall event authority'],
            ['name' => 'Water Referee', 'code' => 'WATER_REF', 'description' => 'Monitors underwater wrestling'],
            ['name' => 'Timekeeper', 'code' => 'TIMEKEEPER', 'description' => 'Match timing'],
            ['name' => 'Table Judge', 'code' => 'TABLE_JUDGE', 'description' => 'Score keeping'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties'],
        ],
        'Underwater Hockey' => [
            ['name' => 'Chief Referee', 'code' => 'CHIEF_REF', 'description' => 'Overall event authority'],
            ['name' => 'Water Referee', 'code' => 'WATER_REF', 'description' => 'In-water officiating'],
            ['name' => 'Deck Referee', 'code' => 'DECK_REF', 'description' => 'Pool deck officiating'],
            ['name' => 'Timekeeper', 'code' => 'TIMEKEEPER', 'description' => 'Match timing'],
            ['name' => 'Goal Judge', 'code' => 'GOAL_JUDGE', 'description' => 'Goal verification'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Score keeping and records'],
        ],
        'Underwater Rugby' => [
            ['name' => 'Chief Referee', 'code' => 'CHIEF_REF', 'description' => 'Overall event authority'],
            ['name' => 'Water Referee', 'code' => 'WATER_REF', 'description' => 'In-water officiating'],
            ['name' => 'Deck Referee', 'code' => 'DECK_REF', 'description' => 'Pool deck officiating'],
            ['name' => 'Goal Referee', 'code' => 'GOAL_REF', 'description' => 'Goal verification'],
            ['name' => 'Timekeeper', 'code' => 'TIMEKEEPER', 'description' => 'Match timing'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Score keeping and records'],
        ],
        'Target Shooting' => [
            ['name' => 'Chief Judge', 'code' => 'CHIEF_JUDGE', 'description' => 'Overall event authority'],
            ['name' => 'Range Officer', 'code' => 'RANGE_OFFICER', 'description' => 'Range safety and control'],
            ['name' => 'Target Judge', 'code' => 'TARGET_JUDGE', 'description' => 'Target scoring'],
            ['name' => 'Safety Officer', 'code' => 'SAFETY', 'description' => 'Safety supervision'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
        ],
        'Sport Diving' => [
            ['name' => 'Chief Judge', 'code' => 'CHIEF_JUDGE', 'description' => 'Overall event authority'],
            ['name' => 'Course Judge', 'code' => 'COURSE_JUDGE', 'description' => 'Course monitoring'],
            ['name' => 'Task Judge', 'code' => 'TASK_JUDGE', 'description' => 'Task verification'],
            ['name' => 'Safety Officer', 'code' => 'SAFETY', 'description' => 'Diving safety supervision'],
            ['name' => 'Timekeeper', 'code' => 'TIMEKEEPER', 'description' => 'Timing'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
        ],
        'Spearfishing' => [
            ['name' => 'Chief Judge', 'code' => 'CHIEF_JUDGE', 'description' => 'Overall event authority'],
            ['name' => 'Weighing Judge', 'code' => 'WEIGH_JUDGE', 'description' => 'Fish weighing and verification'],
            ['name' => 'Safety Officer', 'code' => 'SAFETY', 'description' => 'Safety supervision'],
            ['name' => 'Boat Marshal', 'code' => 'BOAT_MARSHAL', 'description' => 'Boat coordination'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
        ],
        'Orienteering' => [
            ['name' => 'Chief Judge', 'code' => 'CHIEF_JUDGE', 'description' => 'Overall event authority'],
            ['name' => 'Course Judge', 'code' => 'COURSE_JUDGE', 'description' => 'Course monitoring'],
            ['name' => 'Checkpoint Judge', 'code' => 'CHECKPOINT', 'description' => 'Checkpoint verification'],
            ['name' => 'Safety Officer', 'code' => 'SAFETY', 'description' => 'Safety supervision'],
            ['name' => 'Timekeeper', 'code' => 'TIMEKEEPER', 'description' => 'Timing'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
        ],
        'Visual' => [
            ['name' => 'Chief Judge', 'code' => 'CHIEF_JUDGE', 'description' => 'Overall event authority'],
            ['name' => 'Technical Judge', 'code' => 'TECH_JUDGE', 'description' => 'Technical evaluation'],
            ['name' => 'Artistic Judge', 'code' => 'ART_JUDGE', 'description' => 'Artistic evaluation'],
            ['name' => 'Safety Officer', 'code' => 'SAFETY', 'description' => 'Safety supervision'],
            ['name' => 'Secretary', 'code' => 'SECRETARY', 'description' => 'Administrative duties and records'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->functionsBySport as $sportName => $functions) {
            $sport = Sport::where('name', $sportName)->first();

            if (! $sport) {
                $this->command->warn("Sport not found: {$sportName}");

                continue;
            }

            foreach ($functions as $order => $functionData) {
                RefereeFunction::updateOrCreate(
                    [
                        'sport_id' => $sport->id,
                        'function_code' => $functionData['code'],
                    ],
                    [
                        'function_name' => $functionData['name'],
                        'description' => $functionData['description'],
                        'display_order' => $order + 1,
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info("Created referee functions for: {$sportName}");
        }
    }
}
