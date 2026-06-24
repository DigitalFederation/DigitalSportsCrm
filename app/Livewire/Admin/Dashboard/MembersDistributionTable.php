<?php

namespace App\Livewire\Admin\Dashboard;

use App\Scopes\IndividualsFromFederationScope;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
use Domain\Memberships\States\ActiveAffiliationState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MembersDistributionTable extends Component
{
    public function render()
    {
        $federation = Federation::where('is_default_federation', true)->first();
        $federationId = $federation->id ?? null;

        $districts = collect();
        $distribution = [];
        $totals = $this->getEmptyRow();

        if ($federationId) {
            $cacheKey = "admin_members_distribution_{$federationId}_" . now()->format('Y-m-d');
            $ttl = 3600; // 1 hour

            $data = Cache::remember($cacheKey, $ttl, function () use ($federationId) {
                return $this->calculateDistribution($federationId);
            });

            $districts = $data['districts'];
            $distribution = $data['distribution'];
            $totals = $data['totals'];
        }

        return view('livewire.federation.dashboard.members-distribution-table', [
            'districts' => $districts,
            'distribution' => $distribution,
            'totals' => $totals,
            'ageGroups' => $this->getAgeGroups(),
        ]);
    }

    private function calculateDistribution(int $federationId): array
    {
        $today = Carbon::now();

        $age13 = $today->copy()->subYears(13);
        $age18 = $today->copy()->subYears(18);
        $age46 = $today->copy()->subYears(46);

        $districts = District::query()
            ->whereHas('individuals', function ($query) {
                $query->withoutGlobalScope(IndividualsFromFederationScope::class);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $distribution = [];
        $totals = $this->getEmptyRow();

        foreach ($districts as $district) {
            $row = $this->getEmptyRow();

            $registered = $this->getRegisteredCounts($district->id, $age13, $age18, $age46);
            $affiliated = $this->getAffiliatedCounts($federationId, $district->id, $age13, $age18, $age46);

            foreach ($registered as $key => $count) {
                $row[$key]['registered'] = $count;
                $totals[$key]['registered'] += $count;
            }

            foreach ($affiliated as $key => $count) {
                $row[$key]['affiliated'] = $count;
                $totals[$key]['affiliated'] += $count;
            }

            $distribution[$district->id] = $row;
        }

        return [
            'districts' => $districts,
            'distribution' => $distribution,
            'totals' => $totals,
        ];
    }

    private function getRegisteredCounts(int $districtId, $age13, $age18, $age46): array
    {
        $results = Individual::query()
            ->withoutGlobalScope(IndividualsFromFederationScope::class)
            ->select('gender')
            ->selectRaw('
                SUM(CASE WHEN birthdate > ? THEN 1 ELSE 0 END) as up_to_12,
                SUM(CASE WHEN birthdate <= ? AND birthdate > ? THEN 1 ELSE 0 END) as age_13_17,
                SUM(CASE WHEN birthdate <= ? AND birthdate > ? THEN 1 ELSE 0 END) as age_18_45,
                SUM(CASE WHEN birthdate <= ? THEN 1 ELSE 0 END) as age_46_plus
            ', [$age13, $age13, $age18, $age18, $age46, $age46])
            ->where('district_id', $districtId)
            ->whereNotNull('birthdate')
            ->whereNotNull('gender')
            ->groupBy('gender')
            ->get();

        return $this->mapResultsToAgeGroups($results);
    }

    private function getAffiliatedCounts(int $federationId, int $districtId, $age13, $age18, $age46): array
    {
        $results = Individual::query()
            ->withoutGlobalScope(IndividualsFromFederationScope::class)
            ->select('gender')
            ->selectRaw('
                SUM(CASE WHEN birthdate > ? THEN 1 ELSE 0 END) as up_to_12,
                SUM(CASE WHEN birthdate <= ? AND birthdate > ? THEN 1 ELSE 0 END) as age_13_17,
                SUM(CASE WHEN birthdate <= ? AND birthdate > ? THEN 1 ELSE 0 END) as age_18_45,
                SUM(CASE WHEN birthdate <= ? THEN 1 ELSE 0 END) as age_46_plus
            ', [$age13, $age13, $age18, $age18, $age46, $age46])
            ->where('district_id', $districtId)
            ->whereNotNull('birthdate')
            ->whereNotNull('gender')
            ->whereHas('affiliations', function ($q) use ($federationId) {
                $q->where('federation_id', $federationId)
                    ->where('status_class', ActiveAffiliationState::class)
                    ->where('end_date', '>=', now());
            })
            ->groupBy('gender')
            ->get();

        return $this->mapResultsToAgeGroups($results);
    }

    private function mapResultsToAgeGroups($results): array
    {
        $mapped = [
            'female_up_to_12' => 0,
            'male_up_to_12' => 0,
            'female_13_to_17' => 0,
            'male_13_to_17' => 0,
            'female_18_to_45' => 0,
            'male_18_to_45' => 0,
            'female_46_plus' => 0,
            'male_46_plus' => 0,
        ];

        foreach ($results as $row) {
            $gender = strtolower($row->gender);
            $genderPrefix = in_array($gender, ['f', 'female']) ? 'female' : 'male';

            $mapped["{$genderPrefix}_up_to_12"] = (int) $row->up_to_12;
            $mapped["{$genderPrefix}_13_to_17"] = (int) $row->age_13_17;
            $mapped["{$genderPrefix}_18_to_45"] = (int) $row->age_18_45;
            $mapped["{$genderPrefix}_46_plus"] = (int) $row->age_46_plus;
        }

        return $mapped;
    }

    private function getEmptyRow(): array
    {
        return [
            'female_up_to_12' => ['registered' => 0, 'affiliated' => 0],
            'male_up_to_12' => ['registered' => 0, 'affiliated' => 0],
            'female_13_to_17' => ['registered' => 0, 'affiliated' => 0],
            'male_13_to_17' => ['registered' => 0, 'affiliated' => 0],
            'female_18_to_45' => ['registered' => 0, 'affiliated' => 0],
            'male_18_to_45' => ['registered' => 0, 'affiliated' => 0],
            'female_46_plus' => ['registered' => 0, 'affiliated' => 0],
            'male_46_plus' => ['registered' => 0, 'affiliated' => 0],
        ];
    }

    private function getAgeGroups(): array
    {
        return [
            'female_up_to_12' => __('dashboard.female_up_to_12'),
            'male_up_to_12' => __('dashboard.male_up_to_12'),
            'female_13_to_17' => __('dashboard.female_13_to_17'),
            'male_13_to_17' => __('dashboard.male_13_to_17'),
            'female_18_to_45' => __('dashboard.female_18_to_45'),
            'male_18_to_45' => __('dashboard.male_18_to_45'),
            'female_46_plus' => __('dashboard.female_46_plus'),
            'male_46_plus' => __('dashboard.male_46_plus'),
        ];
    }
}
