<div class="min-h-screen">
    {{-- Sticky Header --}}
    <header class="sticky top-10 z-10 bg-white border-b border-gray-200 rounded-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                {{-- Title and Status --}}
                <div class="flex items-center space-x-4 flex-shrink-0">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $certification->name ?: $certification->certification->name }}
                    </h1>

                    <x-tables.badge :status="ucfirst($certification->stateName())"
                                    :color="$certification->stateColor()" />
                </div>

                {{-- Header Actions --}}
                <div
                    class="flex flex-col md:flex-row items-center md:space-x-4 flex-wrap justify-end gap-y-2 w-full md:w-auto">
                    @if($canDownloadCard)
                        <a href="{{ route($route_prefix.'.'.$cardRouteName.'.download', $certification) }}"
                           class="btn btn-info flex items-center gap-x-2 w-full md:w-auto">
                            <x-houtline-credit-card class="w-5 h-5" />
                            <span>{{ __('certifications.actions.download_card') }}</span>
                        </a>
                    @endif

                    @if($canGeneratePdf)
                        <livewire:generate-individual-certification-card-pdf
                            :certificationAttributedId="$certification->id"
                        />
                    @endif

                    {{-- Action Buttons --}}
                    @if($allowedActions['approveRequest'])
                        <form action="{{ route('individual.certification-attributed.activate') }}"
                              class="w-full md:w-auto"
                              method="post">
                            @csrf
                            <input type="hidden" name="id" value="{{ $certification->id }}">
                            <button type="submit"
                                    class="btn btn-primary w-full md:w-auto">{{ __('certifications.actions.approve_request') }}</button>
                        </form>

                        <form action="{{ route('individual.certification-attributed.cancel') }}"
                              class="w-full md:w-auto"
                              method="post">
                            @csrf
                            <input type="hidden" name="id" value="{{ $certification->id }}">
                            <button type="submit"
                                    class="btn btn-danger w-full md:w-auto">{{ __('certifications.actions.reject_request') }}</button>
                        </form>
                    @endif

                    @if($allowedActions['validateCertification'])
                        <form
                            action="{{ route($route_prefix.'.certification-attributed.activate') }}"
                            class="w-full md:w-auto"
                            method="post">
                            @csrf
                            <input type="hidden" name="id" value="{{ $certification->id }}">
                            <button type="submit"
                                    class="btn btn-primary w-full md:w-auto">{{ __('certifications.actions.validate_certification') }}</button>
                        </form>
                    @endif

                    @if($allowedActions['rejectCertification'])
                        <form action="{{ route($route_prefix.'.certification-attributed.cancel') }}"
                              method="post"
                              class="w-full md:w-auto"
                              onsubmit="return confirm('{{ __('certifications.actions.confirm_reject') }}')">
                            @csrf
                            <input type="hidden" name="id" value="{{ $certification->id }}">
                            <button type="submit"
                                    class="btn btn-danger w-full md:w-auto">{{ __('certifications.actions.reject_certification') }}</button>
                        </form>
                    @endif

                    @if($allowedActions['editCertification'])
                        <div @details-updated.window="location.reload()">
                            <x-dynamic-modal
                                :viewName="'edit-certification-details'"
                                :params="['certification' => $certification]"
                                headerTitle="{{ __('certifications.actions.edit_details_title') }}"
                                buttonLabel="{{ __('certifications.actions.validation_and_details') }}"
                                buttonClass="btn btn-success w-full md:w-auto"
                                :isLivewire="true"
                                animation="transition ease-in duration-200"
                            />
                        </div>
                    @endif

                    @if($allowedActions['suspendCertification'])
                        <form action="{{ route($route_prefix.'.certification-attributed.suspend') }}"
                              method="post"
                              class="w-full md:w-auto"
                              onsubmit="return confirm('{{ __('certifications.actions.confirm_suspend') }}')">
                            @csrf
                            <input type="hidden" name="id" value="{{ $certification->id }}">
                            <button type="submit"
                                    class="btn btn-danger w-full md:w-auto">
                                <span>{{ __('certifications.actions.suspend_certification') }}</span>
                            </button>
                        </form>
                    @endif

                    @if($allowedActions['activateCertification'])
                        <form action="{{ route($route_prefix.'.certification-attributed.unsuspend') }}"
                              class="w-full md:w-auto"
                              method="post"
                              onsubmit="return confirm('{{ __('certifications.actions.confirm_activate') }}')">
                            @csrf
                            <input type="hidden" name="id" value="{{ $certification->id }}">
                            <button type="submit"
                                    class="btn btn-primary w-full md:w-auto">{{ __('certifications.actions.activate_certification') }}</button>
                        </form>
                    @endif

                    <a href="{{ url()->previous() }}" class="btn btn-info w-full md:w-auto">
                        <x-houtline-chevron-left class="h-4 w-4" />
                        <span>{{ __('certifications.actions.back') }}</span>
                    </a>

                </div>
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="max-w-7xl mx-auto py-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Left Column - Main Content --}}
            <div class="lg:col-span-8 space-y-8 order-1 md:order-0">
                {{ $mainContent }}
            </div>

            {{-- Right Column - Sidebar --}}
            <div class="lg:col-span-4 space-y-8 order-0 md:order-1">
                <div class="sticky top-24 space-y-8">
                    {{ $sidebar }}
                </div>
            </div>
        </div>
    </main>

    {{-- Action Modals --}}
    @if(isset($modals))
        {{ $modals }}
    @endif
</div>
