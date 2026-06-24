<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('events.confirmed_enrollments') }} - {{ $event->name }}</title>
    <style>
        @page {
            margin: 20px 30px;
            size: A4 portrait;
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
            border-bottom: 2px solid #0066cc;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #0066cc;
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

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-header {
            background-color: #0066cc;
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
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $event->name }}</h1>
        <h2>{{ __('events.confirmed_enrollments') }}</h2>
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
            <span class="summary-label">{{ __('events.organization') }}:</span>
            <span class="summary-value">{{ $event->organizer?->organizable?->name ?? $model->name }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('events.total_confirmed') }}:</span>
            <span class="summary-value">
                {{ $athletes->count() + $coaches->count() + $officials->count() + $referees->count() + $staff->count() }} {{ __('events.participants') }}
            </span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('events.generated_at') }}:</span>
            <span class="summary-value">{{ $generatedAt->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    {{-- Athletes Section --}}
    <div class="section">
        <div class="section-header">
            {{ __('events.athletes_tab') }}
            <span class="section-count">({{ $athletes->count() }})</span>
        </div>

        @if($athletes->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 24%;">{{ __('Name') }}</th>
                        <th style="width: 14%;">{{ __('events.birth_date') }}</th>
                        <th style="width: 8%;">{{ __('events.gender') }}</th>
                        <th style="width: 12%;">{{ __('events.member_number') }}</th>
                        <th style="width: 20%;">{{ __('Entity') }}</th>
                        <th style="width: 18%;">{{ __('Discipline') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($athletes as $index => $athlete)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $athlete->individual->full_name }}</td>
                            <td>{{ $athlete->individual->birthdate?->format('d/m/Y') ?? '-' }}</td>
                            <td class="gender-{{ $athlete->individual->gender }}">
                                {{ $athlete->individual->gender === 'male' ? 'M' : 'F' }}
                            </td>
                            <td>{{ $athlete->individual->member_number ?? '-' }}</td>
                            <td>{{ $athlete->entity?->name ?? '-' }}</td>
                            <td>{{ $athlete->discipline?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-section">{{ __('events.no_athletes_enrolled') }}</div>
        @endif
    </div>

    {{-- Coaches Section --}}
    <div class="section">
        <div class="section-header">
            {{ __('events.coaches_tab') }}
            <span class="section-count">({{ $coaches->count() }})</span>
        </div>

        @if($coaches->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 27%;">{{ __('Name') }}</th>
                        <th style="width: 16%;">{{ __('events.birth_date') }}</th>
                        <th style="width: 10%;">{{ __('events.gender') }}</th>
                        <th style="width: 16%;">{{ __('events.member_number') }}</th>
                        <th style="width: 26%;">{{ __('Entity') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coaches as $index => $coach)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $coach->individual->full_name }}</td>
                            <td>{{ $coach->individual->birthdate?->format('d/m/Y') ?? '-' }}</td>
                            <td class="gender-{{ $coach->individual->gender }}">
                                {{ $coach->individual->gender === 'male' ? 'M' : 'F' }}
                            </td>
                            <td>{{ $coach->individual->member_number ?? '-' }}</td>
                            <td>{{ $coach->entity?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-section">{{ __('events.no_coaches_enrolled') }}</div>
        @endif
    </div>

    {{-- Officials Section --}}
    <div class="section">
        <div class="section-header">
            {{ __('events.officials_tab') }}
            <span class="section-count">({{ $officials->count() }})</span>
        </div>

        @if($officials->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 4%;">#</th>
                        <th style="width: 22%;">{{ __('Name') }}</th>
                        <th style="width: 13%;">{{ __('events.birth_date') }}</th>
                        <th style="width: 8%;">{{ __('events.gender') }}</th>
                        <th style="width: 12%;">{{ __('events.member_number') }}</th>
                        <th style="width: 20%;">{{ __('Entity') }}</th>
                        <th style="width: 21%;">{{ __('events.function') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($officials as $index => $official)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $official->individual->full_name }}</td>
                            <td>{{ $official->individual->birthdate?->format('d/m/Y') ?? '-' }}</td>
                            <td class="gender-{{ $official->individual->gender }}">
                                {{ $official->individual->gender === 'male' ? 'M' : 'F' }}
                            </td>
                            <td>{{ $official->individual->member_number ?? '-' }}</td>
                            <td>{{ $official->entity?->name ?? '-' }}</td>
                            <td>{{ $official->attributes->first()?->value ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-section">{{ __('events.no_officials_enrolled') }}</div>
        @endif
    </div>

    {{-- Referees Section --}}
    <div class="section">
        <div class="section-header">
            {{ __('events.referees_tab') }}
            <span class="section-count">({{ $referees->count() }})</span>
        </div>

        @if($referees->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">{{ __('Name') }}</th>
                        <th style="width: 18%;">{{ __('events.birth_date') }}</th>
                        <th style="width: 12%;">{{ __('events.gender') }}</th>
                        <th style="width: 30%;">{{ __('events.member_number') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($referees as $index => $referee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $referee->individual->full_name }}</td>
                            <td>{{ $referee->individual->birthdate?->format('d/m/Y') ?? '-' }}</td>
                            <td class="gender-{{ $referee->individual->gender }}">
                                {{ $referee->individual->gender === 'male' ? 'M' : 'F' }}
                            </td>
                            <td>{{ $referee->individual->member_number ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-section">{{ __('events.no_referees_enrolled') }}</div>
        @endif
    </div>

    {{-- Staff Section --}}
    <div class="section">
        <div class="section-header">
            {{ __('events.staff_tab') }}
            <span class="section-count">({{ $staff->count() }})</span>
        </div>

        @if($staff->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 35%;">{{ __('Name') }}</th>
                        <th style="width: 18%;">{{ __('events.birth_date') }}</th>
                        <th style="width: 12%;">{{ __('events.gender') }}</th>
                        <th style="width: 30%;">{{ __('events.member_number') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $index => $member)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $member->individual?->full_name ?? ($member->first_name . ' ' . $member->last_name) }}</td>
                            <td>{{ $member->individual?->birthdate?->format('d/m/Y') ?? '-' }}</td>
                            <td class="{{ $member->individual ? 'gender-' . $member->individual->gender : '' }}">
                                @if($member->individual?->gender)
                                    {{ $member->individual->gender === 'male' ? 'M' : 'F' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $member->individual?->member_number ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-section">{{ __('events.no_staff_enrolled') }}</div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-row">
            <div class="footer-left">
                {{ config('app.name') }} - {{ __('events.confirmed_enrollments') }}
            </div>
            <div class="footer-right">
                {{ __('events.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
