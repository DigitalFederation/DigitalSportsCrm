<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Membership Plan') }} - {{ $plan->name }}</h1>
            </div>


            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a href="{{ URL::previous() }}" class="btn btn-info flex justify-between">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                         stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                    </svg>

                    <span class="">{{ __('Back') }}</span>
                </a>

            </div>
        </div>

        <div class="flex flex-col md:flex-row sm:items-start my-5 gap-4">
            <div class="md:w-2/3 flex flex-col gap-4">
                <div class="card overflow-hidden">
                    <h2 class="card-title">{{ $plan->name }}</h2>

                    <div class="mt-4">
                        <p class="text-sm text-gray-500"><strong>Type:</strong> {{ $plan->committee?->name }}</p>
                        <p class="text-sm text-gray-500"><strong>Price:</strong> {{ $plan->price }}</p>
                    </div>
                </div>
            </div>

            @if($plan->licenses->count() > 0)
                <div class="card md:w-1/3 overflow-hidden">
                    <h2 class="card-title">{{ __('Associated Licenses') }}</h2>

                    <div class="mt-4">
                        <table>
                            <thead>
                            <tr>
                                <th class="py-2 text-left">{{ __('Name') }}</th>
                                <th class="py-2 text-left">{{ __('Committee') }}</th>
                                <th class="py-2 text-left">{{ __('Type') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($plan->licenses as $license)
                                <tr>
                                    <td class="border px-4 py-2 text-sm">{{ $license->name }}</td>
                                    <td class="border px-4 py-2 text-sm">{{ $license->committee->name }}</td>
                                    <td class="border px-4 py-2 text-sm">{{ ucwords($license->type->name) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layout>
