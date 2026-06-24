<?php

namespace App\Livewire;

use App\Services\DivingCertificationRequirementService;
use Domain\Diving\Models\DivingEntityTechnicalDirector;
use Domain\Diving\States\AssignedDivingTechnicalDirectorState;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Actions\PurchaseLicenseAction;
use Domain\Licenses\Actions\ValidateLicenseDocumentRequirementsAction;
use Domain\Licenses\Models\License;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class DivingLicenseRequestWizard extends Component
{
    // Wizard state
    public $currentStep = 1;
    public $totalSteps = 5;

    // Form data
    public $licenseId;
    public $license;
    public $entity;
    public $selectedDirectorIds = []; // Simplified: just store selected IDs
    public $notes = '';
    public $selectedCertificationSystems = [];

    // Available options
    public $availableLicenses = [];
    public $potentialDirectors = [];
    public $filteredDirectors = [];
    public $certificationSystems = [];

    // Search for directors table
    public $directorSearch = '';

    // Certification requirements
    public $requiredCertificationLevels = [];
    public $certificationRequirementText = '';

    // UI state (removed modal-related properties)

    // Services
    protected ?DivingCertificationRequirementService $certificationRequirementService = null;

    protected $rules = [
        'licenseId' => 'required|exists:license,id',
        'selectedDirectorIds' => 'required|array|min:1',
        'selectedDirectorIds.*' => 'required|exists:individual,id',
        'notes' => 'nullable|string|max:500',
    ];

    protected function messages(): array
    {
        return [
            'licenseId.required' => __('diving.validation.license_type_required'),
            'selectedCertificationSystems.required' => __('diving.validation.certification_system_required'),
            'selectedDirectorIds.required' => __('diving.validation.director_required'),
            'selectedDirectorIds.*.required' => __('diving.validation.valid_professional_required'),
        ];
    }

    public function boot()
    {
        // Boot is called before every action, ensuring service is always available
        $this->certificationRequirementService = app(DivingCertificationRequirementService::class);

        // Ensure entity is loaded if user is authenticated
        // Use session to persist entity ID across requests
        if (! $this->entity && auth()->check()) {
            $entityId = session('diving_wizard_entity_id');
            if ($entityId) {
                $this->entity = auth()->user()->entities()->where('entity.id', $entityId)->first();
            }

            if (! $this->entity) {
                $this->entity = auth()->user()->entities()->first();
                if ($this->entity) {
                    session(['diving_wizard_entity_id' => $this->entity->id]);
                }
            }
        }

        // Don't refresh directors list on every request as it resets the filtering
        // Directors are loaded once in mount() and filtered based on certification systems
    }

    public function mount()
    {
        $this->certificationSystems = config('diving.certification_systems');
        $this->certificationRequirementService = app(DivingCertificationRequirementService::class);
        $this->entity = auth()->user()->entities()->first();

        // Store entity ID in session for persistence
        if ($this->entity) {
            session(['diving_wizard_entity_id' => $this->entity->id]);
        }

        // Get available diving entity licenses (DIVINGSERVICES = non-international diving services)
        $this->availableLicenses = License::where('committee_id', function ($query) {
            $query->select('id')
                ->from('committee')
                ->where('code', 'DIVINGSERVICES');
        })
            ->where('type_id', function ($query) {
                $query->select('id')
                    ->from('license_type')
                    ->where('name', 'entity');
            })
            ->get();

        // Get potential technical directors
        $this->loadPotentialDirectors();
    }

    public function loadPotentialDirectors()
    {
        // Only load individuals who are diving professionals invited by this entity
        if (! $this->entity) {
            $this->potentialDirectors = collect();

            return;
        }

        $this->potentialDirectors = Individual::whereHas('professionalRoleEntities', function ($query) {
            $query->where('entity_id', $this->entity->id)
                ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class)
                ->whereHas('professionalRole', function ($roleQuery) {
                    $roleQuery->where('role', 'DIVINGPROFESSIONAL')
                        ->whereHas('licenses', function ($q) {
                            $q->whereHas('committee', function ($q2) {
                                $q2->where('code', 'DIVINGSERVICES');
                            });
                        });
                });
        })
            // Must have diving certifications (DIVING = international, DIVINGSERVICES = non-international)
            ->where(function ($q) {
                $q->whereHas('certificationsAttributed', function ($cq) {
                    $cq->certificationAttributedStatus('active')
                        ->whereHas('certification', function ($certQuery) {
                            $certQuery->whereHas('committee', function ($commQuery) {
                                $commQuery->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
                            });
                        });
                })
                    ->orWhereHas('divingProfessionalCertifications', function ($dq) {
                        $dq->active();
                    });
            })
            ->get();

        // Don't automatically set filtered directors - wait for certification system selection
        // This prevents showing directors with wrong certification systems
        $this->filteredDirectors = collect();
    }

    public function updatedSelectedCertificationSystems()
    {
        // Filter directors based on selected certification systems
        // This is the primary filtering mechanism - the user selects which certification systems they accept

        if (empty($this->selectedCertificationSystems)) {
            $this->filteredDirectors = collect();

            return;
        }

        if (! $this->entity) {
            $this->filteredDirectors = collect();

            return;
        }

        $this->filteredDirectors = Individual::whereHas('professionalRoleEntities', function ($query) {
            $query->where('entity_id', $this->entity->id)
                ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class)
                ->whereHas('professionalRole', function ($roleQuery) {
                    $roleQuery->where('role', 'DIVINGPROFESSIONAL')
                        ->whereHas('licenses', function ($q) {
                            $q->whereHas('committee', function ($q2) {
                                $q2->where('code', 'DIVINGSERVICES');
                            });
                        });
                });
        })
            ->where(function ($q) {
                // Filter by specific certification systems
                if (count($this->selectedCertificationSystems) === 1 && in_array('CMAS', $this->selectedCertificationSystems)) {
                    // For international-only selection, show only professionals with international diving certifications
                    // Include both DIVING (international) and DIVINGSERVICES (non-international) committees
                    $q->whereHas('certificationsAttributed', function ($cq) {
                        $cq->certificationAttributedStatus('active')
                            ->whereHas('certification', function ($certQ) {
                                $certQ->whereHas('committee', function ($commQ) {
                                    $commQ->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
                                });
                            });
                    });
                } else {
                    // For mixed or non-international selections, filter by specific systems
                    $q->where(function ($subQ) {
                        foreach ($this->selectedCertificationSystems as $system) {
                            if ($system === 'CMAS') {
                                // Include both DIVING (international) and DIVINGSERVICES (non-international) committees
                                $subQ->orWhereHas('certificationsAttributed', function ($cq) {
                                    $cq->certificationAttributedStatus('active')
                                        ->whereHas('certification', function ($certQ) {
                                            $certQ->whereHas('committee', function ($commQ) {
                                                $commQ->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
                                            });
                                        });
                                });
                            } else {
                                $subQ->orWhereHas('divingProfessionalCertifications', function ($dq) use ($system) {
                                    $dq->active()->where('certification_system', $system);
                                });
                            }
                        }
                    });
                }
            })
            ->get();

        // Check if no directors were found for selected systems
        if ($this->filteredDirectors->isEmpty()) {
            $this->addError('filteredDirectors', __('diving.no_professionals_found_for_systems'));
        }
    }

    public function updatedLicenseId($value)
    {
        if ($value) {
            $this->license = $this->availableLicenses->find($value);
            $this->loadCertificationRequirements();
            // Don't filter directors here - let the certification systems selection handle it
        }
    }

    public function loadCertificationRequirements()
    {
        if ($this->license) {
            $this->requiredCertificationLevels = $this->certificationRequirementService
                ->getRequiredCertificationLevels($this->license)
                ->toArray();

            $this->certificationRequirementText = $this->certificationRequirementService
                ->getFormattedRequirementsText($this->license);
        }
    }

    // Removed filterDirectorsByCertificationRequirements as it was filtering by license requirements
    // instead of by the user's selected certification systems

    /**
     * Check if the entity has all required documents for the selected license.
     *
     * This method validates that the entity has provided all official documents
     * required by the selected license type. It uses the ValidateLicenseDocumentRequirementsAction
     * to check against the license's required_document_types configuration.
     *
     * @return array{is_valid: bool, missing_documents: array<string>, error_messages: array<string>}
     */
    public function checkRequiredEntityDocuments(): array
    {
        if (! $this->entity || ! $this->license) {
            return [
                'is_valid' => false,
                'missing_documents' => [],
                'error_messages' => [__('diving.entity_and_license_required')],
            ];
        }

        // Use the ValidateLicenseDocumentRequirementsAction to check requirements
        $validationResult = app(ValidateLicenseDocumentRequirementsAction::class)
            ->__invoke($this->license, $this->entity);

        // Format error messages for display
        $errorMessages = [];
        foreach ($validationResult['errors'] as $error) {
            $errorMessages[] = $error['message'];
        }

        return [
            'is_valid' => $validationResult['is_valid'],
            'missing_documents' => $validationResult['missing_documents'],
            'error_messages' => $errorMessages,
        ];
    }

    // Methods for managing selected directors
    public function toggleDirector($directorId)
    {
        if (in_array($directorId, $this->selectedDirectorIds)) {
            $this->selectedDirectorIds = array_values(array_diff($this->selectedDirectorIds, [$directorId]));
        } else {
            $this->selectedDirectorIds[] = $directorId;
        }
    }

    public function isDirectorSelected($directorId)
    {
        return in_array($directorId, $this->selectedDirectorIds);
    }

    public function updatedDirectorSearch()
    {
        $this->filterDirectorsBySearch();
    }

    public function filterDirectorsBySearch()
    {
        if (empty($this->directorSearch)) {
            // If no search, re-apply the certification system filter
            $this->updatedSelectedCertificationSystems();
        } else {
            // Apply search filter within the certification system filtered list
            $search = '%' . $this->directorSearch . '%';

            // First get the IDs of directors filtered by certification systems
            // We need to maintain the certification system filter
            if (empty($this->selectedCertificationSystems)) {
                $this->filteredDirectors = collect();

                return;
            }

            if (! $this->entity) {
                $this->filteredDirectors = collect();

                return;
            }

            // Re-apply the certification system filter with search
            $this->filteredDirectors = Individual::whereHas('professionalRoleEntities', function ($query) {
                $query->where('entity_id', $this->entity->id)
                    ->where('status_class', \Domain\Entities\States\ActiveEntityProfessionalRoleState::class)
                    ->whereHas('professionalRole', function ($roleQuery) {
                        $roleQuery->where('role', 'DIVINGPROFESSIONAL')
                            ->whereHas('licenses', function ($q) {
                                $q->whereHas('committee', function ($q2) {
                                    $q2->where('code', 'DIVINGSERVICES');
                                });
                            });
                    });
            })
                ->where(function ($q) {
                    // Apply certification system filter
                    if (count($this->selectedCertificationSystems) === 1 && in_array('CMAS', $this->selectedCertificationSystems)) {
                        // Include both DIVING (international) and DIVINGSERVICES (non-international) committees
                        $q->whereHas('certificationsAttributed', function ($cq) {
                            $cq->certificationAttributedStatus('active')
                                ->whereHas('certification', function ($certQ) {
                                    $certQ->whereHas('committee', function ($commQ) {
                                        $commQ->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
                                    });
                                });
                        });
                    } else {
                        $q->where(function ($subQ) {
                            foreach ($this->selectedCertificationSystems as $system) {
                                if ($system === 'CMAS') {
                                    // Include both DIVING (international) and DIVINGSERVICES (non-international) committees
                                    $subQ->orWhereHas('certificationsAttributed', function ($cq) {
                                        $cq->certificationAttributedStatus('active')
                                            ->whereHas('certification', function ($certQ) {
                                                $certQ->whereHas('committee', function ($commQ) {
                                                    $commQ->whereIn('code', ['DIVING', 'DIVINGSERVICES']);
                                                });
                                            });
                                    });
                                } else {
                                    $subQ->orWhereHas('divingProfessionalCertifications', function ($dq) use ($system) {
                                        $dq->active()->where('certification_system', $system);
                                    });
                                }
                            }
                        });
                    }
                })
                ->where(function ($q) use ($search) {
                    // Apply search filter
                    $q->where('name', 'like', $search)
                        ->orWhere('surname', 'like', $search)
                        ->orWhere('member_number', 'like', $search)
                        ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", [$search]);
                })
                ->get();
        }
    }

    public function goToStep($step)
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        } elseif ($step > $this->currentStep) {
            if ($this->validateCurrentStep()) {
                $this->currentStep = $step;
            }
        }
    }

    public function nextStep()
    {
        // Clear any previous errors
        $this->resetErrorBag();

        if ($this->validateCurrentStep()) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        $this->currentStep--;
    }

    /**
     * Validate the current wizard step before proceeding to the next.
     *
     * Performs step-specific validation including license selection,
     * certification systems, technical directors, and document requirements.
     *
     * @return bool True if validation passes, false otherwise
     */
    public function validateCurrentStep(): bool
    {
        switch ($this->currentStep) {
            case 1: // License selection
                $this->validate(['licenseId' => 'required']);

                return true;

            case 2: // Certification systems selection
                $this->validate(['selectedCertificationSystems' => 'required|array|min:1']);

                return true;

            case 3: // Technical directors
                try {
                    // Check if we have at least one director selected
                    if (empty($this->selectedDirectorIds)) {
                        $this->addError('selectedDirectorIds', __('diving.please_select_certification_systems_first'));

                        return false;
                    }

                    $this->validate([
                        'selectedDirectorIds' => 'required|array|min:1',
                        'selectedDirectorIds.*' => 'required|exists:individual,id',
                    ]);

                    // Validate certification requirements
                    if (! $this->validateSelectedDirectorCertifications()) {
                        return false;
                    }

                    // Check for required entity documents
                    $documentValidation = $this->checkRequiredEntityDocuments();
                    if (! $documentValidation['is_valid']) {
                        // Add specific error messages for each missing document
                        foreach ($documentValidation['error_messages'] as $errorMessage) {
                            $this->addError('entity_documents', $errorMessage);
                        }

                        return false;
                    }

                    return true;
                } catch (\Illuminate\Validation\ValidationException $e) {
                    // Validation errors are already set by validate()
                    return false;
                }

            case 4: // Notes
                return true;

            default:
                return true;
        }
    }

    /**
     * Validate that selected technical directors have certifications in the selected systems.
     *
     * This validates that the selected directors actually have certifications
     * in at least one of the selected certification systems.
     *
     * @return bool True if all directors have appropriate certifications, false otherwise
     */
    protected function validateSelectedDirectorCertifications(): bool
    {
        // We trust that if they appear in the filtered list, they have the right certifications
        // since the filtering is based on certification systems
        // Just validate they exist and are valid IDs
        foreach ($this->selectedDirectorIds as $directorId) {
            $individual = Individual::find($directorId);

            if (! $individual) {
                $this->addError('selectedDirectorIds', __('diving.invalid_director_selected'));

                return false;
            }

            // Verify the individual is in our filtered list (has the right certifications)
            if (! $this->filteredDirectors->contains('id', $directorId)) {
                $this->addError('selectedDirectorIds',
                    __('diving.director_not_qualified', [
                        'name' => $individual->name,
                    ]));

                return false;
            }
        }

        return true;
    }

    /**
     * Submit the diving license request after all validations pass.
     *
     * This method performs final validation, creates the license request,
     * and assigns technical directors. It ensures all required documents
     * are present before processing the request.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    public function submitRequest()
    {
        $this->validate();

        // Check for required entity documents before submission
        $documentValidation = $this->checkRequiredEntityDocuments();
        if (! $documentValidation['is_valid']) {
            // Add all missing document errors
            foreach ($documentValidation['error_messages'] as $errorMessage) {
                $this->addError('submit', $errorMessage);
            }

            return;
        }

        try {
            DB::beginTransaction();

            // Create license request using existing action
            $purchaseAction = app(PurchaseLicenseAction::class);
            $licenseAttributed = $purchaseAction($this->license, $this->entity);

            // Create technical director assignments (directly assigned since they were selected)
            foreach ($this->selectedDirectorIds as $directorId) {
                $individual = Individual::findOrFail($directorId);

                // Get the actual certification systems this director has
                $directorCertSystems = $this->getDirectorCertificationSystems($directorId);

                // Filter to only include systems that were selected by the entity
                $relevantSystems = array_intersect($directorCertSystems, $this->selectedCertificationSystems);

                // Create the technical director assignment as assigned
                $assignment = DivingEntityTechnicalDirector::create([
                    'entity_id' => $this->entity->id,
                    'individual_id' => $individual->id,
                    'license_attributed_id' => $licenseAttributed->id,
                    'license_id' => $this->license->id,
                    'certification_systems' => array_values($relevantSystems), // Use only relevant systems
                    'message' => $this->notes,
                    'status_class' => AssignedDivingTechnicalDirectorState::class,
                    'assigned_at' => now(),
                ]);

                // Log the assignment
                activity('diving_license')
                    ->performedOn($licenseAttributed)
                    ->withProperties([
                        'director_id' => $individual->id,
                        'director_name' => $individual->name . ' ' . $individual->surname,
                        'certification_systems' => array_values($relevantSystems), // Use only relevant systems
                    ])
                    ->log('Technical director assigned to diving license');
            }

            DB::commit();

            session()->flash('success', __('diving.license_request_submitted_successfully'));

            return redirect()->route('entity.diving_licenses.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit diving license request: ' . $e->getMessage());

            // Handle document validation errors
            if (strpos($e->getMessage(), 'document') !== false || strpos($e->getMessage(), 'Document') !== false) {
                // Re-validate to get specific missing documents
                $documentValidation = $this->checkRequiredEntityDocuments();
                if (! $documentValidation['is_valid']) {
                    foreach ($documentValidation['error_messages'] as $errorMessage) {
                        $this->addError('submit', $errorMessage);
                    }
                } else {
                    $this->addError('submit', __('diving.failed_to_submit_request'));
                }
            } else {
                Log::error('Diving license request failed', ['error' => $e->getMessage()]);
                $this->addError('submit', __('diving.failed_to_submit_request'));
            }
        }
    }

    /**
     * Get the actual certification systems that a director possesses.
     *
     * This method queries the database to find what certification systems
     * the director actually has, not what the entity selected.
     *
     * @param  int  $directorId  The ID of the director
     * @return array Array of certification system names the director has
     */
    public function getDirectorCertificationSystems($directorId): array
    {
        $director = Individual::find($directorId);
        if (! $director) {
            return [];
        }

        $certificationSystems = [];

        // Check for international certifications (DIVINGSERVICES only - this wizard is for entity diving services)
        $hasCmasCertifications = $director->certificationsAttributed()
            ->certificationAttributedStatus('active')
            ->whereHas('certification.committee', function ($q) {
                $q->where('code', 'DIVINGSERVICES');
            })
            ->exists();

        if ($hasCmasCertifications) {
            $certificationSystems[] = 'CMAS';
        }

        // Check for other diving professional certifications (PADI, SSI, etc.)
        $otherCertifications = $director->divingProfessionalCertifications()
            ->active()
            ->get()
            ->pluck('certification_system')
            ->unique()
            ->toArray();

        $certificationSystems = array_merge($certificationSystems, $otherCertifications);

        return array_unique($certificationSystems);
    }

    /**
     * Get only the certification systems that have assigned technical directors.
     *
     * This method filters the selected certification systems to only include
     * those that have at least one technical director assigned.
     *
     * @return array Array of certification systems with assigned directors
     */
    public function getActualSelectedCertificationSystems(): array
    {
        $systemsWithDirectors = [];

        // Check each selected director's certification systems
        foreach ($this->selectedDirectorIds as $directorId) {
            $directorSystems = $this->getDirectorCertificationSystems($directorId);

            // Add each system that was originally selected by the user
            foreach ($directorSystems as $system) {
                if (in_array($system, $this->selectedCertificationSystems) && ! in_array($system, $systemsWithDirectors)) {
                    $systemsWithDirectors[] = $system;
                }
            }
        }

        return array_unique($systemsWithDirectors);
    }

    public function getStepTitle()
    {
        switch ($this->currentStep) {
            case 1:
                return __('diving.select_license_type');
            case 2:
                return __('diving.select_certification_systems');
            case 3:
                return __('diving.technical_directors');
            case 4:
                return __('diving.additional_notes');
            case 5:
                return __('diving.review_and_submit');
            default:
                return '';
        }
    }

    public function render()
    {
        return view('livewire.diving-license-request-wizard');
    }
}
