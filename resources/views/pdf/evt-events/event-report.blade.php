<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('events.referee_assignments_tab') }} - {{ $event->name }}</title>
    <style>
        @page {
            margin: 20px 30px;
            size: A4 landscape;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #10b981;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #10b981;
            margin: 0 0 5px 0;
        }

        .header h2 {
            font-size: 14px;
            color: #333;
            margin: 0 0 5px 0;
            font-weight: normal;
        }

        .header .event-dates {
            font-size: 11px;
            color: #666;
        }

        .summary-box {
            background-color: #f3f4f6;
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }

        .summary-label {
            display: table-cell;
            font-weight: bold;
            width: 150px;
        }

        .summary-value {
            display: table-cell;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background-color: #f3f4f6;
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
        }

        td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 8px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .empty-section {
            text-align: center;
            padding: 20px;
            color: #9ca3af;
            font-style: italic;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 9px;
            color: #6b7280;
        }

        .footer-row {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <h2>{{ __('events.referee_assignments_tab') }}</h2>
        <div class="event-dates">
            @if($event->start_date)
                {{ $event->start_date->format('d/m/Y') }}
                @if($event->end_date && $event->end_date->ne($event->start_date))
                    - {{ $event->end_date->format('d/m/Y') }}
                @endif
            @endif
            @if($event->location)
                | {{ $event->location }}
            @endif
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">{{ __('events.technical_officials') }}:</span>
            <span class="summary-value">{{ $refereeEnrollments->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('events.generated_at') }}:</span>
            <span class="summary-value">{{ $generatedAt->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    @if($refereeEnrollments->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 14%;">{{ __('events.technical_official') }}</th>
                    <th style="width: 13%;">{{ __('events.email') }}</th>
                    <th style="width: 14%;">{{ __('events.assigned_functions') }}</th>
                    <th style="width: 7%;">{{ __('events.presence') }}</th>
                    <th style="width: 6%;">{{ __('events.competition_days') }}</th>
                    <th style="width: 6%;">{{ __('events.number_of_games') }}</th>
                    <th style="width: 10%;">{{ __('events.evaluation') }}</th>
                    <th style="width: 13%;">{{ __('events.evaluation_notes') }}</th>
                    <th style="width: 14%;">{{ __('events.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refereeEnrollments as $index => $enrollment)
                    @php
                        $assignments = $enrollment->refereeFunctionAssignments;
                        $isPresent = $assignments->contains('is_present', true);
                        $allPresent = $assignments->isNotEmpty() && $assignments->every('is_present', true);
                        $totalCompetitionDays = $assignments->sum('competition_days');
                        $totalGames = $assignments->sum('number_of_games');
                        $assignmentNotes = $assignments->pluck('notes')->filter()->implode('; ');

                        $functions = $assignments->isEmpty()
                            ? '-'
                            : $assignments->map(fn ($a) => $a->function_name)->implode(', ');

                        if ($assignments->isEmpty()) {
                            $presenceLabel = '-';
                        } elseif ($allPresent) {
                            $presenceLabel = __('events.all_present');
                        } elseif ($isPresent) {
                            $presenceLabel = __('events.partially_present');
                        } else {
                            $presenceLabel = __('events.not_present');
                        }

                        $evaluationLabel = $enrollment->evaluation
                            ? $enrollment->evaluation . ' - ' . \App\Livewire\EvtEvents\JudgeEnrollments::getEvaluationLabel((int) $enrollment->evaluation)
                            : '-';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $enrollment->individual?->full_name }}</td>
                        <td>{{ $enrollment->individual?->email ?? '-' }}</td>
                        <td>{{ $functions }}</td>
                        <td class="text-center">{{ $presenceLabel }}</td>
                        <td class="text-center">{{ $totalCompetitionDays ?: '-' }}</td>
                        <td class="text-center">{{ $totalGames ?: '-' }}</td>
                        <td>{{ $evaluationLabel }}</td>
                        <td>{{ $enrollment->evaluation_notes ?? '-' }}</td>
                        <td>{{ $assignmentNotes ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-section">{{ __('events.no_technical_officials') }}</div>
    @endif

    <div class="footer">
        <div class="footer-row">
            <div class="footer-left">
                {{ config('app.name') }} - {{ __('events.referee_assignments_tab') }}
            </div>
            <div class="footer-right">
                {{ __('events.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
