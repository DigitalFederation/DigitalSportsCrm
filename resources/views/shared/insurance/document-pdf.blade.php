<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APÓLICE DE SEGURO - {{ $insurance->policy_number ?: 'PENDING' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, 'Segoe UI', sans-serif;
            font-size: 8.5pt;
            line-height: 1.3;
            color: #1a202c;
            background: white;
            padding: 10mm 10mm;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f2f59;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: #0f2f59;
        }

        .logo-section {
            margin-bottom: 12px;
        }

        .document-title-pt {
            font-size: 16pt;
            font-weight: 700;
            color: #0f2f59;
            text-transform: uppercase;
            letter-spacing: 1.4px;
            margin-bottom: 2px;
        }

        .document-title-en {
            font-size: 9pt;
            font-weight: 400;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        .policy-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0f2f59;
            color: white;
            padding: 4px 12px;
            border-radius: 28px;
            margin-top: 8px;
        }

        .policy-label {
            font-size: 7pt;
            letter-spacing: .5px;
        }

        .policy-number {
            font-size: 10pt;
            font-weight: 700;
        }

        /* Main Layout */
        .content-wrapper {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: flex-start;
        }

        .left-column, .right-column {
            flex: 1;
            min-width: 0;
        }

        /* Section Styles */
        .section {
            background: white;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 6px;
            border: 1px solid #d6dee9;
        }

        .section-header {
            background: #0f2f59;
            padding: 5px 8px;
        }

        .section-title-pt {
            font-size: 8.5pt;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .section-title-en {
            font-size: 6.5pt;
            color: rgba(255, 255, 255, 0.8);
            text-transform: uppercase;
            margin-top: 1px;
        }

        .section-body {
            padding: 8px;
            background: white;
        }

        /* Photo Section */
        .photo-section {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .photo-container {
            flex-shrink: 0;
        }

        .photo-frame {
            width: 26mm;
            height: 34mm;
            border: 1px solid #d6dee9;
            border-radius: 4px;
            overflow: hidden;
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-placeholder {
            text-align: center;
            color: #6b7280;
            font-size: 7.5pt;
            letter-spacing: .6px;
        }

        .placeholder-mark {
            font-weight: 600;
            letter-spacing: .8px;
            margin-bottom: 2px;
        }

        .photo-data {
            flex: 1;
        }

        /* Field Styles */
        .field-group {
            margin-bottom: 5px;
        }

        .field-label-pt {
            font-weight: 600;
            color: #0f2f59;
            font-size: 8pt;
            display: inline;
            margin-right: 3px;
        }

        .field-label-en {
            font-style: normal;
            color: #64748b;
            font-size: 7.5pt;
            display: inline;
        }

        .field-label-en::before {
            content: ' / ';
            color: #94a3b8;
        }

        .field-value {
            margin-top: 2px;
            font-size: 8.5pt;
            color: #2d3748;
            background: white;
            padding: 3px 6px;
            border-radius: 3px;
            border: 1px solid #d6dee9;
            border-left: 2px solid #0f2f59;
        }

        /* Two Column Fields */
        .fields-row {
            display: flex;
            gap: 8px;
            margin-bottom: 5px;
        }

        .fields-row .field-group {
            flex: 1;
            margin-bottom: 0;
        }

        /* Period Cards */
        .period-cards {
            display: flex;
            gap: 8px;
        }

        .period-card {
            flex: 1;
            background: white;
            border-radius: 3px;
            padding: 6px;
            border: 1px solid #d6dee9;
            text-align: left;
            min-width: 0;
        }

        .period-label {
            font-size: 7pt;
            color: #64748b;
            margin-bottom: 1px;
        }

        .period-date {
            font-size: 8.5pt;
            font-weight: 600;
            color: #0f2f59;
            margin-bottom: 1px;
            display: block;
            white-space: normal;
            word-break: break-word;
        }

        .period-date-en {
            font-size: 7pt;
            color: #64748b;
            display: block;
            white-space: normal;
            word-break: break-word;
        }

        /* Coverage List */
        .coverage-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 4px 8px;
            margin-top: 2px;
        }

        .coverage-item {
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 5px;
        }

        .coverage-name {
            color: #334155;
            font-size: 7.5pt;
            word-break: break-word;
        }

        .coverage-amount {
            font-weight: 600;
            color: #0f2f59;
            font-size: 7.5pt;
            text-align: right;
        }

        /* Footer */
        .footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #d6dee9;
            text-align: center;
        }

        .footer-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 6px;
        }

        .footer-item {
            text-align: center;
        }

        .footer-label {
            font-size: 6.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 2px;
        }

        .footer-value {
            font-size: 7.5pt;
            color: #0f2f59;
            font-weight: 600;
        }

        .footer-disclaimer {
            margin-top: 6px;
            padding: 6px;
            background: #f8fafc;
            border-radius: 3px;
            font-size: 6.5pt;
            color: #64748b;
            line-height: 1.3;
        }

        .disclaimer-pt {
            font-weight: 500;
            color: #475569;
        }

        .disclaimer-en {
            font-style: italic;
            margin-top: 1px;
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-bold { font-weight: 700; }
        .text-primary { color: #003366; }
        .mt-10 { margin-top: 10px; }
        .pre-line { white-space: pre-line; }
    </style>
</head>
<body>
    @php
        $startDateFormatted = \Carbon\Carbon::parse($insurance->start_date)->format('d/m/Y');
        $endDateFormatted = \Carbon\Carbon::parse($insurance->end_date)->format('d/m/Y') . ' - 23h59';
    @endphp

    <!-- Header -->
    <div class="header">
        <div class="document-title-pt">APÓLICE DE SEGURO</div>
        <div class="document-title-en">Insurance Policy Certificate</div>
        <div class="policy-badge">
            <span class="policy-label">Nº APÓLICE / Policy Number</span>
            <span class="policy-number">{{ $insurance->policy_number ?: 'PENDING' }}</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-wrapper">
        <!-- Left Column -->
        <div class="left-column">
            <!-- DADOS DO SEGURADO -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">DADOS DO SEGURADO</div>
                    <div class="section-title-en">INSURED DATA</div>
                </div>
                <div class="section-body">
                    <div class="photo-section">
                        <div class="photo-container">
                            <div class="photo-frame">
                                @php
                                    $avatarMedia = $member->getFirstMedia('profile');
                                    $avatarBase64 = null;
                                    if ($avatarMedia) {
                                        $disk = \Illuminate\Support\Facades\Storage::disk($avatarMedia->disk);
                                        $path = $avatarMedia->getPathRelativeToRoot();
                                        if ($disk->exists($path)) {
                                            $imageContent = $disk->get($path);
                                            $mimeType = $avatarMedia->mime_type ?? 'image/jpeg';
                                            $avatarBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                                        }
                                    }
                                @endphp
                                @if($avatarBase64)
                                    <img src="{{ $avatarBase64 }}" class="photo" alt="Photo">
                                @else
                                    <div class="photo-placeholder">
                                        <div class="placeholder-mark">PHOTO</div>
                                        <div>SEM FOTO</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="photo-data">
                            <div class="field-group">
                                <span class="field-label-pt">Número de Filiado</span>
                                <span class="field-label-en">Member Number</span>
                                <div class="field-value">{{ $member->member_number ?: 'N/A' }}</div>
                            </div>
                            <div class="field-group">
                                <span class="field-label-pt">Nacionalidade</span>
                                <span class="field-label-en">Nationality</span>
                                <div class="field-value">{{ optional($member->country)->name ?? 'N/A' }}</div>
                            </div>
                            <div class="field-group">
                                <span class="field-label-pt">Data de Nascimento</span>
                                <span class="field-label-en">Date of Birth</span>
                                <div class="field-value">{{ $member->birthdate ? \Carbon\Carbon::parse($member->birthdate)->format('d/m/Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="fields-row">
                        <div class="field-group">
                            <span class="field-label-pt">Nome Completo</span>
                            <span class="field-label-en">Full Name</span>
                            <div class="field-value">{{ $member->name }} {{ $member->surname }}</div>
                        </div>
                    </div>

                    <div class="field-group">
                        <span class="field-label-pt">Morada</span>
                        <span class="field-label-en">Address</span>
                        <div class="field-value">
                            {{ $member->address ?: 'N/A' }}<br>
                            {{ $member->postal_code }} {{ $member->location }}
                        </div>
                    </div>

                    <div class="fields-row">
                        <div class="field-group">
                            <span class="field-label-pt">Telefone</span>
                            <span class="field-label-en">Phone</span>
                            <div class="field-value">{{ $member->phone ?: 'N/A' }}</div>
                        </div>
                        <div class="field-group">
                            <span class="field-label-pt">Email</span>
                            <span class="field-label-en">Email</span>
                            <div class="field-value">{{ $member->email ?: 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTACTO DA SEGURADORA -->
            @if($plan->insurance_company_name || $plan->insurer_email || $plan->insurer_phone || $plan->insurer_address)
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">CONTACTO DA SEGURADORA</div>
                    <div class="section-title-en">INSURER CONTACT</div>
                </div>
                <div class="section-body">
                    @if($plan->insurer_address)
                    <div class="field-group">
                        <span class="field-label-pt">Morada</span>
                        <span class="field-label-en">Address</span>
                        <div class="field-value">{{ $plan->insurer_address }}</div>
                    </div>
                    @endif

                    <div class="fields-row">
                        @if($plan->insurer_phone)
                        <div class="field-group">
                            <span class="field-label-pt">Telefone</span>
                            <span class="field-label-en">Phone</span>
                            <div class="field-value">{{ $plan->insurer_phone }}</div>
                        </div>
                        @endif
                        @if($plan->insurer_email)
                        <div class="field-group">
                            <span class="field-label-pt">Email</span>
                            <span class="field-label-en">Email</span>
                            <div class="field-value">{{ $plan->insurer_email }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- COBERTURAS E CAPITAIS SEGUROS -->
            @if($plan->coverage_details)
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">COBERTURAS E CAPITAIS SEGUROS</div>
                    <div class="section-title-en">COVERAGE AND INSURED CAPITAL</div>
                </div>
                <div class="section-body">
                    <div class="field-value pre-line">{{ $plan->coverage_details }}</div>
                </div>
            </div>
            @endif

            <!-- FRANQUIAS APLICÁVEIS -->
            @if($plan->applicable_deductibles)
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">FRANQUIAS APLICÁVEIS</div>
                    <div class="section-title-en">APPLICABLE DEDUCTIBLES</div>
                </div>
                <div class="section-body">
                    <div class="field-value pre-line">{{ $plan->applicable_deductibles }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="right-column">
            <!-- PLANO DE SEGURO -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">PLANO DE SEGURO</div>
                    <div class="section-title-en">INSURANCE PLAN</div>
                </div>
                <div class="section-body">
                    <div class="field-group">
                        <span class="field-label-pt">Plano de Seguro</span>
                        <span class="field-label-en">Insurance Plan</span>
                        <div class="field-value">{{ $plan->name }}</div>
                    </div>

                    @if($plan->insurance_company_name)
                    <div class="field-group">
                        <span class="field-label-pt">Companhia de Seguros</span>
                        <span class="field-label-en">Insurance Company</span>
                        <div class="field-value">{{ $plan->insurance_company_name }}</div>
                    </div>
                    @endif

                    <div class="field-group">
                        <span class="field-label-pt">Tipo de Cobertura</span>
                        <span class="field-label-en">Coverage Type</span>
                        <div class="field-value">{{ __('insurance.types.' . $plan->type->value, [], 'pt') }}</div>
                    </div>

                    <div class="field-group">
                        <span class="field-label-pt">Número da Apólice</span>
                        <span class="field-label-en">Policy Number</span>
                        <div class="field-value text-bold text-primary">{{ $insurance->policy_number ?: 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- PERÍODO DO SEGURO -->
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">PERÍODO DO SEGURO</div>
                    <div class="section-title-en">INSURANCE PERIOD</div>
                </div>
                <div class="section-body">
                    <div class="period-cards">
                        <div class="period-card">
                            <div class="period-label">De / From</div>
                            <div class="period-date">{{ $startDateFormatted }}</div>
                        </div>
                        <div class="period-card">
                            <div class="period-label">Até / Until</div>
                            <div class="period-date">{{ $endDateFormatted }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COBERTURA TERRITORIAL -->
            @if($plan->territorial_scope)
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">COBERTURA TERRITORIAL</div>
                    <div class="section-title-en">TERRITORIAL COVERAGE</div>
                </div>
                <div class="section-body">
                    <div class="field-value pre-line">{{ $plan->territorial_scope }}</div>
                </div>
            </div>
            @endif

            <!-- ATIVIDADES SEGURADAS -->
            @if($plan->insured_activity)
            <div class="section">
                <div class="section-header">
                    <div class="section-title-pt">ATIVIDADES SEGURADAS</div>
                    <div class="section-title-en">INSURED ACTIVITIES</div>
                </div>
                <div class="section-body">
                    <div class="field-value pre-line">{{ $plan->insured_activity }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-info">
            <div class="footer-item">
                <div class="footer-label">Documento Gerado Em</div>
                <div class="footer-value">{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
            </div>
            <div class="footer-item">
                <div class="footer-label">Referência</div>
                <div class="footer-value">INS-{{ $insurance->id }}-{{ date('Ymd') }}</div>
            </div>
        </div>
        <div class="footer-disclaimer">
            <div class="disclaimer-pt">Este documento é apenas informativo. Prevalecem os termos e condições originais da apólice.</div>
            <div class="disclaimer-en">This document is for informational purposes only. The original policy terms and conditions prevail.</div>
        </div>
    </div>
</body>
</html>
