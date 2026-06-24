<?php

namespace Domain\Memberships\Services;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Facades\DB;

class MemberNumberService
{
    /**
     * Assign a member number to an individual
     */
    public function assignIndividualMemberNumber(Individual $individual): void
    {
        if ($individual->member_number !== null) {
            return;
        }

        DB::transaction(function () use ($individual) {
            // Lock the row to prevent concurrent access
            $setting = DB::table('member_number_settings')
                ->where('key', 'individual_counter')
                ->lockForUpdate()
                ->first();

            if (! $setting) {
                throw new \RuntimeException('Individual counter setting not found');
            }

            $memberNumber = (int) $setting->value;

            // Skip numbers that were manually assigned by an admin
            while (Individual::withTrashed()->where('member_number', $memberNumber)->exists()) {
                $memberNumber++;
            }

            // Assign the member number to the individual
            $individual->update(['member_number' => $memberNumber]);

            // Update the counter to the next value after the assigned number
            DB::table('member_number_settings')
                ->where('key', 'individual_counter')
                ->update(['value' => $memberNumber + 1, 'updated_at' => now()]);
        });
    }

    /**
     * Assign a member number to an entity
     */
    public function assignEntityMemberNumber(Entity $entity): void
    {
        if ($entity->member_number !== null) {
            return;
        }

        DB::transaction(function () use ($entity) {
            // Lock the row to prevent concurrent access
            $setting = DB::table('member_number_settings')
                ->where('key', 'entity_counter')
                ->lockForUpdate()
                ->first();

            if (! $setting) {
                throw new \RuntimeException('Entity counter setting not found');
            }

            $memberNumber = (int) $setting->value;

            // Skip numbers that were manually assigned by an admin
            while (Entity::withTrashed()->where('member_number', $memberNumber)->exists()) {
                $memberNumber++;
            }

            // Assign the member number to the entity
            $entity->update(['member_number' => $memberNumber]);

            // Update the counter to the next value after the assigned number
            DB::table('member_number_settings')
                ->where('key', 'entity_counter')
                ->update(['value' => $memberNumber + 1, 'updated_at' => now()]);
        });
    }

    /**
     * Get current counter value for individuals
     */
    public function getCurrentIndividualCounter(): int
    {
        $setting = DB::table('member_number_settings')
            ->where('key', 'individual_counter')
            ->first();

        return $setting ? (int) $setting->value : 1;
    }

    /**
     * Get current counter value for entities
     */
    public function getCurrentEntityCounter(): int
    {
        $setting = DB::table('member_number_settings')
            ->where('key', 'entity_counter')
            ->first();

        return $setting ? (int) $setting->value : 1;
    }

    /**
     * Update counter value for individuals
     */
    public function updateIndividualCounter(int $value): void
    {
        DB::table('member_number_settings')
            ->where('key', 'individual_counter')
            ->update(['value' => $value, 'updated_at' => now()]);
    }

    /**
     * Update counter value for entities
     */
    public function updateEntityCounter(int $value): void
    {
        DB::table('member_number_settings')
            ->where('key', 'entity_counter')
            ->update(['value' => $value, 'updated_at' => now()]);
    }
}
