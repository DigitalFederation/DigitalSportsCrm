
@php
    $mainFederation = \Domain\Federations\Models\Federation::where('is_default_federation', true)->first();
    $brandLogoPath = config('branding.primary.logo_path') ? public_path(config('branding.primary.logo_path')) : null;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $document->invoice_extended ?? $document->number_extended }}</title>

    <style type="text/css">
        html {
            line-height: 1.5;
            /* 1 */
            -webkit-text-size-adjust: 100%;
            /* 2 */
            -moz-tab-size: 4;
            /* 3 */
            -o-tab-size: 4;
            tab-size: 4;
            /* 3 */
            font-family: 'Dejavu Sans', sans-serif;
            /* 4 */
            font-feature-settings: normal;
            /* 5 */
            font-variation-settings: normal;
            /* 6 */
        }

        img,
        video {
            max-width: 100%;
            height: auto;
        }

        * {
            /* font-family: 'Inter', Arial, sans-serif; */
        }

        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }

        .gray {
            background-color: lightgray;
        }
    </style>

</head>
<body>

<table width="100%">
    <td>
        @if($brandLogoPath && file_exists($brandLogoPath))
            <img src="{{ $brandLogoPath }}" alt="" width="150" />
        @else
            <p style="font-size:16px; font-weight:bold">{{ config('branding.primary.name') }}</p>
        @endif
    </td>
    <td valign="top" style="text-align:right">
        <p style="font-size:14px; font-weight:bold">{{ $document->invoice_extended ?? $document->number_extended }}</p>
        <div style="text-align: right">
            {{ $mainFederation?->legal_name ?? $mainFederation?->name }}<br>
            {{ $mainFederation?->address }}<br>
            {{ $mainFederation?->zip_code }} {{ $mainFederation?->location }}, {{ config('branding.primary.country', 'Example Country') }}<br>
            NIF: {{ $mainFederation?->vat_number }}<br>
        </div>
    </td>
</table>

<br /><br />

<table width="100%" style="border-collapse: collapse; border: 1px solid #ccc;">
    <tr>
        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.name') }}</td>
        <td style="padding: 5px;">
            {{ $document->getOrganizationName() }}
        </td>

        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.city') }}</td>
        <td style="padding: 5px;">{{ $document->getCity() }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.address') }}</td>
        <td style="padding: 5px;">{{ $document->getAddress() }}</td>

        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.date') }}</td>
        <td style="padding: 5px;">{{ optional($document->created_at)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.vat_number') }}</td>
        <td style="padding: 5px;">{{ $document->getVatNumber() }}</td>

        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.postal_code') }}</td>
        <td style="padding: 5px;">{{ $document->getPostalCode() }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.member_number') }}</td>
        <td style="padding: 5px;">{{ $document->owner?->member_number ?? '' }}</td>

        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.country') }}</td>
        <td style="padding: 5px;">{{ $document->getCountry() }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold; padding: 5px;">{{ __('documents.pdf.notes') }}</td>
        <td colspan="3" style="padding: 5px;">{{ $document->notes ?? '' }}</td>
    </tr>
</table>


<br /><br />

<table width="100%" style="border-collapse: collapse;">
    <thead style="background-color: #1f2937; color: white;">
    <tr>
        <th style="padding: 5px;">#</th>
        <th style="padding: 5px; text-align: left;">{{ __('documents.pdf.description') }}</th>
        <th style="padding: 5px; text-align: right;">{{ __('documents.pdf.qty') }}</th>
        <th style="padding: 5px; text-align: right;">{{ __('documents.pdf.unit_price') }}</th>
        <th style="padding: 5px; text-align: right;">{{ __('documents.pdf.total') }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->details as $detail)
        <tr>
            <td style="padding: 5px; text-align: center;">{{ $loop->iteration }}</td>
            <td style="padding: 5px; text-align: left;">{{ $detail->owner->morphName ?? '' }}
                - {{ $detail->description }}</td>
            <td style="padding: 5px; text-align: right;">{{ $detail->quantity }}</td>
            <td style="padding: 5px; text-align: right;">{{ $detail->unit_value }}€</td>
            <td style="padding: 5px; text-align: right;">{{ $detail->total_value }}€</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3" style="padding: 5px;"></td>
        <td style="padding: 5px; text-align: right;">{{ __('documents.pdf.subtotal') }}</td>
        <td style="padding: 5px; text-align: right;">{{ $document->net_value }}€</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 5px;"></td>
        <td style="padding: 5px; text-align: right;">{{ __('documents.pdf.tax') }}</td>
        <td style="padding: 5px; text-align: right;">{{ $document->tax_value }}€</td>
    </tr>
    <tr>
        <td colspan="3" style="padding: 5px;"></td>
        <td style="padding: 5px; text-align: right;">{{ __('documents.pdf.total') }}</td>
        <td style="padding: 5px; text-align: right;">{{ $document->total_value }}€</td>
    </tr>
    </tfoot>
</table>

<br>

@if($document->stateName() !== 'paid')
<table width="100%" style="border-collapse: collapse; margin-top: 10px; border: 1px solid #ccc; background-color: #f9fafb;">
    <tr>
        <td style="text-align: left; font-style:italic; padding: 8px; font-size: 9px; color: #374151;">
            {{ __('documents.pdf.order_disclaimer') }}
        </td>
    </tr>
</table>
@endif

<br>

<table width="100%" style="border-collapse: collapse; margin-top: 10px; border: 1px solid #ccc;">
    <tr style="border-bottom:1px solid #ccc">
        <td style="text-align: left; font-style:italic;padding:5px;">
            {{ __('documents.invoice_compliance_en') }}
        </td>
    </tr>
    <tr>
        <td style="text-align: left; font-style:italic; padding:5px;">
            {{ __('documents.invoice_compliance_pt') }}
        </td>
    </tr>
</table>

<div style="width: 100%; text-align: center; position: fixed; bottom: 0; font-size:10px">
    Record ID: {{ $document->id }}
</div>
</body>
</html>
