<?php

namespace App\Livewire\EvtEvents;

use App\Exports\RefereeEnrollmentsExport;
use App\Livewire\EvtEvents\Concerns\HasEnrollmentTableHelpers;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\EvtEvents\Actions\SaveRefereeEvaluationAction;
use Domain\EvtEvents\Actions\SyncRefereeFunctionAssignmentsAction;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\EventRole;
use Domain\EvtEvents\Models\RefereeEnrollment;
use Domain\EvtEvents\Models\RefereeFunctionAssignment;
use Domain\EvtEvents\States\ActiveRefereeEnrollmentState;
use Domain\EvtEvents\States\PendingRefereeEnrollmentState;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class JudgeEnrollments extends Component implements HasForms, HasTable
{
    use HasEnrollmentTableHelpers;
    use InteractsWithForms;
    use InteractsWithTable;

    public Event $event;

    public function mount(Event $event): void
    {
        $this->authorizeChiefJudge($event);
        $this->event = $event;
    }

    protected function authorizeChiefJudge(Event $event): void
    {
        $user = Auth::user();

        abort_unless($user->individual, 403, __('events.no_individual_profile'));

        $hasRole = EventRole::where('event_id', $event->id)
            ->where('individual_id', $user->individual->id)
            ->where('role', EventRole::ROLE_CHIEF_JUDGE)
            ->exists();

        abort_unless($hasRole, 403, __('events.not_chief_judge'));
    }

    protected function getEnrollmentQueryForType(string $enrollmentType): ?Builder
    {
        return null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getRefereesQuery())
            ->columns([
                TextColumn::make('individual.full_name')
                    ->label(__('events.referee'))
                    ->sortable(['name', 'surname'])
                    ->searchable(['name', 'surname'])
                    ->weight('medium'),

                TextColumn::make('individual.birthdate')
                    ->label(__('events.birth_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                $this->makeGenderColumn(),

                TextColumn::make('individual.member_number')
                    ->label(__('events.member_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('assigned_functions')
                    ->label(__('events.functions_performed'))
                    ->getStateUsing(function ($record) {
                        $assignments = $record->refereeFunctionAssignments;

                        if ($assignments->isEmpty()) {
                            return __('events.no_function_assigned');
                        }

                        return $assignments->map(fn ($a) => $a->function_name)->implode(', ');
                    })
                    ->badge()
                    ->separator(',')
                    ->color(fn (string $state): string => $state === __('events.no_function_assigned') ? 'gray' : 'info'),

                TextColumn::make('competition_days_display')
                    ->label(__('events.competition_days'))
                    ->getStateUsing(fn ($record) => $record->refereeFunctionAssignments->first()?->competition_days ?? '-')
                    ->alignCenter(),

                TextColumn::make('number_of_games_display')
                    ->label(__('events.number_of_games'))
                    ->getStateUsing(fn ($record) => $record->refereeFunctionAssignments->first()?->number_of_games ?? '-')
                    ->alignCenter()
                    ->visible($this->event->sport?->sport_type === 'team'),

                TextColumn::make('enrolled_function')
                    ->label(__('events.enrolled_function'))
                    ->getStateUsing(function ($record) {
                        $selectAttribute = $record->attributes
                            ->first(fn ($attr) => $attr->attribute && $attr->attribute->attribute_type === 'SELECT');

                        return $selectAttribute?->value ?: '-';
                    })
                    ->badge()
                    ->color(fn (string $state): string => $state === '-' ? 'gray' : 'primary'),

                TextColumn::make('evaluation')
                    ->label(__('events.evaluation'))
                    ->formatStateUsing(fn ($state): string => $state ? $state . ' - ' . self::getEvaluationLabel((int) $state) : '-')
                    ->alignCenter(),
            ])
            ->actions([
                Action::make('manageFunctions')
                    ->iconButton()
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->tooltip(__('events.assign_referee_function'))
                    ->modalHeading(fn ($record) => __('events.assign_referee_function') . ' - ' . $record->individual->full_name)
                    ->fillForm(function ($record) {
                        $firstAssignment = $record->refereeFunctionAssignments
                            ->where('event_id', $this->event->id)
                            ->first();

                        return [
                            'functions' => $record->refereeFunctionAssignments
                                ->where('event_id', $this->event->id)
                                ->pluck('function_text')
                                ->toArray(),
                            'notes' => $firstAssignment?->notes,
                            'competition_days' => $firstAssignment?->competition_days,
                            'number_of_games' => $firstAssignment?->number_of_games,
                        ];
                    })
                    ->form($this->getFunctionFormSchema())
                    ->action(function (array $data, $record): void {
                        $this->syncFunctions($record, $data);
                    })
                    ->modalWidth('md'),

                Action::make('manageEvaluation')
                    ->iconButton()
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->tooltip(__('events.evaluation'))
                    ->modalHeading(fn ($record) => __('events.evaluation') . ' - ' . $record->individual->full_name)
                    ->fillForm(fn ($record) => [
                        'evaluation' => $record->evaluation,
                        'evaluation_notes' => $record->evaluation_notes,
                    ])
                    ->form($this->getEvaluationFormSchema())
                    ->action(function (array $data, $record): void {
                        $this->saveEvaluation($record, $data);
                    })
                    ->modalWidth('md'),

                Action::make('togglePresence')
                    ->iconButton()
                    ->icon(fn ($record): string => $this->isRefereePresent($record) ? 'heroicon-s-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn ($record): string => $this->isRefereePresent($record) ? 'success' : 'warning')
                    ->tooltip(fn ($record): string => $this->isRefereePresent($record) ? __('events.mark_absent') : __('events.mark_present'))
                    ->action(function ($record): void {
                        $this->togglePresence($record);
                    }),
            ])
            ->defaultSort('individual.full_name', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function getFunctionFormSchema(): array
    {
        $options = $this->getAvailableFunctionOptions();
        $isTeamSport = $this->event->sport?->sport_type === 'team';

        $schema = [
            CheckboxList::make('functions')
                ->label(__('events.function'))
                ->options($options)
                ->columns(1)
                ->required(),

            Textarea::make('notes')
                ->label(__('events.notes'))
                ->placeholder(__('events.additional_notes'))
                ->maxLength(1000)
                ->rows(3),

            TextInput::make('competition_days')
                ->label(__('events.competition_days'))
                ->numeric()
                ->minValue(0)
                ->maxValue(365),

            TextInput::make('number_of_games')
                ->label(__('events.number_of_games'))
                ->numeric()
                ->minValue(0)
                ->maxValue(999)
                ->visible($isTeamSport),
        ];

        return $schema;
    }

    protected function getEvaluationFormSchema(): array
    {
        return [
            Select::make('evaluation')
                ->label(__('events.evaluation'))
                ->options(self::getEvaluationOptions())
                ->placeholder('-'),

            Textarea::make('evaluation_notes')
                ->label(__('events.evaluation_notes'))
                ->placeholder(__('events.additional_notes'))
                ->maxLength(1000)
                ->rows(3),
        ];
    }

    public static function getEvaluationOptions(): array
    {
        return [
            1 => '1 - ' . __('events.evaluation_insufficient'),
            2 => '2 - ' . __('events.evaluation_regular_flaws'),
            3 => '3 - ' . __('events.evaluation_regular'),
            4 => '4 - ' . __('events.evaluation_excellent'),
            5 => '5 - ' . __('events.evaluation_high_prestige'),
        ];
    }

    public static function getEvaluationLabel(int $value): string
    {
        return match ($value) {
            1 => __('events.evaluation_insufficient'),
            2 => __('events.evaluation_regular_flaws'),
            3 => __('events.evaluation_regular'),
            4 => __('events.evaluation_excellent'),
            5 => __('events.evaluation_high_prestige'),
            default => '-',
        };
    }

    protected function getAvailableFunctionOptions(): array
    {
        $options = [];

        $this->event->refereeAttributes()
            ->where('attribute_type', 'SELECT')
            ->get()
            ->each(function ($attribute) use (&$options) {
                $data = $attribute->attribute_data;

                if (is_array($data)) {
                    foreach ($data as $option) {
                        $options[$option] = $option;
                    }
                }
            });

        return $options;
    }

    protected function syncFunctions($record, array $data): void
    {
        try {
            app(SyncRefereeFunctionAssignmentsAction::class)->execute(
                $this->event,
                $record,
                Auth::user()->individual,
                $data
            );

            Notification::make()
                ->title(__('events.function_assigned'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error syncing referee functions: ' . $e->getMessage());

            Notification::make()
                ->title(__('common.error_occurred'))
                ->danger()
                ->send();
        }
    }

    protected function saveEvaluation($record, array $data): void
    {
        try {
            app(SaveRefereeEvaluationAction::class)->execute($record, $data);

            Notification::make()
                ->title(__('events.evaluation_saved'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error saving referee evaluation: ' . $e->getMessage());

            Notification::make()
                ->title(__('common.error_occurred'))
                ->danger()
                ->send();
        }
    }

    protected function togglePresence($record): void
    {
        try {
            $assignment = $record->refereeFunctionAssignments->first();

            if ($assignment) {
                $assignment->update(['is_present' => ! $assignment->is_present]);
            } else {
                RefereeFunctionAssignment::create([
                    'event_id' => $this->event->id,
                    'referee_enrollment_id' => $record->id,
                    'is_present' => false,
                    'assigned_by' => Auth::user()->individual->id,
                ]);
            }

            Notification::make()
                ->title(__('events.presence_toggled'))
                ->success()
                ->send();
        } catch (\Exception $e) {
            Log::error('Error toggling referee presence: ' . $e->getMessage());

            Notification::make()
                ->title(__('common.error_occurred'))
                ->danger()
                ->send();
        }
    }

    protected function isRefereePresent($record): bool
    {
        return $record->refereeFunctionAssignments->first()?->is_present ?? true;
    }

    protected function getRefereesQuery(): Builder
    {
        return RefereeEnrollment::query()
            ->where('event_id', $this->event->id)
            ->where(function ($q) {
                $q->where('status_class', ActiveRefereeEnrollmentState::class)
                    ->orWhere('status_class', PendingRefereeEnrollmentState::class);
            })
            ->with([
                'individual:id,name,surname,member_number,gender,birthdate',
                'attributes.attribute',
                'refereeFunctionAssignments.refereeFunction',
            ]);
    }

    public function getRefereesCountProperty(): int
    {
        return $this->getRefereesQuery()->count();
    }

    public function exportToExcel()
    {
        $export = new RefereeEnrollmentsExport($this->event);

        $filename = "judge_referees_{$this->event->id}.xlsx";

        return Excel::download($export, $filename);
    }

    public function generatePdf()
    {
        $referees = $this->getRefereesQuery()->get();

        $data = [
            'event' => $this->event,
            'referees' => $referees,
            'generatedAt' => now(),
        ];

        $pdf = Pdf::loadView('pdf.evt-events.judge-enrollments', $data);

        $filename = "judge_enrollments_{$this->event->id}.pdf";

        Notification::make()
            ->title(__('events.pdf_generated'))
            ->success()
            ->send();

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    public function render(): View
    {
        return view('livewire.evt-events.judge-enrollments');
    }
}
