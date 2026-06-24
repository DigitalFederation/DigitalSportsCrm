<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Create Role') }} </h1>
        </div>


        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="bg-white shadow-lg rounded-sm mb-8">
                <div class="flex flex-col md:flex-row md:-mr-px">
                    <div class="grow">
                        <!-- Panel body -->

                        <section class="p-6">

                            <div class="flex information-box">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <circle cx="12" cy="12" r="9" />
                                    <line x1="12" y1="8" x2="12.01" y2="8" />
                                    <polyline points="11 12 12 12 12 16 13 16" />
                                </svg>
                                <p class="text-sm"> Choose a name for the Role and add the required permissions on the next screen.</p>

                            </div>

                            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

                                <div class="sm:w-1/4">
                                   <label class="block text-sm font-medium mb-1" for="name">{{ __('Name') }}</label>
                                    <input type="text"
                                        class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                                        name="name"
                                        id="name">

                                    @if($errors->has('name'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('name') }}
                                        </div>
                                    @endif
                                </div>


                            </div>

                        </section>

                        <!-- Panel footer -->
                        <section>
                            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                                <div class="flex self-end">
                                    <a class="btn self-center bg-slate-500 text-white" href="{{ route('admin.roles.index') }}"> {{ __('Back') }} </a>
                                    <button type="submit" class="btn btn-action ml-3 px-3 py-2 rounded">
                                        Save Record
                                    </button>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </form>

    </div>

</x-layout>
