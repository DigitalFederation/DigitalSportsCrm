@section('title', 'Anti-Doping')
<x-public-layout>
    @php($brand = config('branding.primary'))
    <main class="bg-slate-900 h-screen">

        <div class="mx-auto pt-4 w-24">
            <img src="{{ asset($brand['logo_path']) }}" class="w-24 " alt="{{ $brand['short_name'] }}">
        </div>

        <div class="w-full md:max-w-lg md:mx-auto mt-40">
            @include('components.layout.banner_message')

            <div class="card justify-center flex">
                <form method="POST" action="{{ route('public.anti-doping.pin.verify') }}">
                    @csrf
                    <div class="flex flex-row">
                        <div class="form-group">
                            <label for="pin">PIN:</label>
                            <input type="password" class="form-input" id="pin" name="pin" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>

        </div>

    </main>
</x-public-layout>
