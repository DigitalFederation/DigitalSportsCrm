@if($documents->count() > 0)
    <div class="sm:w-1/2 bg-white p-4 rounded-md shadow-md">
        <h1 class="text-lg font-bold mb-2">Pending Official Documents</h1>
        <div class="overflow-auto">
            <table class="table-auto w-full">
                <!-- Table header -->
                <thead
                    class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>

                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center md:text-left">{{ __('Individual') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center md:text-left">{{ __('Doc. Type') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center md:text-left">{{ __('Date') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center md:text-left">{{ __('Status') }}</div>
                    </th>

                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-right">{{ __('Actions') }}</div>
                    </th>
                </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-xs divide-y divide-slate-200">
                <!-- Row -->
                @foreach($documents as $document)
                    <tr>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <a href="#"
                               class="hover:text-cyan-600">{{ $document->individual->member_code }}</a>
                        </td>

                        <td class="px-2 py-3 whitespace-nowrap w-px text-center md:text-left">
                            {{ $document->name }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ ucfirst($document->stateName()) }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end items-end">
                                @if($document->stateName() == 'pending')
                                    <form method="POST"
                                          action="{{ route(request()->segment(1).'.official-documents.activate', $document->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                                class="text-green-500 hover:text-green-600 rounded-full"
                                                title="Accept">
                                            <span class="sr-only">Accept</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                 stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                <form
                                    action="{{ route(strtolower(auth()->user()->group->pluck('name')->first()).'.media.download') }}"
                                    method="POST"
                                    enctype="multipart/form-data" class="w-auto">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $document->media->value('id') }}">
                                    <button type="submit"
                                            class="text-neutral-500 hover:text-neutral-600 rounded-full"
                                            title="Accept">
                                        <span class="sr-only">Accept</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                             stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div></div>
@endif
