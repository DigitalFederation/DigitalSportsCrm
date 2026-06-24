<?php

namespace Domain\Licenses\Actions;

use Carbon\Carbon;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\PendingLicenseAttributedState;
use Illuminate\Support\Str;
use Support\UtilityMethods;

class CreateArrayFromBulkRequestAction
{
    public function __invoke(string $type, array $bulk_ids, int $federation_id, $request): array
    {

        // TODO: Make validations and checks
        $records = [];

        foreach ($bulk_ids as $id) {
            $holder_name = '';
            switch ($type) {
                case 'individual':
                    $individual = Individual::select('id', 'name', 'surname')->where(compact('id'))->first();
                    $holder_name = $individual->name.' '.$individual->surname;
                    break;
                case 'entity':
                    $entity = Entity::select('id', 'name')->where(compact('id'))->first();
                    $holder_name = $entity->name;
                    break;
            }

            $records[] = [
                'id' => Str::uuid()->toString(),
                'model_type' => $type === 'individual' ? 'individual' : 'entity',
                'model_id' => $id,
                'federation_id' => $federation_id,
                'status_class' => PendingLicenseAttributedState::class,
                'license_id' => $request->input('license_id'),
                'license_name' => License::select('id', 'name')->where('id', $request->input('license_id'))->value('name'),
                'federation_name' => Federation::where('id', $federation_id)->value('name'),
                'holder_name' => $holder_name,
                'notes' => $request->input('notes'),
                'current_term_starts_at' => $request->input('current_term_starts_at') ?? Carbon::today(),
                'current_term_ends_at' => Carbon::now()->lastOfMonth()->endOfDay(),
                'created_by' => auth()->user()->id,
                'updated_by' => auth()->user()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                // 'license_number' => UtilityMethods::generateLicenseCmasInternationalNumber(date('Y'), $license_code, $federation_code)
            ];
        }

        return $records;
    }
}
