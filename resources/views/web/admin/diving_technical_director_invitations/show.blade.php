@section('title', __('diving.invitation_details'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <div>
                <h1 class="page-first-title">{{ __('diving.invitation_details') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('diving.technical_director_invitation_information') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.diving_technical_director_invitations.index') }}" class="btn btn-secondary">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2">
                <!-- Invitation Details -->
                <div class="card">
                    <h3 class="text-lg font-semibold mb-4">{{ __('diving.invitation_information') }}</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.entity') }}</label>
                            <p class="mt-1">
                                {{ $invitation->entity->name }}<br>
                                <span class="text-sm text-gray-500">{{ $invitation->entity->member_code ?? $invitation->entity->id }}</span>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.status') }}</label>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                      style="background-color: {{ $invitation->state->color() }}20; color: {{ $invitation->state->color() }}">
                                    {{ $invitation->state->name() }}
                                </span>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.invited_professional') }}</label>
                            <p class="mt-1">
                                {{ $invitation->individual->full_name }}<br>
                                <span class="text-sm text-gray-500">{{ $invitation->individual->member_code }}</span>
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.license') }}</label>
                            <p class="mt-1">{{ $invitation->licenseAttributed->license->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.certification_system') }}</label>
                            <p class="mt-1">{{ $invitation->certification_system }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.sent_on') }}</label>
                            <p class="mt-1">{{ $invitation->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        @if($invitation->accepted_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.accepted_on') }}</label>
                            <p class="mt-1">{{ $invitation->accepted_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                        
                        @if($invitation->rejected_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.rejected_on') }}</label>
                            <p class="mt-1">{{ $invitation->rejected_at->format('d/m/Y H:i') }}</p>
                        </div>
                        @endif
                    </div>
                    
                    @if($invitation->rejection_reason)
                    <div class="mt-4 p-4 bg-red-50 rounded">
                        <label class="block text-sm font-medium text-red-700">{{ __('diving.rejection_reason') }}</label>
                        <p class="mt-1 text-red-600">{{ $invitation->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
                
                <!-- Professional's Certifications -->
                <div class="card mt-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('diving.professional_certifications') }}</h3>
                    
                    @if($invitation->individual->divingProfessionalCertifications->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="table-auto w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left">{{ __('diving.certification') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('diving.system') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('diving.number') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('diving.issue_date') }}</th>
                                        <th class="px-4 py-2 text-left">{{ __('diving.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invitation->individual->divingProfessionalCertifications as $cert)
                                    <tr class="border-t">
                                        <td class="px-4 py-2">
                                            <div>
                                                <div class="font-medium">{{ $cert->certification_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $cert->certification_level }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">{{ $cert->certification_system }}</td>
                                        <td class="px-4 py-2">{{ $cert->certification_number }}</td>
                                        <td class="px-4 py-2">{{ $cert->issue_date->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                  style="background-color: {{ $cert->state->color() }}20; color: {{ $cert->state->color() }}">
                                                {{ $cert->state->name() }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('diving.no_certifications_found') }}</p>
                    @endif
                </div>
            </div>
            
            <!-- Actions Sidebar -->
            <div class="lg:col-span-1">
                <div class="card">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Actions') }}</h3>
                    
                    @if($invitation->status_class === \Domain\Diving\States\PendingDivingTechnicalDirectorInvitationState::class)
                        <form action="{{ route('admin.diving_technical_director_invitations.cancel', $invitation) }}" method="POST" 
                              onsubmit="return confirm('{{ __('diving.confirm_cancel_invitation') }}');">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger w-full">
                                {{ __('diving.cancel_invitation') }}
                            </button>
                        </form>
                    @endif
                    
                    <div class="mt-4 p-4 bg-gray-50 rounded">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">{{ __('diving.invitation_lifecycle') }}</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• {{ __('diving.invitation_sent_to_professional') }}</li>
                            @if($invitation->status_class === \Domain\Diving\States\AcceptedDivingTechnicalDirectorInvitationState::class)
                                <li>• {{ __('diving.professional_accepted_invitation') }}</li>
                            @elseif($invitation->status_class === \Domain\Diving\States\RejectedDivingTechnicalDirectorInvitationState::class)
                                <li>• {{ __('diving.professional_rejected_invitation') }}</li>
                            @elseif($invitation->status_class === \Domain\Diving\States\CanceledDivingTechnicalDirectorInvitationState::class)
                                <li>• {{ __('diving.invitation_was_canceled') }}</li>
                            @else
                                <li>• {{ __('diving.waiting_professional_response') }}</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>