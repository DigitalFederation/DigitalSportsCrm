<?php

namespace Database\Seeders;

use App\Models\Committee;
use Domain\Memberships\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $memberships = [
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Finswimming',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Freediving',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Aquathlon',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Underwater Hockey',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Underwater Rugby',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Target Shooting',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Sport Diving',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Spearfishing',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Orienteering',
            ],
            [
                'committee_id' => $this->getCommitteeId('SPORT'),
                'name' => 'Sport Membership Visual',
            ],
            [
                'committee_id' => $this->getCommitteeId('DIVING'),
                'name' => 'Technical Committee Membership',
            ],
            [
                'committee_id' => $this->getCommitteeId('DIVING'),
                'name' => 'Underwater Environmental Membership',
            ],
        ];

        foreach ($memberships as $membership) {
            MembershipPlan::create($membership);
        }
    }

    private function getCommitteeId($code): int
    {
        $license = Committee::where('code', 'LIKE', '%'.$code.'%')->first();

        return $license->id;
    }
}
