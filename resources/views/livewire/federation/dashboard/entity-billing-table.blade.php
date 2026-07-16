<div class="overflow-x-auto">
    <!-- Explanatory text -->
    <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-100 dark:border-blue-800">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            <p class="text-xs sm:text-sm text-blue-700 dark:text-blue-300">
                {{ __('dashboard.entity_billing_explanation') }}
            </p>
        </div>
    </div>

    @if($entities->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th scope="col" class="px-3 sm:px-4 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider w-12 sm:w-16">
                            #
                        </th>
                        <th scope="col" class="px-3 sm:px-4 py-3 text-center text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider w-14 sm:w-16">
                            {{ __('dashboard.logo') }}
                        </th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ __('dashboard.entity_name') }}
                        </th>
                        <th scope="col" class="hidden sm:table-cell px-4 sm:px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ __('dashboard.district') }}
                        </th>
                        <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                            {{ __('dashboard.total_billed', ['currency' => currency_code()]) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entities as $index => $entity)
                        @php
                            $ranking = ($entities->currentPage() - 1) * $entities->perPage() + $index + 1;
                            $isTopThree = $ranking <= 3;
                            $isOdd = $ranking % 2 === 1;

                            // All ranking numbers have blue background
                            $rankingStyles = 'bg-blue-600 dark:bg-blue-500 text-white shadow-md shadow-blue-300/50 dark:shadow-blue-900/50 ring-2 ring-blue-400/70 dark:ring-blue-600/70';

                            $rowBg = match(true) {
                                $ranking === 1 => 'bg-gradient-to-r from-blue-50 via-blue-50/50 to-white dark:from-blue-900/20 dark:via-blue-900/10 dark:to-gray-800',
                                $ranking === 2 => 'bg-gradient-to-r from-blue-50/70 via-blue-50/30 to-white dark:from-blue-900/15 dark:via-blue-900/5 dark:to-gray-800',
                                $ranking === 3 => 'bg-gradient-to-r from-blue-50/50 via-blue-50/20 to-white dark:from-blue-900/10 dark:via-blue-900/5 dark:to-gray-800',
                                $isOdd => 'bg-gray-50/50 dark:bg-gray-800',
                                default => 'bg-white dark:bg-gray-800/50'
                            };

                            $entityPhoto = $entity->getFirstMediaUrl('profile', 'thumb');
                        @endphp
                        <tr wire:key="billing-entity-{{ $entity->id }}" class="{{ $rowBg }} hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                            <!-- Ranking -->
                            <td class="px-3 sm:px-4 py-3 sm:py-4 whitespace-nowrap text-center">
                                <div class="inline-flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-full {{ $rankingStyles }} font-bold text-sm">
                                    {{ $ranking }}
                                </div>
                            </td>
                            <!-- Logo -->
                            <td class="px-3 sm:px-4 py-3 sm:py-4 whitespace-nowrap text-center">
                                @if($entityPhoto)
                                    <img src="{{ $entityPhoto }}" alt="{{ $entity->name }}" class="h-9 w-9 sm:h-10 sm:w-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600 mx-auto">
                                @else
                                    <div class="h-9 w-9 sm:h-10 sm:w-10 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center border border-gray-200 dark:border-gray-600 mx-auto">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 16.5v-13h-.25a.75.75 0 010-1.5h12.5a.75.75 0 010 1.5H16v13h.25a.75.75 0 010 1.5h-3.5a.75.75 0 01-.75-.75v-2.5a.75.75 0 00-.75-.75h-2.5a.75.75 0 00-.75.75v2.5a.75.75 0 01-.75.75h-3.5a.75.75 0 010-1.5H4zm3-11a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5v-1zM7.5 9a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1zM11 5.5a.5.5 0 01.5-.5h1a.5.5 0 01.5.5v1a.5.5 0 01-.5.5h-1a.5.5 0 01-.5-.5v-1zm.5 3.5a.5.5 0 00-.5.5v1a.5.5 0 00.5.5h1a.5.5 0 00.5-.5v-1a.5.5 0 00-.5-.5h-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <!-- Entity Name -->
                            <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm {{ $isTopThree ? 'font-bold text-gray-900 dark:text-white' : 'font-medium text-gray-800 dark:text-gray-200' }}">
                                        {{ $entity->name }}
                                    </div>
                                    <!-- District on mobile -->
                                    <div class="sm:hidden text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ $entity->district->name ?? '-' }}
                                    </div>
                                </div>
                            </td>
                            <!-- District (hidden on mobile) -->
                            <td class="hidden sm:table-cell px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $entity->district->name ?? '-' }}
                                </span>
                            </td>
                            <!-- Total Billed -->
                            <td class="px-4 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right">
                                @if($ranking === 1)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-bold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400">
                                        {{ money($entity->total_affiliation_fee ?? 0) }}
                                    </span>
                                @elseif($isTopThree)
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ money($entity->total_affiliation_fee ?? 0) }}
                                    </span>
                                @else
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ money($entity->total_affiliation_fee ?? 0) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($entities->hasPages())
            <div class="mt-4">
                {{ $entities->links() }}
            </div>
        @endif
    @else
        <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.no_billing_data') }}</h3>
        </div>
    @endif
</div>
