<?php

namespace App\Reports;

use Domain\Memberships\Models\Affiliation;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Memberships\States\ExpiredAffiliationState;
use Domain\Memberships\States\InactiveAffiliationState;
use Domain\Memberships\States\PendingPaymentAffiliationState;
use Domain\Memberships\States\SuspendedAffiliationState;

class AffiliationsListReport implements ReportTemplate
{
    public static function getDisplayName(): string
    {
        return __('reports.affiliations_list');
    }

    public function query($filters)
    {
        $query = Affiliation::query()
            ->select([
                'affiliations.id',
                'affiliations.member_type',
                'affiliations.member_id',
                'affiliations.federation_id',
                'affiliations.member_subscription_id',
                'affiliations.start_date',
                'affiliations.end_date',
                'affiliations.individual_fee',
                'affiliations.entity_fee',
                'affiliations.status_class',
                'affiliations.requester_type',
                'affiliations.requester_id',
                'affiliations.created_at',
            ])
            ->with([
                'member',
                'federation',
                'memberSubscription.membershipPackage',
                'requester',
            ]);

        // Apply date filters based on created_at (activation date)
        if (! empty($filters['start_date'])) {
            $query->whereDate('affiliations.created_at', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('affiliations.created_at', '<=', $filters['end_date']);
        }

        return $query;
    }

    public function processData($data)
    {
        if (! $data instanceof \Illuminate\Support\Collection) {
            $data = collect($data);
        }

        return $data->map(function ($affiliation) {
            // Get member name based on type
            $memberName = $this->getMemberName($affiliation);
            $memberType = $this->getMemberType($affiliation->member_type);

            // Get membership package name
            $planName = $affiliation->memberSubscription?->membershipPackage?->name ?? 'N/A';

            // Get territorial association (federation name if is_local)
            $territorialAssociation = $this->getTerritorialAssociation($affiliation);

            // Get activation date (created_at or paid_at from document)
            $activationDate = $this->formatDate($affiliation->created_at);

            // Get requester name
            $requesterName = $this->getRequesterName($affiliation);

            // Calculate fee
            $fee = $this->getFee($affiliation);

            return [
                'Membro' => $memberName,
                'Tipo' => $memberType,
                'Nome do Plano de Filiação' => $planName,
                'Associação Territorial' => $territorialAssociation,
                'Data de Ativação' => $activationDate,
                'Data de Início' => $this->formatDate($affiliation->start_date, 'd/m/Y'),
                'Data de Fim' => $this->formatDate($affiliation->end_date, 'd/m/Y'),
                'Taxa' => $fee,
                'Solicitado por' => $requesterName,
                'Estado' => $this->getStatusName($affiliation->status_class),
            ];
        });
    }

    private function getMemberName($affiliation): string
    {
        $member = $affiliation->member;

        if (! $member) {
            return 'N/A';
        }

        // Check if it's an individual (has native_name) or entity (has name)
        if ($affiliation->member_type === 'Domain\Individuals\Models\Individual' || $affiliation->member_type === 'individual') {
            return $member->native_name ?? ($member->name . ' ' . ($member->surname ?? ''));
        }

        // Entity
        return $member->name ?? 'N/A';
    }

    private function getMemberType(string $memberType): string
    {
        if (str_contains($memberType, 'Individual') || $memberType === 'individual') {
            return app()->getLocale() == 'pt' ? 'Individual' : 'Individual';
        }

        return app()->getLocale() == 'pt' ? 'Entidade' : 'Entity';
    }

    private function getTerritorialAssociation($affiliation): string
    {
        $federation = $affiliation->federation;

        if (! $federation) {
            return 'N/A';
        }

        // If federation is_local, return its name as territorial association
        if ($federation->is_local) {
            return $federation->name;
        }

        return $federation->name ?? 'N/A';
    }

    private function getRequesterName($affiliation): string
    {
        $requester = $affiliation->requester;

        if (! $requester) {
            return 'N/A';
        }

        // Check requester type
        if (str_contains($affiliation->requester_type ?? '', 'Individual') || $affiliation->requester_type === 'individual') {
            // If the requester is the same individual as the member, show the primary federation.
            if ($affiliation->member_id === $affiliation->requester_id &&
                (str_contains($affiliation->member_type ?? '', 'Individual') || $affiliation->member_type === 'individual')) {
                return config('branding.primary.short_name', 'DF');
            }

            return $requester->native_name ?? ($requester->name . ' ' . ($requester->surname ?? ''));
        }

        if (str_contains($affiliation->requester_type ?? '', 'Entity') || $affiliation->requester_type === 'entity') {
            return $requester->name ?? 'N/A';
        }

        if (str_contains($affiliation->requester_type ?? '', 'User')) {
            return $requester->name ?? $requester->email ?? 'N/A';
        }

        return $requester->name ?? 'N/A';
    }

    private function getFee($affiliation): string
    {
        $fee = 0;

        if ($affiliation->individual_fee) {
            $fee = $affiliation->individual_fee;
        } elseif ($affiliation->entity_fee) {
            $fee = $affiliation->entity_fee;
        }

        return number_format($fee, 2, ',', '.') . ' EUR';
    }

    private function getStatusName(?string $statusClass): string
    {
        if (! $statusClass) {
            return 'N/A';
        }

        $isPortuguese = app()->getLocale() == 'pt';

        return match ($statusClass) {
            ActiveAffiliationState::class, 'Domain\Affiliations\States\ActiveAffiliationState' => $isPortuguese ? 'Ativo' : 'Active',
            ExpiredAffiliationState::class, 'Domain\Affiliations\States\ExpiredAffiliationState' => $isPortuguese ? 'Expirado' : 'Expired',
            InactiveAffiliationState::class, 'Domain\Affiliations\States\InactiveAffiliationState' => $isPortuguese ? 'Inativo' : 'Inactive',
            PendingPaymentAffiliationState::class, 'Domain\Affiliations\States\PendingPaymentAffiliationState' => $isPortuguese ? 'Pagamento Pendente' : 'Pending Payment',
            SuspendedAffiliationState::class, 'Domain\Affiliations\States\SuspendedAffiliationState' => $isPortuguese ? 'Suspenso' : 'Suspended',
            default => class_basename($statusClass)
        };
    }

    private function formatDate($date, $format = 'd/m/Y H:i'): string
    {
        if (! $date) {
            return 'N/A';
        }

        if ($date instanceof \Carbon\Carbon) {
            return $date->format($format);
        }

        return \Carbon\Carbon::parse($date)->format($format);
    }

    public function columns(): array
    {
        return [
            'Membro',
            'Tipo',
            'Nome do Plano de Filiação',
            'Associação Territorial',
            'Data de Ativação',
            'Data de Início',
            'Data de Fim',
            'Taxa',
            'Solicitado por',
            'Estado',
        ];
    }
}
