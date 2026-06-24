@if($licenses->count() > 0)
    <div class="card sm:flex flex-col">

        <header class="pb-2 border-b border-slate-100 flex justify-between">
            <h2 class="text-sm md:text-lg font-semibold text-slate-800">{{ $title }}</h2>
            <div class="text-secondary font-medium">
                <a href="{{ route('admin.license-attributed.index', [
                'filter[filter_member_code]' => $owner?->member_code
                ]) }}" class="text-xs py-0 btn-outline hover:btn-outline-hover">
                    <span>{{ __('View All') }}</span>
                </a>
            </div>
        </header>

        <div class="overflow-x-auto py-4 w-full">

            @if($licenses->count() > 0)
                <table class="w-full bg-transparent py-4" role="table">

                    <thead role="rowgroup">
                    <tr role="row" class="bg-slate-50 text-gray-400">
                        <th role="columnheader" class="text-xs md:text-base text-left py-0 md:p-1" aria-sort="none">
                            {{ __('Certification') }}
                        </th>
                        <th role="columnheader" class="text-xs md:text-base text-left py-0 md:p-1" aria-sort="none">
                            {{ __('Country') }}
                        </th>
                        <th role="columnheader" class="text-xs md:text-base text-left py-0 md:p-1" aria-sort="none">
                            {{ __('National Code') }}
                        </th>
                        <th role="columnheader" class="text-xs md:text-base text-center py-0 md:p-1" aria-sort="none">
                            {{ __('Status') }}
                        </th>
                        <th role="columnheader" class="text-xs md:text-base text-right py-0 md:p-1" aria-sort="none">
                            {{ __('Issue Date') }}
                        </th>
                        <th role="columnheader" class="text-xs md:text-base text-right py-0 md:p-1" aria-sort="none">
                            {{ __('Valid Until') }}
                        </th>
                    </tr>
                    </thead>

                    <tbody role="rowgroup">
                    @foreach($licenses as $license)
                        <tr role="row">
                            <td role="cell">
                            <span class="font-medium text-sm text-secondary whitespace-nowrap">
                                <a href="{{ route('admin.license-attributed.show', $license->id) }}"
                                   class="hover:underline flex justify-start items-center">

                                    <span>
                                    {{ empty($license->license_name)? $license->license->name : $license->license_name }}
                                    </span>

                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         strokeWidth={1.5} stroke="currentColor" class="w-4 h-4">
                                        <path strokeLinecap="round" strokeLinejoin="round"
                                              d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </a>
                            </span>
                            </td>
                            <td role="cell">
                            <span class="font-medium text-sm text-secondary whitespace-nowrap">
                                <div class="flex items-center">
                                  <img src="{{ asset('img/flags/' . strtolower($license->federation->country->iso) . '.svg') }}"
                                       class="w-4 h-4 mr-1" />
                                  {{ $license->federation->country->name }}
                                </div>
                            </span>
                            </td>
                            <td role="cell">
                            <span class="font-medium text-sm text-secondary whitespace-nowrap">
                                {{ empty($license->national_license_code)? '--' : $license->national_license_code }}
                            </span>
                            </td>
                            <td role="cell" class="text-center">
                            <span class="font-medium text-sm text-secondary whitespace-nowrap rounded-full text-white px-3
                                @switch($license->status_class)
                                    @case(\Domain\Licenses\States\PendingLicenseAttributedState::class)
                                        bg-orange-400
                                        @break
                                    @case(\Domain\Licenses\States\ActiveLicenseAttributedState::class)
                                        bg-emerald-500
                                        @break
                                    @case(\Domain\Licenses\States\CanceledLicenseAttributedState::class)
                                        bg-red-500
                                        @break
                                    @case(\Domain\Licenses\States\ExpiredLicenseAttributedState::class)
                                        bg-red-500
                                        @break
                                    @default
                                        bg-slate-600
                                @endswitch">
                                {{ ucfirst($license->stateName()) }}
                            </span>
                            </td>
                            <td role="cell" class="text-right">
                            <span class="font-medium text-sm text-secondary whitespace-nowrap text-right">
                                {{ !empty($license->current_term_start_at) ? date('d/m/Y', strtotime($license->current_term_start_at)) : "--" }}
                            </span>
                            </td>
                            <td role="cell" class="text-right">
                            <span class="font-medium text-sm text-secondary whitespace-nowrap text-right">
                                {{ !empty($license->current_term_ends_at) ? date('d/m/Y', strtotime($license->current_term_ends_at)) : "--" }}
                            </span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>


                </table>
            @else
                <div class="flex flex-col items-center justify-center h-64">
                    <div class="text-secondary font-medium">{{ __('No Licenses Available') }}</div>
                </div>
            @endif

        </div>

    </div>
@endif
