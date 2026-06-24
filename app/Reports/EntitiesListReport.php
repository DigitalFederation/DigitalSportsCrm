<?php

namespace App\Reports;

use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Entities\States\PendingEntityFederationState;
use Domain\Entities\States\RejectedEntityFederationState;

class EntitiesListReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.entities_list');
    }

    public function query($filters)
    {
        return Entity::query()
            ->with([
                'country',
                'district',
                'zones',
                'entityFederations.federation',
            ]);
    }

    public function processData($data)
    {
        if (! $data instanceof \Illuminate\Support\Collection) {
            $data = collect($data);
        }

        return $data->map(function ($entity) {
            $na = __('reports.not_available');

            $mainFederation = $entity->entityFederations
                ->first(fn ($ef) => $ef->federation?->is_default_federation);

            $localFederations = $entity->entityFederations
                ->filter(fn ($ef) => (bool) $ef->federation?->is_local)
                ->map(fn ($ef) => $ef->federation->name)
                ->implode(', ');

            $modalidadeFederations = $entity->entityFederations
                ->filter(fn ($ef) => ! $ef->federation?->is_default_federation && ! $ef->federation?->is_local)
                ->map(fn ($ef) => $ef->federation->name)
                ->implode(', ');

            return [
                __('reports.columns.entity_name') => $entity->name ?? $na,
                __('reports.columns.legal_name') => $entity->legal_name ?? $na,
                __('reports.columns.member_number') => $entity->member_number ?? $na,
                __('reports.columns.member_id') => $entity->id,
                __('reports.columns.federation') => $mainFederation?->federation?->name ?? $na,
                __('reports.columns.territorial_association') => $localFederations ?: $na,
                __('reports.columns.sport_association') => $modalidadeFederations ?: $na,
                __('reports.columns.responsible_person') => $entity->legal_responsible_person ?? $na,
                __('reports.columns.vat_number') => $entity->vat_number ?? $na,
                __('reports.columns.country') => $entity->country?->name ?? $na,
                __('reports.columns.district') => $entity->district?->name ?? $na,
                __('reports.columns.zone') => $entity->zones->pluck('name')->implode(', ') ?: $na,
                __('reports.columns.address') => $entity->address ?? $na,
                __('reports.columns.locality') => $entity->location ?? $na,
                __('reports.columns.postal_code') => $entity->postal_code ?? $na,
                __('reports.columns.email') => $entity->email ?? $na,
                __('reports.columns.phone') => $entity->phone ?? $na,
                __('reports.columns.website') => $entity->website ?? $na,
                __('reports.columns.facebook') => $entity->facebook_url ?? $na,
                __('reports.columns.x') => $entity->x_url ?? $na,
                __('reports.columns.instagram') => $entity->instagram_url ?? $na,
                __('reports.columns.linkedin') => $entity->linkedin_url ?? $na,
                __('reports.columns.cmas_portal') => $entity->has_international_portal ? __('reports.yes') : __('reports.no'),
                __('reports.columns.affiliation_status') => $this->getAffiliationStatus($mainFederation),
            ];
        });
    }

    private function getAffiliationStatus($entityFederation): string
    {
        if (! $entityFederation || ! $entityFederation->status_class) {
            return __('reports.not_available');
        }

        return match ($entityFederation->status_class) {
            ActiveEntityFederationState::class => __('states.active'),
            PendingEntityFederationState::class => __('states.pending'),
            RejectedEntityFederationState::class => __('states.rejected'),
            default => __('reports.not_available'),
        };
    }

    public function columns(): array
    {
        return [
            __('reports.columns.entity_name'),
            __('reports.columns.legal_name'),
            __('reports.columns.member_number'),
            __('reports.columns.member_id'),
            __('reports.columns.federation'),
            __('reports.columns.territorial_association'),
            __('reports.columns.sport_association'),
            __('reports.columns.responsible_person'),
            __('reports.columns.vat_number'),
            __('reports.columns.country'),
            __('reports.columns.district'),
            __('reports.columns.zone'),
            __('reports.columns.address'),
            __('reports.columns.locality'),
            __('reports.columns.postal_code'),
            __('reports.columns.email'),
            __('reports.columns.phone'),
            __('reports.columns.website'),
            __('reports.columns.facebook'),
            __('reports.columns.x'),
            __('reports.columns.instagram'),
            __('reports.columns.linkedin'),
            __('reports.columns.cmas_portal'),
            __('reports.columns.affiliation_status'),
        ];
    }
}
