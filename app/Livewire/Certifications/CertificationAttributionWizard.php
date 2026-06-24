<?php

declare(strict_types=1);

namespace App\Livewire\Certifications;

use App\Events\CertificationAttributedCreatedEvent;
use App\Models\Committee;
use Domain\Certifications\Actions\CalculateCertificationPriceAction;
use Domain\Certifications\Actions\CreateCertificationAttributedAction;
use Domain\Certifications\Actions\GetCertificationsFromInstructorAction;
use Domain\Certifications\DataTransferObject\CertificationAttributedData;
use Domain\Certifications\Models\Certification;
use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityProfessionalRoleState;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\DetectIfIndividualIsInstructorAction;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Exception;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class CertificationAttributionWizard extends Component implements HasForms
{
    use InteractsWithForms;

    /**
     * Actor type: 'federation' or 'entity'.
     * Determines the behavior of the wizard.
     */
    public string $actorType = 'federation'; // Default to federation

    /**
     * Listen for events from child components (Livewire event system)
     */
    protected $listeners = [
        'selectDirector',
        'removeDirector',
        'toggleAssistant',
        'toggleStudent',
    ];

    public int $step = 1;

    // Step 1 State
    public ?int $selectedFederationId = null;
    public ?int $selectedSchoolId = null;

    // Step 2 State
    public ?string $selectedDirectorId = null;
    public array $selectedAssistantIds = [];
    public array $selectedStudentIds = [];

    // Step 3 State
    public ?int $selectedCertificationId = null;
    public ?string $issueDate = null;
    public ?string $expirationDate = null;
    public ?string $notes = null;
    public string $selectedPriceOption = 'digital';

    // Loaded Data
    public ?Federation $selectedFederation = null;
    public ?Entity $selectedSchool = null;
    public ?Individual $selectedDirector = null;
    public Collection $selectedAssistants;
    public Collection $selectedStudents;
    public ?Certification $selectedCertification = null;
    public bool $federationApprove = false;
    public Collection $certifications;
    public array $federationSchoolsOptions = []; // New property for caching school options

    public ?string $committee_code = '';

    public $successMessage = '';
    public $showSuccessState = false;
    public $attributionSummary = [];

    public array $studentNationalNumbers = [];
    public bool $confirmationAccepted = false;

    // Validation Rules (Simplified - refine per step)
    protected function rules(): array
    {
        return [
            'selectedFederationId' => 'required_if:step,1|integer|exists:federations,id',
            'selectedSchoolId' => 'required_if:step,1|integer|exists:entities,id',
            // Add more rules for other steps
        ];
    }

    public function mount(): void
    {
        $this->selectedAssistants = collect();
        $this->selectedStudents = collect();
        $this->certifications = collect();
        $this->showSuccessState = false;

        $user = Auth::user();

        if ($this->actorType === 'entity') {
            $entity = $user->entities()->first();
            if ($entity) {
                $this->selectedSchoolId = $entity->id;
                $this->selectedSchool = $entity;
                $federation = $entity->federations()->first();
                if ($federation) {
                    $this->selectedFederationId = $federation->id;
                    $this->selectedFederation = $federation;
                    // For entity, school options are not typically selected by the user in this step
                } else {
                    Notification::make()->title(__('certifications.error_entity_federation_context_not_found'))->danger()->send();
                }
                $this->federationApprove = false;
            } else {
                Notification::make()->title(__('certifications.error_entity_context_not_found'))->danger()->send();
            }
        } else { // Default federation actor type
            $federation = $user->federations()->first();
            if ($federation) {
                $this->selectedFederationId = $federation->id;
                $this->selectedFederation = $federation;
                $this->loadFederationSchoolsOptions(); // Load options on mount for federation
            } else {
                Notification::make()->title(__('certifications.error_federation_context_not_found'))->danger()->send();
                $this->federationApprove = false;
            }
        }
    }

    // --- Step Navigation ---

    public function nextStep(): void
    {
        $this->validateStep($this->step);
        $this->step++;
        $this->dispatch('stepChanged', $this->step); // For potential Alpine listeners
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            $this->dispatch('stepChanged', $this->step); // For potential Alpine listeners
        }
    }

    public function goToStep(int $targetStep): void
    {
        if ($targetStep > $this->step) {
            // Validate all steps up to the target
            for ($i = 1; $i < $targetStep; $i++) {
                $this->validateStep($i);
            }
        }
        $this->step = $targetStep;
        $this->dispatch('stepChanged', $this->step);
    }

    // --- Data Loading & Selection ---

    public function updatedSelectedFederationId(int $id): void
    {
        $this->selectedFederation = Federation::find($id);
        $this->selectedSchoolId = null; // Reset school when federation changes
        $this->selectedSchool = null;
        $this->loadFederationSchoolsOptions(); // Reload options when federation changes
        // Reset subsequent steps if needed
        $this->resetStepData(2);
    }

    public function updatedSelectedSchoolId(?int $id): void
    {
        $this->selectedSchool = $id ? Entity::find($id) : null;
        // Reset subsequent steps if needed
        $this->resetStepData(2);
    }

    public function selectDirector(string $individualId, DetectIfIndividualIsInstructorAction $checkInstructor): void
    {
        $individual = Individual::find($individualId);

        if (! $individual) {
            Notification::make()->title(__('certifications.director_not_found'))->body(__('certifications.director_not_found_body'))->danger()->send();
            $this->removeDirector();

            return;
        }

        // 1. Check professional role within the selected entity (if a school is selected)
        if ($this->selectedSchoolId) {
            $isValidForSchool = Individual::where('id', $individual->id)
                ->whereHas('professionalRoleEntities', function (Builder $query) {
                    $query->where('entity_id', $this->selectedSchoolId)
                        ->where('status_class', ActiveEntityProfessionalRoleState::class)
                        ->whereHas('professionalRole', function (Builder $query) {
                            $query->where('role', 'like', '%INSTRUCTOR%');
                        });
                })
                ->exists();

            if (! $isValidForSchool) {
                Notification::make()->title(__('certifications.invalid_director_role'))
                    ->body(__('certifications.invalid_director_role_body'))
                    ->warning()->send();
                $this->removeDirector();

                return;
            }
        }

        // 2. Perform the comprehensive instructor check using the action
        $directorQuery = Individual::where('id', $individual->id)->with('licenses');

        if ($this->selectedFederationId) {
            // Attempt to use a known scope if available, otherwise a direct whereHas
            // Assuming 'IndividualFromFederation' is a scope on the Individual model:
            if (method_exists(Individual::class, 'scopeIndividualFromFederation')) {
                $directorQuery->IndividualFromFederation($this->selectedFederationId);
            } else {
                // Fallback: direct check on federations relationship
                $directorQuery->whereHas('federations', function (Builder $q) {
                    $q->where('federation.id', $this->selectedFederationId);
                });
            }
        }

        if (! $checkInstructor($directorQuery)) {
            Notification::make()->title(__('certifications.director_qualification_failed'))
                ->body(__('certifications.director_qualification_failed_body'))
                ->warning()->send();
            $this->removeDirector();

            return;
        }

        // If all checks pass:
        $this->selectedDirectorId = $individualId;
        $this->selectedDirector = $individual;

        if (($key = array_search($individualId, $this->selectedAssistantIds, true)) !== false) {
            unset($this->selectedAssistantIds[$key]);
            $this->selectedAssistantIds = array_values($this->selectedAssistantIds);
            $this->selectedAssistants = Individual::findMany($this->selectedAssistantIds);
        }

        $this->updateCertificationsFromInstructor();
        $this->resetStepData(3);
    }

    public function removeDirector(): void
    {
        $this->selectedDirectorId = null;
        $this->selectedDirector = null;
        $this->resetStepData(3); // Resets selected cert, dates etc.
        $this->updateCertificationsFromInstructor(); // Ensures certification list is cleared/updated
    }

    public function toggleAssistant($individualId): void
    {
        $individualId = (string) $individualId;
        // === Server-side Validation ===
        if ($individualId === $this->selectedDirectorId) {
            Notification::make()->title(__('certifications.cannot_select_director_as_assistant'))->warning()->send();

            return;
        }

        $individual = Individual::find($individualId);
        if (! $individual) {
            return;
        }

        if (in_array($individualId, $this->selectedAssistantIds, true)) {
            $this->selectedAssistantIds = array_values(
                array_filter(
                    $this->selectedAssistantIds,
                    fn (string $id) => $id !== $individualId
                )
            );
        } else {
            $this->selectedAssistantIds[] = $individualId;
        }

        $this->selectedAssistants = Individual::findMany($this->selectedAssistantIds);
    }

    public function toggleStudent($individualId): void
    {
        $individualId = (string) $individualId;

        if (in_array($individualId, $this->selectedStudentIds, true)) {
            $this->selectedStudentIds = array_values(
                array_filter(
                    $this->selectedStudentIds,
                    fn (string $id) => $id !== $individualId
                )
            );
        } else {
            $this->selectedStudentIds[] = $individualId;
        }

        $this->selectedStudents = Individual::findMany($this->selectedStudentIds);
    }

    public function updatedSelectedCertificationId(int $id): void
    {
        $this->selectedCertification = Certification::find($id);
        // Reset price option when certification changes
        $this->selectedPriceOption = 'digital';
    }

    public function updatedSelectedPriceOption(string $value): void
    {
        $this->selectedPriceOption = $value;
    }

    public function updatedFederationApprove(bool $value): void
    {
        $this->federationApprove = $value;
        if ($value) {
            // If approved by federation, clear director and assistants as they are not needed
            $this->selectedDirectorId = null;
            $this->selectedDirector = null;
            $this->selectedAssistantIds = [];
            $this->selectedAssistants = collect();

            // Load certifications from committee
            $this->updateCertificationsFromCommittee();
        } else {
            // Clear certifications when toggling off federation approval
            $this->certifications = collect();
            // If we have a director, load their certifications
            if ($this->selectedDirector) {
                $this->updateCertificationsFromInstructor();
            }
        }
        $this->dispatch('federationApprovalChanged', $value); // Notify child components if needed
    }

    private function updateCertificationsFromCommittee(): void
    {
        if (empty($this->committee_code)) {
            $this->certifications = collect();

            return;
        }

        $committeeId = Committee::where('code', $this->committee_code)->value('id');
        if ($committeeId && $this->selectedFederationId) {
            // Get the federation's allowed licenses (include international licenses)
            $allowedLicenseIds = \Domain\Federations\Models\Federation::find($this->selectedFederationId)
                ->licenses()
                ->withoutGlobalScope(ExcludeInternationalScope::class)
                ->pluck('license_id')
                ->toArray();

            $this->certifications = Certification::where('committee_id', $committeeId)
                ->when(! empty($allowedLicenseIds), function ($query) use ($allowedLicenseIds) {
                    // Allow certifications with NULL license_id (instructor certifications don't require a license)
                    // or those with license_id in the federation's allowed licenses
                    $query->where(function ($q) use ($allowedLicenseIds) {
                        $q->whereIn('license_id', $allowedLicenseIds)
                            ->orWhereNull('license_id');
                    });
                })
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $this->certifications = collect();
        }
    }

    private function updateCertificationsFromInstructor(): void
    {
        if (empty($this->selectedFederationId) || ! $this->selectedDirector) {
            $this->certifications = collect();

            return;
        }

        $instructorCertifications = new GetCertificationsFromInstructorAction;
        $certifications = $instructorCertifications($this->selectedDirector, $this->selectedFederationId, $this->committee_code);

        if (empty($certifications) || $certifications->isEmpty()) {
            $this->certifications = collect();

            return;
        }

        // Filter by federation's allowed licenses (include international licenses)
        // Skip filtering for certifications with NULL license_id (e.g., instructor certifications)
        $allowedLicenseIds = \Domain\Federations\Models\Federation::find($this->selectedFederationId)
            ->licenses()
            ->withoutGlobalScope(ExcludeInternationalScope::class)
            ->pluck('license_id')
            ->toArray();

        if (! empty($allowedLicenseIds)) {
            $certifications = $certifications->filter(function ($certification) use ($allowedLicenseIds) {
                // Allow certifications with NULL license_id (instructor certifications don't require a license)
                return $certification->license_id === null || in_array($certification->license_id, $allowedLicenseIds);
            });
        }

        $this->filterCertificationsByCommittee($certifications);
    }

    private function filterCertificationsByCommittee(Collection $certifications): void
    {
        if (! empty($this->committee_code)) {
            $committeeId = Committee::where('code', $this->committee_code)->value('id');
            $this->certifications = $committeeId ? $certifications->where('committee_id', $committeeId) : collect();
        } else {
            $this->certifications = $certifications;
        }
    }

    // --- Final Submission ---

    public function submit(
        CreateCertificationAttributedAction $creator,
        CalculateCertificationPriceAction $calculatePriceAction
    ): void {
        $this->validateStep(1);
        $this->validateStep(2);

        $user = Auth::user();
        $federation = $user->federations()->first();

        $individualsWithTheCertification = [];
        $successfullyAttributedCount = 0; // Track successful attributions
        $successfullyAttributedNames = []; // Optional: track names if needed for summary
        $createdCertifications = []; // Track created certifications for payment document

        try {
            DB::beginTransaction();

            // Load certification with price calculation
            $certification = Certification::find($this->selectedCertificationId);
            if (! $certification) {
                throw new Exception(__('certifications.selected_certification_not_found'));
            }

            // Calculate price per individual certification using the new pricing model
            $pricePerCertification = 0;
            if (! $certification->isFree()) {
                // Use the selected price option (digital or digital_plus_card)
                $pricePerCertification = $certification->getPriceForOption($this->selectedPriceOption);
                // Apply tax if configured
                if ($certification->tax_percentage > 0) {
                    $pricePerCertification = $pricePerCertification * (1 + ($certification->tax_percentage / 100));
                }
            }

            $batchId = Str::uuid()->toString();

            foreach ($this->selectedStudentIds as $studentId) {
                $attributionData = [
                    'federation_id' => $this->selectedFederationId,
                    'entity_id' => $this->selectedSchoolId,
                    'certification_id' => $this->selectedCertificationId,
                    'individual_ids' => [$studentId], // Process one student at a time
                    'director_instructor_id' => $this->selectedDirectorId,
                    'assistant_instructor_ids' => $this->selectedAssistantIds,
                    'approved_by_federation' => $this->federationApprove,
                    'current_term_starts_at' => $this->issueDate,
                    'current_term_ends_at' => $this->expirationDate,
                    'notes' => $this->notes,
                    'activator_id' => $federation ? $federation->id : null,
                    'activator_type' => $federation ? \Domain\Federations\Models\Federation::class : null,
                    'code' => null,
                    'number' => null,
                    'activated_at' => null,
                    'international_code' => null,
                    'approve_without_slots' => true, // No more slots!
                    'national_code' => $this->studentNationalNumbers[$studentId] ?? null,
                    'price_option' => $this->selectedPriceOption,
                    'price_paid' => $pricePerCertification,
                    'batch_id' => $batchId,
                ];

                $certificationAttributedData = CertificationAttributedData::fromArray($attributionData);
                $result = $creator($certificationAttributedData, $this->actorType);

                // Check if the action indicated this individual already had the certification
                if (! empty($result['individualsWithTheCertification'])) {
                    $individualsWithTheCertification = array_merge($individualsWithTheCertification, $result['individualsWithTheCertification']);
                } else {
                    // Success - track the created certification
                    $successfullyAttributedCount++;

                    // Find the created certification for this individual
                    $createdCert = \Domain\Certifications\Models\CertificationAttributed::where('certification_id', $this->selectedCertificationId)
                        ->where('individual_id', $studentId)
                        ->where('federation_id', $this->selectedFederationId)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($createdCert) {
                        $createdCertifications[] = $createdCert;
                    }
                }
            }

            DB::commit();

            // Fire payment document events for paid certifications NOT in DirectorApproval state.
            // DirectorApproval flow handles its own payment event in ApproveCertificationByDirectorAction.
            // Only generate payment documents for non-federation actors - federations issuing certifications should never generate payments.
            if ($pricePerCertification > 0 && $this->actorType !== 'federation') {
                foreach ($createdCertifications as $createdCert) {
                    if ($createdCert->status_class !== DirectorApprovalCertificationAttributedState::class) {
                        $createdCert->load('certification');
                        event(new CertificationAttributedCreatedEvent($createdCert, $pricePerCertification));
                    }
                }
            }

            $totalStudentsSelected = count($this->selectedStudentIds);
            $alreadyHadCount = count($individualsWithTheCertification);

            // Determine overall status and message
            if ($successfullyAttributedCount > 0 && $alreadyHadCount > 0) {
                // Partial success
                $this->successMessage = __('certifications.certifications_attributed_successfully', [
                    'count' => $successfullyAttributedCount,
                    'already' => $alreadyHadCount,
                    'names' => implode(', ', $individualsWithTheCertification),
                ]);
                $this->showSuccessState = true;
            } elseif ($successfullyAttributedCount > 0 && $alreadyHadCount == 0) {
                // Complete success
                $this->successMessage = __('certifications.all_certifications_attributed_successfully', [
                    'count' => $successfullyAttributedCount,
                ]);
                $this->showSuccessState = true;
            } elseif ($successfullyAttributedCount == 0 && $alreadyHadCount > 0) {
                // No new attributions, all already had it
                $this->successMessage = __('certifications.no_new_certifications_attributed', [
                    'count' => $alreadyHadCount,
                    'names' => implode(', ', $individualsWithTheCertification),
                ]);
                $this->showSuccessState = true; // Still show the final screen
            } else {
                // This case means 0 selected or an issue before processing individuals
                $this->successMessage = __('certifications.processing_complete_no_individuals');
                $this->showSuccessState = false; // Don't show success state
                Log::warning('Certification attribution submit ended with 0 successful and 0 already had.', [
                    'selected_students' => $this->selectedStudentIds,
                    'user_id' => $user->id,
                ]);
            }

            // Fetch student names for logging (after commit, before logging)
            $selectedStudentNames = Individual::whereIn('id', $this->selectedStudentIds)
                ->pluck('name')
                ->implode(', ');

            // --- Activity Logging --- Should happen after commit and before any potential redirects/state clears
            if ($this->showSuccessState) { // Only log if the operation didn't fail with an exception
                activity('CertificationAttributed')
                    ->causedBy($user)
                    ->performedOn($this->selectedCertification) // Primary subject
                    ->event($successfullyAttributedCount > 0 ? 'attributed' : 'skipped') // Event type based on outcome
                    ->withProperties([
                        'federation_id' => $this->selectedFederationId,
                        'federation_name' => $this->selectedFederation?->name, // Optional: Add name for readability
                        'school_id' => $this->selectedSchoolId,
                        'school_name' => $this->selectedSchool?->name, // Optional
                        'certification_id' => $this->selectedCertificationId,
                        'certification_name' => $this->selectedCertification?->name, // Optional
                        'director_id' => $this->selectedDirectorId,
                        'assistant_ids' => $this->selectedAssistantIds,
                        'student_ids' => $this->selectedStudentIds,
                        'approved_by_federation' => $this->federationApprove,
                        'issue_date' => $this->issueDate,
                        'expiration_date' => $this->expirationDate,
                        'attributed_count' => $successfullyAttributedCount,
                        'skipped_count' => $alreadyHadCount,
                        'skipped_individual_names' => $individualsWithTheCertification, // Names already collected
                        'notes' => $this->notes,
                    ])
                    ->log(sprintf(
                        'Attributed %s to individuals (%s) via wizard: %d succeeded, %d skipped.',
                        $this->selectedCertification?->name ?? 'Unknown Certification',
                        $selectedStudentNames,
                        $successfullyAttributedCount,
                        $alreadyHadCount
                    ));
            }
            // --- End Activity Logging ---

            // Update Attribution Summary only if new certs were attributed
            if ($successfullyAttributedCount > 0) {
                $this->attributionSummary = [
                    'school' => $this->selectedSchool->name ?? '',
                    'certification' => $this->selectedCertification->name ?? '',
                    'studentCount' => $successfullyAttributedCount, // Show count of *newly* attributed
                    'issueDate' => $this->issueDate ? \Carbon\Carbon::parse($this->issueDate)->format('M d, Y') : '',
                    // Consider adding newly attributed student names if needed:
                    // 'students' => implode(', ', $successfullyAttributedNames),
                ];
            } else {
                $this->attributionSummary = []; // Clear summary if nothing new was attributed
            }

            // $this->showSuccessState = true; // Logic moved into conditional blocks above

        } catch (\Exception $e) {
            DB::rollBack();
            $this->showSuccessState = false; // Ensure success state is false on exception
            Notification::make()
                ->title(__('certifications.error_attributing_certification'))
                ->body($e->getMessage()) // Consider logging the full trace in production
                ->danger()
                ->send();
            // Log the detailed error
            Log::error('Error during certification attribution process.', [
                'user_id' => Auth::id(),
                'selected_students' => $this->selectedStudentIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Be cautious logging full traces in production logs
            ]);
        }
    }

    /**
     * Start a new attribution after successful submission
     */
    public function startNewAttribution(): void
    {
        $this->resetForm();
        $this->showSuccessState = false;
    }

    // --- Helper Methods ---

    private function loadFederationSchoolsOptions(): void
    {
        if ($this->selectedFederationId && $this->actorType === 'federation') {
            $this->federationSchoolsOptions = Entity::whereHas('federations', fn ($q) => $q->where('federation_id', $this->selectedFederationId))
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        } else {
            $this->federationSchoolsOptions = [];
        }
    }

    protected function validateStep(int $stepToValidate): void
    {
        $rulesToValidate = [];

        if ($stepToValidate === 1) {
            if ($this->actorType === 'federation') {
                $rulesToValidate = [
                    'selectedFederationId' => 'required|integer|exists:federation,id',
                    'selectedSchoolId' => ($this->committee_code !== 'sport') ? 'nullable|integer|exists:entity,id' : 'nullable', // School is optional, or not applicable for sport committee
                    // Director ID nullability is handled by the custom check below for federations
                ];
                // Custom logic check for advancing from Step 1 for federations
                if (empty($this->selectedDirectorId) && ! $this->federationApprove) {
                    throw ValidationException::withMessages([
                        'step1_requirement' => __('certifications.step1_requirement'),
                    ]);
                }
            } elseif ($this->actorType === 'entity') {
                if ($this->federationApprove) {
                    throw ValidationException::withMessages([
                        'federationApprove' => __('certifications.entity_no_federation_approval'),
                    ]);
                }
                $rulesToValidate = [
                    'selectedFederationId' => 'required|integer|exists:federation,id', // Federation context still needed
                    'selectedSchoolId' => 'required|integer|exists:entity,id',      // Entity is fixed and required
                    'selectedDirectorId' => 'required|string|exists:individual,id',    // Director is mandatory
                ];
                // Custom logic for entity (director is always required)
                if (empty($this->selectedDirectorId)) {
                    throw ValidationException::withMessages([
                        'selectedDirectorId' => __('certifications.director_required_for_entity'),
                    ]);
                }
            }
        } elseif ($stepToValidate === 2) {
            $baseStep2Rules = [
                'selectedStudentIds' => 'required|array|min:1',
                'selectedStudentIds.*' => 'string|exists:individual,id',
                'selectedAssistantIds.*' => 'nullable|string|exists:individual,id',
                'selectedCertificationId' => 'required|integer|exists:certification,id',
                'notes' => 'nullable|string|max:1000',
                'confirmationAccepted' => 'accepted',
            ];

            if ($this->actorType === 'federation') {
                $rulesToValidate = array_merge($baseStep2Rules, [
                    'selectedDirectorId' => $this->federationApprove ? 'nullable|string|exists:individual,id' : 'required|string|exists:individual,id',
                    'issueDate' => 'nullable|date',
                    'expirationDate' => 'nullable|date|after_or_equal:issueDate',
                    'studentNationalNumbers' => 'required|array',
                    'studentNationalNumbers.*' => 'required|string|max:255',
                ]);
            } elseif ($this->actorType === 'entity') {
                $rulesToValidate = array_merge($baseStep2Rules, [
                    'selectedDirectorId' => 'required|string|exists:individual,id',
                ]);
            }
        } else {
            // For any other steps, define rules or leave empty if not needed.
            // Default to empty if not step 1 or 2.
            $rulesToValidate = [];
        }

        try {
            // Run standard field validation first (if rules are defined)
            if (! empty($rulesToValidate)) {
                $this->validate($rulesToValidate, [
                    'confirmationAccepted.accepted' => __('certifications.confirmation_required'),
                ]);
            }
            // Custom logic for step 1 (federation) is already handled above by throwing ValidationException.
            // Custom logic for step 1 (entity) regarding director presence is also handled.

        } catch (ValidationException $e) {
            // Log the specific validation errors
            Log::warning('Validation failed in CertificationAttributionWizard', [
                'step' => $stepToValidate,
                'actorType' => $this->actorType,
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);

            // Prepare a more detailed message for the notification
            $errorMessages = collect($e->errors())->flatten()->implode(' ');
            Notification::make()
                ->title(__('certifications.validation_error'))
                ->body(__('certifications.please_correct_the_following') . $errorMessages) // Show actual errors
                ->warning()
                ->send();
            throw $e; // Re-throw to stop execution and let Filament display errors
        }
    }

    protected function resetStepData(int $resetStartingFromStep): void
    {
        if ($resetStartingFromStep <= 2) { // Affects step 2 data (director, assistants, students)
            $this->selectedDirectorId = null;
            $this->selectedDirector = null;
            $this->selectedAssistantIds = [];
            $this->selectedAssistants = collect();
            $this->selectedStudentIds = [];
            $this->selectedStudents = collect();
            // Certifications list depends on director or federation approval, so ensure it's updated.
            // If director is reset, updateCertificationsFromInstructor will handle clearing it if called.
            // If federationApprove is true, updateCertificationsFromCommittee will be called.
            // Explicitly clear here if director was the source.
            if (! $this->federationApprove) {
                $this->certifications = collect();
            }
        }

        if ($resetStartingFromStep <= 3) { // Affects step 3 data (certification selection, dates, notes)
            // Note: Step 3 form items are in getStep2FormSchema in the wizard code.
            $this->selectedCertificationId = null;
            $this->selectedCertification = null;
            $this->issueDate = null;
            $this->expirationDate = null;
            $this->notes = null;
        }
    }

    protected function resetForm(): void
    {
        $this->selectedFederationId = null;
        $this->selectedSchoolId = null;
        $this->selectedFederation = null;
        $this->selectedSchool = null;
        $this->resetStepData(2); // Reset all subsequent steps
        $this->step = 1;
        $this->dispatch('stepChanged', $this->step);
    }

    // --- Filament Form Definition ---

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchemaForCurrentStep())
            ->extraAttributes(['class' => 'card overflow-visible']);
        // ->statePath('formData'); // Remove this line
        // If state needs to be kept separate per step, more complex state management needed.
        // For now, we bind directly to public properties, so statePath might not be strictly needed
    }

    protected function getFormSchemaForCurrentStep(): array
    {
        return match ($this->step) {
            1 => $this->getStep1FormSchema(),
            2 => [...$this->getStep2FormSchema(), ...$this->getStep3FormSchema()],
            default => [],
        };
    }

    protected function getStep1FormSchema(): array
    {
        $fields = [];

        if ($this->actorType === 'federation') {
            if ($this->committee_code !== 'sport') {
                $fields[] = Select::make('selectedSchoolId')
                    ->label(__('certifications.school'))
                    ->options(fn (): array => $this->federationSchoolsOptions) // Use cached options
                    ->searchable()
                    ->native(false)
                    ->live();
            }
            $fields[] = Checkbox::make('federationApprove')
                ->label($this->committee_code === 'sport' ? __('certifications.approved_by_federation') : __('certifications.approved_by_ntc'))
                ->helperText(__('certifications.select_without_director_help'))
                ->live()
                ->reactive();
        }

        // For 'entity' actor, school is pre-filled (not a form field here), and federationApprove is not applicable.
        // Director Select is handled by a separate table component included in the blade view, not part of this schema directly.
        return $fields;
    }

    protected function getStep2FormSchema(): array
    {
        return [
            // Add any form fields needed for step 2 (students/roles selection)
            // We're handling most of this with custom components in the view
        ];
    }

    protected function getStep3FormSchema(): array
    {
        $schema = [
            Select::make('selectedCertificationId')
                ->label(__('certifications.certification'))
                ->options(function (): array {
                    return $this->certifications->pluck('name', 'id')->toArray();
                })
                ->searchable()
                ->required()
                ->disabled(! $this->selectedDirectorId && ! $this->federationApprove),
        ];

        if ($this->actorType === 'federation') {
            $schema[] = DatePicker::make('issueDate')
                ->label(__('certifications.issue_date'))
                ->helperText(__('certifications.whats_the_start_date'));
            $schema[] = DatePicker::make('expirationDate')
                ->label(__('certifications.expiration_date'))
                ->helperText(__('certifications.when_certification_expires'))
                ->afterOrEqual('issueDate');
        }

        $schema[] = Textarea::make('notes')
            ->label(__('certifications.notes'))
            ->helperText(__('certifications.add_notes_if_needed'))
            ->rows(2)
            ->maxLength(1000);

        return $schema;
    }

    // --- Rendering ---
    public function render(): View
    {
        return view('livewire.certifications.certification-attribution-wizard');
    }
}
