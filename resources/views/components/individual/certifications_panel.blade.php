
<section class="mt-2 bg-white rounded-md shadow hover:shadow-xl">
    <div class="mx-5 mt-4 mb-2 flex justify-between">
        <h2 class="font-semibold text-slate-600 text-lg">{{ $title }}</h2>
        <div class="text-secondary font-medium">
            <a href="{{ route(Request::segment(1).'.certification-attributed.index', [
                'filter[filter_member_code]' => $individual->member_code,
                'filter[committee]' => $committee
                ]) }}" class="text-xs py-0 btn-outline btn-sm hover:btn-outline-hover">
                <span>{{ __('main.view_all') }}</span>
            </a>
        </div>
    </div>

    @if(!empty($certifications) && $certifications->count() > 0)
    <x-dynamic-table
        :headers="[__('main.certification'), __('main.expiration_date'), ['text'=>__('main.status'),'alignment'=>'text-right'], '']">
        @foreach($certifications as $certification)
            <tr role="row">
                <td class="pl-5 py-2 whitespace-nowrap text-left">

                    <a href="{{ route(Request::segment(1).'.certification-attributed.show', $certification->id) }}" class="hover:underline flex justify-start items-center">
                        <span>
                        {{ empty($certification->certification_name)? $certification->certification->name : $certification->certification_name }}
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" class="w-4 h-4">
                            <path strokeLinecap="round" strokeLinejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </td>

                <td class="px-2 py-2 whitespace-nowrap text-left">
                    {{ !empty($certification->current_term_ends_at) ? date('d/m/Y', strtotime($certification->current_term_ends_at)) : "--" }}
                </td>
                <td class="px-2 py-2 whitespace-nowrap text-right">
                    <x-tables.badge :status="ucfirst($certification->stateName())" :color="$certification->stateColor()"/>
                </td>
                <td class="pr-5 py-2 whitespace-nowrap text-right">
                    @if(Request::segment(1) != 'individual' && Request::segment(1) != 'entity')
                        <a href="{{ route(Request::segment(1).'.certification-attributed.show', $certification->id) }}"
                           class="text-sm btn btn-xs btn-info hover:btn-outline-hover">
                            <span>{{ __('main.detail') }}</span>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </x-dynamic-table>
    @else
        <div class="flex flex-col items-center justify-center h-24 md:h-32">
            <div class="text-secondary font-medium">{{ __('main.no_certifications_available') }}</div>
        </div>
    @endif

</section>

