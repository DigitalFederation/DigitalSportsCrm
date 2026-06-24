@section('title', __('certification_card.cmas_certifications'))
<x-layout>
    <div class="min-h-screen"
         x-data="{
            selectedCard: null,
            showModal: false,
            openModal(event, certification) {
                event.preventDefault();
                this.selectedCard = certification;
                this.showModal = true;
                document.body.style.overflow = 'hidden';
            },
            closeModal() {
                this.showModal = false;
                this.selectedCard = null;
                document.body.style.overflow = '';
            }
        }">

        {{-- Header with Statistics --}}
        <div class="bg-white border-b border-gray-200 rounded-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
                <div class="flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">
                            {{ __('certification_card.cmas_certifications') }}
                        </h1>
                        <p class="text-sm text-gray-500">
                            {{ $certifications_attributed->sum(function($group) { return $group->count(); }) }} {{ __('certification_card.total_certifications') }}
                        </p>
                    </div>

                    {{-- Smart Filter Section --}}
                    <div class="flex-shrink-0 mt-4 md:mt-0 flex space-x-2" x-cloak>
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="btn btn-info">
                                <span class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                    {{ __('certification_card.filter') }}
                                </span>
                            </button>

                            {{-- Filter Dropdown --}}
                            <div x-show="open"
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg z-10">
                                <form action="{{ request()->url() }}" method="get" class="p-4">
                                    <div class="space-y-4">
                                        {{-- Category Filter --}}
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700">{{ __('certification_card.category') }}</label>
                                            <select name="filter[filter_certification_category]"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <option value="">{{ __('certification_card.all_categories') }}</option>
                                                @foreach($certification_categories as $category)
                                                    <option value="{{ $category }}"
                                                        {{ request()->input('filter.filter_certification_category') == $category ? 'selected' : '' }}>
                                                        {{ __('certification_card.categories.' . $category) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="flex justify-end pt-4">
                                            <button type="submit"
                                                    class="btn btn-primary">{{ __('certification_card.apply_filters') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8">
            <div class="space-y-12">
                @forelse($certifications_attributed as $category => $certifications)
                    <div class="overflow-hidden">
                        <!-- Category header -->
                        <div class="bg-white px-6 py-2 border-b border-gray-200 rounded-xl shadow-sm ">
                            <h2 class="text-md font-bold text-gray-800">{{ __('certification_card.categories.' . $category) }}</h2>
                        </div>

                        <div class="mt-4">
                            <div class="relative">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    @foreach($certifications as $certification)
                                        <div class="group relative block"
                                             @click="window.innerWidth < 768 ? openModal($event, {{ json_encode($certification) }}) : window.location.href='{{ route('individual.international-certification-card.show', $certification->id) }}'">

                                            <div
                                                class="certification-card transform transition-all duration-300 group-hover:scale-[1.02]">
                                                {{-- Card Container with Shadow Effect --}}
                                                <div class="relative rounded-xl overflow-hidden shadow-lg">
                                                    {{-- Main Card Image --}}
                                                    <div id="card-front-{{ $certification->id }}"
                                                         class="aspect-[1.586/1] relative">
                                                        @if(!empty($certification->certification->certification_view))
                                                            <img
                                                                src="{{ Storage::disk('public')->url('img/cards/' . $certification->certification->certification_view) }}"
                                                                alt="{{ $certification->certification->name }}"
                                                                class="w-full h-full object-cover"
                                                            >
                                                        @else
                                                            <img
                                                                src="{{ asset('img/default_certification_card.jpg') }}"
                                                                alt="{{ $certification->name }}"
                                                                class="w-full h-full object-cover"
                                                            >
                                                        @endif

                                                        {{-- Committee Badge Overlay --}}
                                                        <div class="absolute top-4 left-4">
                                                            @if($certification->certification->committee)
                                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 shadow-sm">
                                                                    {{ $certification->certification->committee->name }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        {{-- Status Badge Overlay --}}
                                                        <div class="absolute top-4 right-4">
                                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium shadow-sm
                                                                       {{ match($certification->stateName()) {
                                                                           'active' => 'bg-active text-white shadow-md',
                                                                           'pending' => 'bg-amber-100 text-amber-800',
                                                                           default => 'bg-gray-100 text-gray-800'
                                                                       } }}">
                                                                {{ ucfirst($certification->stateName()) }}
                                                            </span>
                                                        </div>

                                                        {{-- Bottom Gradient Overlay for better text readability --}}
                                                        <div
                                                            class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent"></div>

                                                        {{-- Bottom Info Overlay --}}
                                                        <div class="absolute inset-x-0 bottom-0 p-4">
                                                            <div class="flex justify-between items-end">
                                                                <div>
                                                                    <p class="text-sm text-white/90">
                                                                        {{ $certification->license_number }}
                                                                    </p>
                                                                    <div class="flex gap-x-2">
                                                                        <p class="text-xs text-white/75">
                                                                            {{ $certification->current_term_starts_at?->format('M d, Y') }}
                                                                        </p>
                                                                        <p class="text-xs text-white/75">
                                                                            {{ $certification->certification->name }}
                                                                        </p>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div id="card-reverse-{{ $certification->id }}" class="hidden">
                                                    <x-certification_attributed.card_reverse
                                                        :certificationAttributed="$certification" />
                                                </div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Empty State --}}
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('certification_card.no_international_certifications') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('certification_card.no_international_certifications_message') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Mobile Modal -->
        <template x-teleport="body">
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 md:hidden"
                 @click.self="closeModal">

                <!-- Modal Backdrop -->
                <div class="absolute inset-0 bg-black/75 backdrop-blur-sm"></div>

                <!-- Modal Content -->
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="translate-y-full"
                     x-transition:enter-end="translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-y-0"
                     x-transition:leave-end="translate-y-full"
                     class="absolute inset-x-0 bottom-0 bg-white rounded-t-3xl overflow-hidden"
                     @touchstart="startY = event.touches[0].clientY"
                     @touchmove.prevent="
                 if (!startY) return;
                 const currentY = event.touches[0].clientY;
                 const diff = currentY - startY;
                 if (diff > 100) closeModal();
             "
                     @touchend="startY = null"
                     x-data="{ startY: null }">

                    <!-- Drag Handle -->
                    <div class="w-full py-4 flex justify-center">
                        <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                    </div>

                    <!-- Cards Container -->
                    <div class="px-4 pb-8 space-y-6">
                        <!-- Front Card -->
                        <template x-if="selectedCard">
                            <div class="certification-card transform transition-all duration-300">
                                <div class="relative rounded-xl overflow-hidden shadow-lg aspect-[1.586/1]">
                                    <div
                                        x-html="document.getElementById(`card-front-${selectedCard.id}`).innerHTML"></div>
                                </div>
                            </div>
                        </template>

                        <!-- Back Card -->
                        <template x-if="selectedCard">
                            <div class="certification-card-reverse"
                                 x-html="document.getElementById(`card-reverse-${selectedCard.id}`).innerHTML">
                            </div>
                        </template>

                        <!-- View Details Button -->
                        <template x-if="selectedCard">
                            <a :href="`/individual/international-certification-card/${selectedCard.id}`"
                               class="block w-full py-3 px-4 btn-primary text-white text-center font-medium hover:bg-blue-700 transition-colors text-lg">
                                {{ __('certification_card.view_details') }}
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

</x-layout>
