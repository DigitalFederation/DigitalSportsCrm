<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Digital Sports CRM') }}</title>
    <style>
        @page {
            margin: 0px;
        }

        /* Essential CSS for PDF */
        html, body, div, span, h1, h2, h3, h4, h5, h6, p, a, img, table, tr, th, td {
            margin: 0;
            padding: 0;
            border: 0;
            vertical-align: baseline;
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
            line-height: 1.5;
        }

        .container {
            border: 1px solid black;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .font-bold {
            font-weight: bold;
        }

        .font-normal {
            font-weight: normal;
        }

        .mt-4 {
            margin-top: 1rem;
        }

        .mt-8 {
            margin-top: 2rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .mx-auto {
            margin-left: auto;
            margin-right: auto;
        }

        .horizontal-separator {
            border-bottom: 1px solid #eee;
            margin: 1rem auto;
            width: 70%;
        }

        .card_logo_cmas {
            width: 58px;
        }

        table {
            width: 100%;
            text-align: center;
            border-collapse: collapse;
            margin: 0 auto;
        }

        td {
            padding: 0.5rem;
        }

        .small_flag {
            width: 15px;
            vertical-align: middle;
        }

        .letter-spacing-lg {
            letter-spacing: 0.125em;
        }

        .flex {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .inline-flex {
            display: inline-flex;
            align-items: center;
        }

        .text-blue-cmas {
            color: #0f6eb5;
        }

        .inner-container {

            padding: 35px;
        }

        .inner-container-border {
            padding: 15px;
        }
    </style>
</head>
<body>

@include('web.layout.print')

</body>
</html>
