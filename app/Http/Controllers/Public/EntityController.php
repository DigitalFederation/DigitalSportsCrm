<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Contracts\View\View;

class EntityController extends Controller
{
    /**
     * Display the public profile of a specific entity.
     *
     * @param  Entity  $entity  The entity model instance resolved via route model binding.
     */
    public function show(Entity $entity): View
    {
        // Define instructor/dive leader role identifiers (UPDATE THESE WITH ACTUAL VALUES if different)
        // $instructorRoleIdentifiers = ['INSTRUCTOR', 'LEADER']; // Using the 'role' field from ProfessionalRole // Removed

        // Eager load necessary relationships for efficiency
        $entity->load([
            'country:id,name,iso',
            'licenses' => function ($query) {
                $query->where('status_class', ActiveLicenseAttributedState::class)
                    ->with([
                        'license:id,name,committee_id',
                        'license.committee:id,code',
                    ]);
            },
            // Load all active professional roles for the entity
            'entityProfessionals' => function ($query) {
                $query->where('status_class', ActiveEntityProfessionalRoleState::class)
                    ->with([
                        'individual' => function ($indQuery) {
                            $indQuery->select('id', 'name', 'surname')
                                ->with('media');
                        },
                        'professionalRole:id,name,role,committee_id',
                        'professionalRole.committee:id,code',
                        'sport:id,name',
                    ]);
            },
            'media',
            'media.model',
        ]);

        // 1. Entity Data (already loaded)
        $entityData = $entity;
        // Get logo URL (using 'profile' collection like in the dashboard)
        $logoUrl = $entity->getFirstMediaUrl('profile', 'thumb') ?: null;
        // Get background image URL (using 'entity-background' collection)
        $backgroundUrl = $entity->getFirstMediaUrl('entity-background') ?: null;

        // 2. Active Licenses - Split by committee
        $activeLicenses = $entity->licenses;

        // Sport Licenses (committee = 'SPORT')
        $sportLicenses = $activeLicenses->filter(function ($attributedLicense) {
            return $attributedLicense->license?->committee?->code === 'SPORT';
        });

        // Diving Licenses (committee = 'DIVINGSERVICES')
        $divingLicenses = $activeLicenses->filter(function ($attributedLicense) {
            return $attributedLicense->license?->committee?->code === 'DIVINGSERVICES';
        });

        // 3. Active Coaches (role = 'COACH')
        $coaches = $entity->entityProfessionals
            ->filter(fn ($ep) => $ep->professionalRole?->role === 'COACH')
            ->map(function ($entityRole) {
                $individual = $entityRole->individual;
                if (! $individual) {
                    return null;
                }

                $avatarUrl = $individual->getFirstMediaUrl('profile', 'thumb') ?:
                    $individual->getFirstMediaUrl('avatars', 'thumb') ?:
                    null;

                return [
                    'id' => $individual->id,
                    'name' => $individual->name . ' ' . $individual->surname,
                    'sport' => $entityRole->sport?->translatedName,
                    'avatar_url' => $avatarUrl,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        // 4. Diving Professionals (role = 'DIVINGPROFESSIONAL' or 'INSTRUCTOR' or 'LEADER')
        $divingProfessionals = $entity->entityProfessionals
            ->filter(function ($ep) {
                $role = $ep->professionalRole?->role;
                $committeeCode = $ep->professionalRole?->committee?->code;

                // Diving professionals from DIVINGSERVICES committee
                return in_array($role, ['DIVINGPROFESSIONAL', 'INSTRUCTOR', 'LEADER'])
                    && $committeeCode === 'DIVINGSERVICES';
            })
            ->map(function ($entityRole) {
                $individual = $entityRole->individual;
                if (! $individual) {
                    return null;
                }

                $avatarUrl = $individual->getFirstMediaUrl('profile', 'thumb') ?:
                    $individual->getFirstMediaUrl('avatars', 'thumb') ?:
                    null;

                return [
                    'id' => $individual->id,
                    'name' => $individual->name . ' ' . $individual->surname,
                    'role' => $entityRole->professionalRole?->name,
                    'avatar_url' => $avatarUrl,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        // 5. Section visibility flags (based on content, not entity committee)
        $hasSportContent = $sportLicenses->isNotEmpty() || $coaches->isNotEmpty();
        $hasDivingContent = $divingLicenses->isNotEmpty()
            || $divingProfessionals->isNotEmpty();

        return view('public.entities.show', [
            'entity' => $entityData,
            'logoUrl' => $logoUrl,
            'backgroundUrl' => $backgroundUrl,
            'sportLicenses' => $sportLicenses,
            'divingLicenses' => $divingLicenses,
            'coaches' => $coaches,
            'divingProfessionals' => $divingProfessionals,
            'hasSportContent' => $hasSportContent,
            'hasDivingContent' => $hasDivingContent,
            'title' => $entityData->name . ' - ' . __('Entity Profile'),
        ])->layout('layouts.guest');
    }
}
