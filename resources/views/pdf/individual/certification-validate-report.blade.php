<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('certifications.validate.export.pdf_title') }}</title>
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
            border-bottom: 2px solid #4f46e5;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            color: #4f46e5;
            margin: 0 0 10px 0;
        }

        .profile-card {
            background-color: #f3f4f6;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }

        .profile-card-table {
            width: 100%;
            border-collapse: collapse;
        }

        .profile-card-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .profile-photo-cell {
            width: 90px;
            padding-right: 15px;
        }

        .profile-photo {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            object-fit: cover;
        }

        .profile-initials {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            line-height: 80px;
        }

        .profile-fields-table {
            width: 100%;
            border-collapse: collapse;
        }

        .profile-fields-table td {
            border: none;
            padding: 2px 0;
            font-size: 10px;
        }

        .profile-field-label {
            font-weight: bold;
            color: #374151;
            width: 120px;
        }

        .profile-field-value {
            color: #333;
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
        <h1>{{ __('certifications.validate.export.pdf_title') }}</h1>
        <div class="profile-card">
            <table class="profile-card-table">
                <tr>
                    <td class="profile-photo-cell">
                        @if($photoBase64)
                            <img src="{{ $photoBase64 }}" class="profile-photo" alt="">
                        @else
                            <div class="profile-initials">
                                {{ strtoupper(substr($individual->name, 0, 1)) }}{{ strtoupper(substr($individual->surname, 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <table class="profile-fields-table">
                            <tr>
                                <td class="profile-field-label">{{ __('certifications.validate.export.full_name') }}:</td>
                                <td class="profile-field-value" colspan="3">{{ $instructorName }}</td>
                            </tr>
                            <tr>
                                <td class="profile-field-label">{{ __('certifications.validate.export.birthdate') }}:</td>
                                <td class="profile-field-value">{{ $individual->birthdate ? $individual->birthdate->format('d/m/Y') : '-' }}</td>
                                <td class="profile-field-label" style="padding-left: 15px;">{{ __('certifications.validate.export.gender') }}:</td>
                                <td class="profile-field-value">{{ $individual->gender === 'male' ? __('certifications.validate.export.gender_male') : __('certifications.validate.export.gender_female') }}</td>
                            </tr>
                            <tr>
                                <td class="profile-field-label">{{ __('certifications.validate.export.member_number') }}:</td>
                                <td class="profile-field-value">{{ $individual->member_number ?? '-' }}</td>
                                <td class="profile-field-label" style="padding-left: 15px;">{{ __('certifications.validate.export.email') }}:</td>
                                <td class="profile-field-value">{{ $individual->email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="profile-field-label">{{ __('certifications.validate.export.phone') }}:</td>
                                <td class="profile-field-value" colspan="3">{{ $individual->phone ?? '-' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">{{ __('certifications.validate.export.total') }}:</span>
            <span class="summary-value">{{ $certifications->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('certifications.validate.export.generated_at') }}:</span>
            <span class="summary-value">{{ $generatedAt->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    @if($certifications->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 14%;">{{ __('certifications.validate.export.issue_date') }}</th>
                    <th style="width: 26%;">{{ __('certifications.validate.export.certification') }}</th>
                    <th style="width: 18%;">{{ __('certifications.validate.export.entity') }}</th>
                    <th style="width: 22%;">{{ __('certifications.validate.export.student') }}</th>
                    <th style="width: 16%;">{{ __('certifications.validate.export.function_role') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($certifications as $index => $certification)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $certification->activated_at ? \Carbon\Carbon::parse($certification->activated_at)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $certification->certification_name }}</td>
                        <td>{{ $certification->entity_name ?? '-' }}</td>
                        <td>{{ $certification->holder_name }}</td>
                        <td>{{ $certification->is_main ? __('certifications.validate.export.course_director') : __('certifications.validate.export.assistant') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-section">{{ __('certifications.validate.no_certifications') }}</div>
    @endif

    <div class="footer">
        <div class="footer-row">
            <div class="footer-left">
                {{ config('branding.international.name', 'International Federation') }} - {{ config('branding.international.website_label', 'international.example.test') }}
            </div>
            <div class="footer-right">
                {{ __('certifications.validate.export.generated_at') }}: {{ $generatedAt->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>
