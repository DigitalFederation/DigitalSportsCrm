<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('event_applications.wizard.sections.event_summary') }} - {{ $application->event_name }}</title>
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

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .subsection-title {
            font-size: 11px;
            font-weight: bold;
            color: #475569;
            margin: 10px 0 6px 0;
        }

        .field-grid {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }

        .field-row {
            display: table-row;
        }

        .field-label {
            display: table-cell;
            font-weight: bold;
            color: #64748b;
            width: 180px;
            padding: 2px 8px 2px 0;
            vertical-align: top;
            font-size: 9px;
        }

        .field-value {
            display: table-cell;
            padding: 2px 0;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        th {
            background-color: #f3f4f6;
            border: 1px solid #ddd;
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #374151;
        }

        td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .checklist-item {
            margin: 2px 0;
            font-size: 9px;
        }

        .checklist-item::before {
            content: "- ";
        }

        .budget-box {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .budget-cell {
            display: table-cell;
            width: 33.33%;
            padding: 6px 8px;
            text-align: center;
            font-size: 9px;
        }

        .budget-cell-expenses {
            background-color: #fff1f2;
            border: 1px solid #fecdd3;
        }

        .budget-cell-revenue {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
        }

        .budget-cell-balance {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .budget-cell-negative {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
        }

        .budget-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .budget-value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 2px;
        }

        .text-field {
            font-size: 9px;
            line-height: 1.5;
            margin-bottom: 6px;
            white-space: pre-line;
        }

        .text-field-label {
            font-weight: bold;
            color: #64748b;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .documents-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .documents-list li {
            padding: 3px 0;
            font-size: 9px;
            border-bottom: 1px solid #f1f5f9;
        }

        .documents-list li::before {
            content: "- ";
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
    @php
        $fd = $application->form_data ?? [];
    @endphp

    {{-- Header --}}
    <div class="header">
        <h1>{{ $application->event_name }}</h1>
        <h2>{{ $entity->name }}</h2>
        <div class="event-dates">
            @if ($application->start_date)
                {{ $application->start_date->format('d/m/Y') }}
                @if ($application->end_date && $application->end_date->ne($application->start_date))
                    - {{ $application->end_date->format('d/m/Y') }}
                @endif
            @endif
            @if ($application->district)
                | {{ $application->district->name }}
                @if ($application->municipality)
                    , {{ $application->municipality }}
                @endif
            @endif
        </div>
    </div>

    {{-- Event Information --}}
    <div class="section">
        <div class="section-title">{{ __('event_applications.wizard.sections.event_identification') }}</div>
        <div class="field-grid">
            @if ($application->event_type)
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.labels.event_type') }}</span>
                    <span class="field-value">{{ __('event_applications.event_types.' . $application->event_type) }}</span>
                </div>
            @endif
            @if ($application->event_category)
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.labels.event_category') }}</span>
                    <span class="field-value">{{ __('event_applications.event_categories.' . $application->event_category) }}</span>
                </div>
            @endif
            @if ($application->sport)
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.labels.sport') }}</span>
                    <span class="field-value">{{ $application->sport->name }}</span>
                </div>
            @endif
            @if ($application->target_audience)
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.labels.target_audience') }}</span>
                    <span class="field-value">{{ $application->target_audience }}</span>
                </div>
            @endif
            @if ($application->expected_participants)
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.labels.expected_participants') }}</span>
                    <span class="field-value">{{ $application->expected_participants }}</span>
                </div>
            @endif
            @php $pdfCategory = $application->category ?? $application->template?->category; @endphp
            @if ($pdfCategory)
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.labels.category') }}</span>
                    <span class="field-value">{{ __('event_applications.categories.' . $pdfCategory) }}</span>
                </div>
            @endif
            @if (!empty($fd['address']))
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.wizard.labels.address') }}</span>
                    <span class="field-value">{{ $fd['address'] }}</span>
                </div>
            @endif
            @if (!empty($fd['postal_code']))
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.wizard.labels.postal_code') }}</span>
                    <span class="field-value">{{ $fd['postal_code'] }}</span>
                </div>
            @endif
            @if (!empty($fd['location']))
                <div class="field-row">
                    <span class="field-label">{{ __('event_applications.wizard.labels.location') }}</span>
                    <span class="field-value">{{ $fd['location'] }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Entity --}}
    @if (!empty($fd['entity_name']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.promoting_entity') }}</div>
            <div class="field-grid">
                @foreach (['entity_name', 'national_federation_number', 'entity_address', 'entity_postal_code', 'entity_location', 'entity_nipc', 'entity_phone', 'entity_email'] as $field)
                    @if (!empty($fd[$field]))
                        <div class="field-row">
                            <span class="field-label">{{ __('event_applications.wizard.labels.' . $field) }}</span>
                            <span class="field-value">{{ $fd[$field] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>

            @if (!empty($fd['event_director_name']) || !empty($fd['event_director_phone']) || !empty($fd['event_director_email']))
                <div class="subsection-title">{{ __('event_applications.wizard.sections.event_director') }}</div>
                <div class="field-grid">
                    @foreach (['event_director_name', 'event_director_phone', 'event_director_email'] as $field)
                        @if (!empty($fd[$field]))
                            <div class="field-row">
                                <span class="field-label">{{ __('event_applications.wizard.labels.' . $field) }}</span>
                                <span class="field-value">{{ $fd[$field] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Previous Editions --}}
    @if (!empty($fd['previous_editions']) || !empty($fd['previous_actions']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.previous_editions') }}</div>

            @if (!empty($fd['previous_editions']))
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.year') }}</th>
                            <th>{{ __('event_applications.wizard.labels.edition_location') }}</th>
                            <th>{{ __('event_applications.wizard.labels.edition_name') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.participants_count') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.clubs_count') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['previous_editions'] as $ed)
                            <tr>
                                <td>{{ $ed['year'] ?? '-' }}</td>
                                <td>{{ $ed['location'] ?? '-' }}</td>
                                <td>{{ $ed['name'] ?? '-' }}</td>
                                <td class="text-right">{{ $ed['athletes'] ?? '-' }}</td>
                                <td class="text-right">{{ $ed['clubs'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (!empty($fd['previous_actions']))
                <div class="subsection-title">{{ __('event_applications.wizard.labels.previous_actions') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.action') }}</th>
                            <th>{{ __('event_applications.wizard.labels.agents') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.participants') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['previous_actions'] as $action)
                            <tr>
                                <td>{{ $action['action'] ?? '-' }}</td>
                                <td>
                                    @if (!empty($action['agents']))
                                        {{ collect($action['agents'])->map(fn($a) => __('event_applications.wizard.agent_options.' . $a))->implode(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">{{ $action['participants'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- Results Forecast --}}
    @if (!empty($fd['forecast_total_participants']) || !empty($fd['event_objectives_description']) || !empty($fd['planned_actions']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.results_forecast') }}</div>
            <div class="field-grid">
                @foreach (['forecast_total_participants', 'forecast_female_athletes', 'forecast_male_athletes', 'forecast_technical_officials', 'forecast_coaches', 'forecast_clubs'] as $field)
                    @if (!empty($fd[$field]))
                        <div class="field-row">
                            <span class="field-label">{{ __('event_applications.wizard.labels.' . $field) }}</span>
                            <span class="field-value">{{ $fd[$field] }}</span>
                        </div>
                    @endif
                @endforeach
            </div>

            @foreach (['event_objectives_description', 'event_benefits_description', 'event_link_description', 'event_equipment_description'] as $field)
                @if (!empty($fd[$field]))
                    <div class="text-field-label">{{ __('event_applications.wizard.labels.' . $field) }}</div>
                    <div class="text-field">{{ $fd[$field] }}</div>
                @endif
            @endforeach

            @if (!empty($fd['planned_actions']))
                <div class="subsection-title">{{ __('event_applications.wizard.labels.planned_actions') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.action') }}</th>
                            <th>{{ __('event_applications.wizard.labels.agents') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.participants') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['planned_actions'] as $action)
                            <tr>
                                <td>{{ $action['action'] ?? '-' }}</td>
                                <td>
                                    @if (!empty($action['agents']))
                                        {{ collect($action['agents'])->map(fn($a) => __('event_applications.wizard.agent_options.' . $a))->implode(', ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right">{{ $action['participants'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- Facilities & Logistics --}}
    @php
        $facilityItems = collect($fd['facilities_checklist'] ?? [])->filter()->keys();
        $logisticsItems = collect($fd['logistics_checklist'] ?? [])->filter()->keys();
    @endphp
    @if ($facilityItems->isNotEmpty() || $logisticsItems->isNotEmpty() || !empty($fd['other_facilities']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.logistics') }}</div>

            @if ($facilityItems->isNotEmpty())
                <div class="subsection-title">{{ __('event_applications.wizard.sections.facilities') }}</div>
                @foreach ($facilityItems as $code)
                    <div class="checklist-item">{{ __('event_applications.wizard.checklist_items.' . $code) }}</div>
                @endforeach
            @endif

            @if (!empty($fd['other_facilities']))
                <div class="text-field-label" style="margin-top: 6px;">{{ __('event_applications.wizard.labels.other_facilities') }}</div>
                <div class="text-field">{{ $fd['other_facilities'] }}</div>
            @endif

            @if ($logisticsItems->isNotEmpty())
                @php
                    $logisticsGroups = [
                        'accommodations' => ['ATA1', 'ATA2', 'ATA3', 'ATA4'],
                        'transport' => ['TRA1', 'TRA2', 'TRA3', 'TRA4'],
                        'food' => ['ALI1', 'ALI2'],
                    ];
                @endphp
                @foreach ($logisticsGroups as $groupKey => $groupCodes)
                    @php $activeInGroup = $logisticsItems->intersect($groupCodes); @endphp
                    @if ($activeInGroup->isNotEmpty())
                        <div class="subsection-title">{{ __('event_applications.wizard.sections.' . $groupKey) }}</div>
                        @foreach ($activeInGroup as $code)
                            <div class="checklist-item">{{ __('event_applications.wizard.checklist_items.' . $code) }}</div>
                        @endforeach
                    @endif
                @endforeach
            @endif
        </div>
    @endif

    {{-- Safety --}}
    @php
        $safetyItems = collect($fd['safety_checklist'] ?? [])->filter()->keys();
    @endphp
    @if ($safetyItems->isNotEmpty() || !empty($fd['pse_responsible_name']) || !empty($fd['insurances']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.safety_plan') }}</div>

            @if ($safetyItems->isNotEmpty())
                @foreach ($safetyItems as $code)
                    <div class="checklist-item">{{ __('event_applications.wizard.checklist_items.' . $code) }}</div>
                @endforeach
            @endif

            @if (!empty($fd['pse_responsible_name']) || !empty($fd['pse_responsible_phone']) || !empty($fd['pse_responsible_email']))
                <div class="subsection-title">{{ __('event_applications.wizard.labels.emergency_team') }}</div>
                <div class="field-grid">
                    @foreach (['pse_responsible_name', 'pse_responsible_phone', 'pse_responsible_email'] as $field)
                        @if (!empty($fd[$field]))
                            <div class="field-row">
                                <span class="field-label">{{ __('event_applications.wizard.labels.' . $field) }}</span>
                                <span class="field-value">{{ $fd[$field] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if (!empty($fd['insurances']))
                <div class="subsection-title">{{ __('event_applications.wizard.labels.insurances') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.insurance_type') }}</th>
                            <th>{{ __('event_applications.wizard.labels.insurer') }}</th>
                            <th>{{ __('event_applications.wizard.labels.policy_number') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['insurances'] as $ins)
                            <tr>
                                <td>{{ $ins['type'] ?? '-' }}</td>
                                <td>{{ $ins['insurer'] ?? '-' }}</td>
                                <td>{{ $ins['policy_number'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- Partners & Promotion --}}
    @php
        $promotionItems = collect($fd['promotion_checklist'] ?? [])->filter()->keys();
    @endphp
    @if (!empty($fd['partners']) || $promotionItems->isNotEmpty() || !empty($fd['promotion_description']) || !empty($fd['financing_description']) || !empty($fd['technical_documents_description']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.partners_norms') }}</div>

            @if (!empty($fd['partners']))
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.partner_name') }}</th>
                            <th>{{ __('event_applications.wizard.labels.partnership_type') }}</th>
                            <th>{{ __('event_applications.wizard.labels.partner_email') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['partners'] as $p)
                            <tr>
                                <td>{{ $p['name'] ?? '-' }}</td>
                                <td>{{ $p['partnership_type'] ?? '-' }}</td>
                                <td>{{ $p['email'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if ($promotionItems->isNotEmpty())
                <div class="subsection-title">{{ __('event_applications.wizard.sections.technical_docs') }}</div>
                @foreach ($promotionItems as $code)
                    <div class="checklist-item">{{ __('event_applications.wizard.checklist_items.' . $code) }}</div>
                @endforeach
            @endif

            @if (!empty($fd['promotion_description']))
                <div class="text-field-label">{{ __('event_applications.wizard.labels.promotion_description') }}</div>
                <div class="text-field">{{ $fd['promotion_description'] }}</div>
            @endif

            @if (!empty($fd['financing_description']))
                <div class="text-field-label">{{ __('event_applications.wizard.labels.financing_description') }}</div>
                <div class="text-field">{{ $fd['financing_description'] }}</div>
            @endif

            @if (!empty($fd['technical_documents_description']))
                <div class="text-field-label">{{ __('event_applications.wizard.labels.technical_documents_description') }}</div>
                <div class="text-field">{{ $fd['technical_documents_description'] }}</div>
            @endif
        </div>
    @endif

    {{-- Budget --}}
    @if (!empty($fd['expenses']) || !empty($fd['revenue']))
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.sections.budget') }}</div>

            @php
                $totalExpenses = 0;
                $totalRevenue = 0;
                if (!empty($fd['expenses'])) {
                    foreach ($fd['expenses'] as $group) {
                        foreach ($group as $item) {
                            $totalExpenses += ((float) ($item['qty'] ?? 0) * (float) ($item['value'] ?? 0));
                        }
                    }
                }
                if (!empty($fd['revenue'])) {
                    foreach ($fd['revenue'] as $key => $group) {
                        if ($key === 'partners') {
                            foreach ($group as $p) {
                                $totalRevenue += ((float) ($p['qty'] ?? 0) * (float) ($p['value'] ?? 0));
                            }
                        } else {
                            foreach ($group as $item) {
                                $totalRevenue += ((float) ($item['qty'] ?? 0) * (float) ($item['value'] ?? 0));
                            }
                        }
                    }
                }
                $balance = $totalRevenue - $totalExpenses;
            @endphp

            <div class="budget-box">
                <div class="budget-cell budget-cell-expenses">
                    <div class="budget-label" style="color: #be123c;">{{ __('event_applications.wizard.sections.expenses') }}</div>
                    <div class="budget-value" style="color: #be123c;">{{ number_format($totalExpenses, 2) }} EUR</div>
                </div>
                <div class="budget-cell budget-cell-revenue">
                    <div class="budget-label" style="color: #059669;">{{ __('event_applications.wizard.sections.revenue') }}</div>
                    <div class="budget-value" style="color: #059669;">{{ number_format($totalRevenue, 2) }} EUR</div>
                </div>
                <div class="budget-cell {{ $balance >= 0 ? 'budget-cell-balance' : 'budget-cell-negative' }}">
                    <div class="budget-label" style="color: {{ $balance >= 0 ? '#2563eb' : '#d97706' }};">{{ __('event_applications.wizard.labels.balance') }}</div>
                    <div class="budget-value" style="color: {{ $balance >= 0 ? '#2563eb' : '#d97706' }};">{{ number_format($balance, 2) }} EUR</div>
                </div>
            </div>

            {{-- Expense Details --}}
            @if (!empty($fd['expenses']))
                <div class="subsection-title">{{ __('event_applications.wizard.sections.expenses') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.item') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.quantity') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['expenses'] as $groupKey => $group)
                            @php $groupTotal = 0; @endphp
                            @foreach ($group as $itemKey => $item)
                                @php
                                    $qty = (float) ($item['qty'] ?? 0);
                                    $val = (float) ($item['value'] ?? 0);
                                    $subtotal = $qty * $val;
                                    $groupTotal += $subtotal;
                                @endphp
                                @if ($qty > 0 || $val > 0)
                                    <tr>
                                        <td>{{ __('event_applications.wizard.expense_items.' . $itemKey) }}</td>
                                        <td class="text-right">{{ $qty }}</td>
                                        <td class="text-right">{{ number_format($val, 2) }}</td>
                                        <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Revenue Details --}}
            @if (!empty($fd['revenue']))
                <div class="subsection-title">{{ __('event_applications.wizard.sections.revenue') }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('event_applications.wizard.labels.item') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.quantity') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                            <th class="text-right">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fd['revenue'] as $groupKey => $group)
                            @foreach ($group as $itemKey => $item)
                                @php
                                    $isPartner = $groupKey === 'partners';
                                    $qty = (float) ($item['qty'] ?? 0);
                                    $val = (float) ($item['value'] ?? 0);
                                    $subtotal = $qty * $val;
                                    $label = $isPartner ? ($item['entity'] ?? __('event_applications.wizard.labels.partner_entity')) : __('event_applications.wizard.revenue_items.' . $itemKey);
                                @endphp
                                @if ($qty > 0 || $val > 0)
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-right">{{ $qty }}</td>
                                        <td class="text-right">{{ number_format($val, 2) }}</td>
                                        <td class="text-right">{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    {{-- Documents --}}
    @if ($application->documents->count() > 0)
        <div class="section">
            <div class="section-title">{{ __('event_applications.wizard.steps.documents') }}</div>
            <ul class="documents-list">
                @foreach ($application->documents as $doc)
                    <li>{{ $doc->original_filename ?? $doc->name ?? $doc->file_name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-row">
            <div class="footer-left">
                {{ config('app.name') }} - {{ __('event_applications.wizard.sections.event_summary') }}
            </div>
            <div class="footer-right">
                {{ __('event_applications.wizard.pdf_generated_at') }}: {{ $generatedAt->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
