@section('title', __('admin.homepage_settings'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title">{{ __('admin.homepage_settings') }}</h1>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.homepage-settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card mb-6">
                <div class="flex flex-col md:-mr-px">
                    <section class="mb-4 w-full">
                        <x-information-box
                            title="{{ __('admin.homepage_settings') }}"
                            body="{{ __('admin.homepage_settings_description') }}">
                        </x-information-box>

                        {{-- Identity --}}
                        <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('admin.homepage_identity') }}</h2>
                        <div class="flex flex-wrap -mx-4 mb-8 space-y-4 md:space-y-0">
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="app_name">
                                    {{ __('admin.homepage_app_name') }}
                                </label>
                                <input id="app_name" class="form-input w-full" type="text" name="app_name"
                                       value="{{ old('app_name', $settings['app_name'] ?? '') }}"
                                       placeholder="{{ config('app.name') }}" />
                                @error('app_name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="federation_name">
                                    {{ __('admin.homepage_federation_name') }}
                                </label>
                                <input id="federation_name" class="form-input w-full" type="text" name="federation_name"
                                       value="{{ old('federation_name', $settings['federation_name'] ?? '') }}"
                                       placeholder="{{ $brand['name'] }}" />
                                @error('federation_name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Images --}}
                        <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('admin.homepage_images') }}</h2>
                        <div class="flex flex-wrap -mx-4 mb-8 space-y-6 md:space-y-0">
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="hero_background">
                                    {{ __('admin.homepage_hero_background') }}
                                </label>
                                @if (!empty($settings['hero_background_path']))
                                    <div class="mb-2">
                                        <img src="{{ asset($settings['hero_background_path']) }}"
                                             alt="{{ __('admin.homepage_hero_background') }}"
                                             class="h-28 w-full object-cover rounded-lg border border-slate-200">
                                        <label class="inline-flex items-center mt-2 text-sm text-slate-600">
                                            <input type="checkbox" name="remove_hero_background" value="1" class="form-checkbox mr-2">
                                            {{ __('admin.homepage_remove_image') }}
                                        </label>
                                    </div>
                                @endif
                                <input id="hero_background" class="form-input w-full" type="file"
                                       name="hero_background" accept="image/jpeg,image/png,image/webp" />
                                <p class="text-xs text-slate-500 mt-1">{{ __('admin.homepage_hero_background_hint') }}</p>
                                @error('hero_background')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="logo">
                                    {{ __('admin.homepage_logo') }}
                                </label>
                                <div class="mb-2 flex items-center gap-4">
                                    <img src="{{ asset($settings['logo_path'] ?? $brand['logo_path']) }}"
                                         alt="Logo" class="h-14 w-auto rounded border border-slate-200 bg-white p-1">
                                    @if (!empty($settings['logo_path']))
                                        <label class="inline-flex items-center text-sm text-slate-600">
                                            <input type="checkbox" name="remove_logo" value="1" class="form-checkbox mr-2">
                                            {{ __('admin.homepage_remove_image') }}
                                        </label>
                                    @endif
                                </div>
                                <input id="logo" class="form-input w-full" type="file"
                                       name="logo" accept="image/jpeg,image/png,image/svg+xml,image/webp" />
                                @error('logo')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Footer --}}
                        <h2 class="text-lg font-semibold text-slate-800 mb-4">{{ __('admin.homepage_footer') }}</h2>
                        <div class="flex flex-wrap -mx-4 space-y-4 md:space-y-0">
                            <div class="w-full px-4">
                                <label class="block text-sm font-medium mb-1" for="federation_about">
                                    {{ __('admin.homepage_footer_about') }}
                                </label>
                                <textarea id="federation_about" class="form-input w-full" rows="3"
                                          name="federation_about"
                                          placeholder="{{ $brand['about'] }}">{{ old('federation_about', $settings['federation_about'] ?? '') }}</textarea>
                                @error('federation_about')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="w-full px-4 md:w-1/2 mt-4">
                                <label class="block text-sm font-medium mb-1" for="federation_support_email">
                                    {{ __('admin.homepage_footer_support_email') }}
                                </label>
                                <input id="federation_support_email" class="form-input w-full" type="email"
                                       name="federation_support_email"
                                       value="{{ old('federation_support_email', $settings['federation_support_email'] ?? '') }}"
                                       placeholder="{{ $brand['support_email'] }}" />
                                <p class="text-xs text-slate-500 mt-1">{{ __('admin.homepage_footer_support_email_hint') }}</p>
                                @error('federation_support_email')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Legal pages --}}
                        <h2 class="text-lg font-semibold text-slate-800 mt-8 mb-4">{{ __('admin.homepage_legal') }}</h2>
                        <div class="flex flex-wrap -mx-4">
                            <div class="w-full px-4 md:w-1/2">
                                <label class="block text-sm font-medium mb-1" for="federation_address">
                                    {{ __('admin.homepage_footer_address') }}
                                </label>
                                <input id="federation_address" class="form-input w-full" type="text"
                                       name="federation_address"
                                       value="{{ old('federation_address', $settings['federation_address'] ?? '') }}"
                                       placeholder="{{ $brand['address'] }}" />
                                <p class="text-xs text-slate-500 mt-1">{{ __('admin.homepage_legal_address_hint') }}</p>
                                @error('federation_address')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <footer>
                    <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                        <div class="flex self-end gap-2">
                            <a href="{{ url('/') }}" target="_blank" rel="noopener"
                               class="btn border-slate-200 hover:border-slate-300 text-slate-600">
                                {{ __('admin.homepage_preview') }}
                            </a>
                            <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                                {{ __('common.save') }}
                            </button>
                        </div>
                    </div>
                </footer>
            </div>
        </form>
    </div>
</x-layout>
