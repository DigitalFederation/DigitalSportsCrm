{{-- components/certification/actions.blade.php --}}
<div class="flex items-center space-x-4">
    @if($canActivate)
        <form action="{{ route(request()->segment(1).'.certification-attributed.activate') }}"
              method="POST"
              class="inline-flex">
            @csrf
            <input type="hidden" name="id" value="{{ $certification->id }}">
            <button type="submit"
                    class="btn-primary">
                {{ __('certifications.actions.validate_certification') }}
            </button>
        </form>
    @endif

    @if($canSuspend)
        <form action="{{ route(request()->segment(1).'.certification-attributed.suspend') }}"
              method="POST"
              class="inline-flex"
              x-data
              @submit.prevent="if(confirm('{{ __('certifications.actions.confirm_suspend') }}')) $el.submit()">
            @csrf
            <input type="hidden" name="id" value="{{ $certification->id }}">
            <button type="submit"
                    class="btn-danger">
                {{ __('certifications.actions.suspend_certification') }}
            </button>
        </form>
    @endif

    @if($canEdit)
        <x-dynamic-modal
            :viewName="'edit-certification-details'"
            :params="['certification' => $certification]"
            headerTitle="{{ __('certifications.actions.edit_details_title') }}"
            buttonLabel="{{ __('certifications.actions.edit_details') }}"
            buttonClass="btn btn-secondary"
            :isLivewire="true"
        />
    @endif
</div>
