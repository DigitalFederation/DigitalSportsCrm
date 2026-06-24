@props(['inCard' => false, 'title'=> __('No data to display.')])
<div class="flex flex-col justify-center items-center {{ $inCard ? '' : 'h-[calc(100vh-400px)]' }}">
    <p class="my-2 md:my-4 text-center text-gray-500 text-xl font-bold mx-auto"> {{ $title}} </p>
    <!-- Back link -->
    @if(url()->previous() !== url()->current() && empty($inCard))
        <a href="{{ url()->previous() }}" class="btn btn-info btn-sm mx-auto">{{ __('Go Back') }}</a>
    @endif
</div>
