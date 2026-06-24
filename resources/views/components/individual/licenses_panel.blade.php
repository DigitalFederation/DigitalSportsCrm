@if(!empty($licenses) && $licenses->count() > 0)

    <section class="mt-2 bg-white rounded-md shadow hover:shadow-xl">
        <div class="mx-5 mt-4 mb-2 flex justify-between">
            <h2 class="font-semibold text-slate-600 text-lg">{{ $title }}</h2>
            <div class="text-secondary text-sm">
                <a href="{{ route(Request()->segment(1).'.license-attributed.index', [
                'filter[filter_member_code]' => $individual->member_code,
                'filter[filter_holder_type]' => 'individual',
                'filter[committee]' => !empty($committee) ? $committee : null,
                ]) }}" class="text-sm py-0 btn-outline btn-sm hover:btn-outline-hover">
                    <span>{{ __('main.view_all') }}</span>
                </a>
            </div>
        </div>

        <x-dynamic-table
            :headers="[__('main.license'), __('main.expiration_date'), ['text'=>__('main.status'),'alignment'=>'text-right'], '']">
            @foreach($licenses as $license)
                <tr role="row">
                    <td class="pl-5 py-2 whitespace-nowrap text-left">{{ $license->license_name }}</td>

                    <td class="px-2 py-2 whitespace-nowrap text-left">
                        {{ !empty($license->current_term_ends_at) ? date('d/m/Y', strtotime($license->current_term_ends_at)) : "--" }}
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-right">
                        <x-tables.badge :status="ucfirst($license->stateName())" :color="$license->stateColor()"/>
                    </td>
                    <td class="pr-5 py-2 whitespace-nowrap text-right">
                        @if(Request::segment(1) != 'individual' && Request::segment(1) != 'entity')
                            <a href="{{ route(Request::segment(1).'.license-attributed.show', $license->id) }}"
                               class="text-sm btn btn-xs btn-info hover:btn-outline-hover">
                                <span>{{ __('main.detail') }}</span>
                            </a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>
    </section>

@endif
