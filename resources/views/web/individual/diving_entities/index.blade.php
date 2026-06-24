@section('title', __('menu.individual.diving_entities'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('menu.individual.diving_entities') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('diving.diving_entities_info') }}</p>
            </div>
        </div>

        @if($pendingInvitations->count() > 0)
            <div class="card-no-padding">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-snug text-slate-800 font-bold">{{ __('diving.pending_entity_invitations') }}</h3>
                </div>
                <div class="overflow-x-auto border-t border-slate-200">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.name') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.type') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('common.requested_at') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">{{ __('common.actions') }}</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($pendingInvitations as $invitation)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $invitation->entity->name }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $invitation->entity->type }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $invitation->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <form action="{{ route('individual.diving_entities.approve') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $invitation->entity_id }}">
                                                <button type="submit" class="btn btn-success btn-sm">{{ __('common.accept') }}</button>
                                            </form>
                                            <form action="{{ route('individual.diving_entities.reject') }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $invitation->entity_id }}">
                                                <button type="submit" class="btn btn-danger btn-sm">{{ __('common.reject') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($activeAssociations->count() > 0)
            <div class="card-no-padding mt-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-snug text-slate-800 font-bold">{{ __('diving.my_diving_entities') }}</h3>
                </div>
                <div class="overflow-x-auto border-t border-slate-200">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.name') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.type') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.license_number') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('common.joined_at') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">{{ __('common.actions') }}</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($activeAssociations as $association)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $association->entity->name }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $association->entity->type }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @foreach($association->entity->licenses()->whereHas('license', function($q) { $q->where('committee_id', 3); })->with('license')->get() as $license)
                                            {{ $license->license->code ?? '' }}<br>
                                        @endforeach
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $association->updated_at->format('d/m/Y') }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <a href="{{ route('individual.diving_entities.show', $association->entity) }}" class="btn btn-secondary btn-sm">{{ __('common.view') }}</a>
                                            <form action="{{ route('individual.diving_entities.destroy', $association->entity) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">{{ __('common.leave') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($availableEntities->count() > 0)
            <div class="card-no-padding mt-6">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-snug text-slate-800 font-bold">{{ __('diving.available_diving_entities') }}</h3>
                </div>
                <div class="overflow-x-auto border-t border-slate-200">
                    <table class="table-auto w-full divide-y divide-slate-200">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.name') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('entities.type') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">{{ __('common.location') }}</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-right">{{ __('common.actions') }}</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @foreach($availableEntities as $entity)
                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $entity->name }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $entity->type }}</td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        {{ $entity->country->name ?? '' }}
                                        @if($entity->country->subRegion)
                                            , {{ $entity->country->subRegion->name }}
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex justify-end items-end">
                                            <a href="{{ route('individual.diving_entities.show', $entity) }}" class="btn btn-secondary btn-sm">{{ __('common.view') }}</a>
                                            <form action="{{ route('individual.diving_entities.store') }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                                <button type="submit" class="btn btn-primary btn-sm">{{ __('diving.request_to_join') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layout>
