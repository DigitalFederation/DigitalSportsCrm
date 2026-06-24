<!-- Page header -->
<div class="sm:flex sm:justify-between sm:items-center">

    <!-- Left: Title -->
    <div class="mb-4 sm:mb-0">
        <h1 class="page-first-title">{{ __('Certification Attributed') }}</h1>
    </div>

    <!-- Right: Actions -->
    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

        <!-- Button -->

    </div>

</div>

<div class="sm:grid sm:grid-cols-2 gap-2 mb-5">

    <div class="panel-box p-4">

        <p class="font-bold"> {{ $certificationAttributed->certification->name }} </p>
        <p class="text-sm"> {{ __('Date requested:') }} {{ date('d/m/Y', strtotime($certificationAttributed->created_at)) }} </p>
        <p class="text-sm"> {{ __('Individual:') }} {{ $certificationAttributed->individual?->name }} {{ $certificationAttributed->individual?->surname }}</p>
        @if(!empty($mainInstructor))
            <p class="text-sm"> {{ __('Instructor:') }} {{ $mainInstructor->name }} {{ $mainInstructor->surname }}</p>
        @endif
        <p class="text-sm"> {{ __('Status:') }} {{ ucfirst($certificationAttributed->stateName()) }} </p>

    </div>

    <div class="panel-box p-4">

        <p class="font-bold"> {{ $certificationAttributed->individual?->name }} </p>

        <table class="w-full mt-4">
            <tbody>
            <tr>
                <td>{{ $certificationAttributed->certification->name }}</td>
                <td>{{ $certificationAttributed->current_term_ends_at }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    @if(!empty($mainInstructor))
        <div class="panel-box p-4">

            <p> {{ __('Main Instructor:') }} <span class="font-bold">{{ $mainInstructor->name }}</span></p>

            <table class="w-full mt-4">
                <tbody>
                @foreach($mainInstructor->certifications as $certification)
                    <tr>
                        <td> {{ $certification->name }} </td>
                        <td> {{ $certification->current_term_ends_at }} </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
