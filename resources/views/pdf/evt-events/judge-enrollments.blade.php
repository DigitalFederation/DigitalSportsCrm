<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('events.technical_officials_tab') }} - {{ $event->name }}</title>
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
            border-bottom: 2px solid #7c3aed;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #7c3aed;
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

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-header {
            background-color: #7c3aed;
            color: white;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .section-count {
            float: right;
            font-weight: normal;
            font-size: 11px;
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
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
        }

        td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .gender-male {
            color: #2563eb;
        }

        .gender-female {
            color: #db2777;
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
    {{-- Header --}}
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <h2>{{ __('events.technical_officials_tab') }}</h2>
        <div class="event-dates">
            {{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}
            @if($event->location)
                | {{ $event->location }}
            @endif
        </div>
    </div>

    {{-- Summary Box --}}
    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">{{ __('events.total_confirmed') }}:</span>
            <span class="summary-value">{{ $referees->count() }} {{ __('events.technical_officials_tab') }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('events.generated_at') }}:</span>
            <span class="summary-value">{{ $generatedAt->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    {{-- Referees Section --}}
    <div class="section">
        <div class="section-header">
            {{ __('events.technical_officials_tab') }}
            <span class="section-count">({{ $referees->count() }})</span>
        </div>

        @if($referees->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 18%;">{{ __('Name') }}</th>
                        <th style="width: 10%;">{{ __('events.member_number') }}</th>
                        <th style="width: 7%;">{{ __('events.gender') }}</th>
                        <th style="width: 15%;">{{ __('events.enrolled_function') }}</th>
                        <th style="width: 18%;">{{ __('events.functions_performed') }}</th>
                        <th style="width: 12%;">{{ __('events.evaluation') }}</th>
                        <th style="width: 16%;">{{ __('events.evaluation_notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referees as $index => $referee)
                        @php
                            $selectAttribute = $referee->attributes
                                ->first(fn ($attr) => $attr->attribute && $attr->attribute->attribute_type === 'SELECT');

                            $enrolledFunction = $selectAttribute?->value ?: '-';

                            $functionsPerformed = $referee->refereeFunctionAssignments->isEmpty()
                                ? '-'
                                : $referee->refereeFunctionAssignments->map(fn ($a) => $a->function_name)->implode(', ');

                            $evaluationLabel = $referee->evaluation
                                ? $referee->evaluation . ' - ' . \App\Livewire\EvtEvents\JudgeEnrollments::getEvaluationLabel((int) $referee->evaluation)
                                : '-';
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $referee->individual->full_name }}</td>
                            <td>{{ $referee->individual->member_number ?? '-' }}</td>
                            <td class="gender-{{ $referee->individual->gender }}">
                                {{ $referee->individual->gender ? __('events.' . $referee->individual->gender) : '-' }}
                            </td>
                            <td>{{ $enrolledFunction }}</td>
                            <td>{{ $functionsPerformed }}</td>
                            <td>{{ $evaluationLabel }}</td>
                            <td>{{ $referee->evaluation_notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-section">{{ __('events.no_technical_officials') }}</div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-row">
            <div class="footer-left">
                {{ config('app.name') }} - {{ __('events.technical_officials_tab') }}
            </div>
            <div class="footer-right">
                {{ __('events.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
