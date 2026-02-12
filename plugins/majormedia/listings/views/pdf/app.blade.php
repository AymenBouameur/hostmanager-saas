<!doctype html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Releve Des Reservations</title>
</head>

<style>
    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-Bold.ttf') }}) format("truetype");
        font-weight: 700;
        font-style: normal;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-BoldItalic.ttf') }}) format("truetype");
        font-weight: 700;
        font-style: italic;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-ExtraBold.ttf') }}) format("truetype");
        font-weight: 800;
        font-style: normal;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-ExtraBoldItalic.ttf') }}) format("truetype");
        font-weight: 800;
        font-style: italic;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-Light.ttf') }}) format("truetype");
        font-weight: 300;
        font-style: normal;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-LightItalic.ttf') }}) format("truetype");
        font-weight: 300;
        font-style: italic;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-Medium.ttf') }}) format("truetype");
        font-weight: 500;
        font-style: normal;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-MediumItalic.ttf') }}) format("truetype");
        font-weight: 500;
        font-style: italic;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-Regular.ttf') }}) format("truetype");
        font-weight: 400;
        font-style: normal;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-SemiBold.ttf') }}) format("truetype");
        font-weight: 600;
        font-style: normal;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-SemiBoldItalic.ttf') }}) format("truetype");
        font-weight: 600;
        font-style: italic;
    }

    @font-face {
        font-family: 'Jost';
        src: url({{ storage_path('fonts/Jost-Italic.ttf') }}) format("truetype");
        font-weight: 400;
        font-style: italic;
    }

    :root {
        --primary-color: rgba(48, 179, 166, 1);
        --secondary-color: rgba(32, 53, 50, 1);
    }

    /* @page {
        margin: 100px 250px;

    } */

    * {
        margin: 0;
        padding: 0;
    }

    .page-break {
        page-break-after: always;
        clear: both;
        /* DOMPDF recognizes this for page breaks */
    }


    body {
        font-family: "Jost", serif;
        margin: 0 auto;
        color: #333;
        max-width: 794px;
        /* height: 1123px; */
        position: relative;
        padding-top: 60px;
        padding-bottom: 80px;
        padding-left: 20px;
        padding-right: 20px;
    }

    header {
        position: fixed;
        margin: 0 auto;
        max-width: 794px;
        top: 0cm;
        left: 0cm;
        right: 0cm;
        height: 70px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        color: rgba(48, 179, 166, 1);
        font-size: 12px;
        font-weight: 500;

        padding: 8px 20px;
        text-align: center;
        /* background-color: black */
    }

    footer {
        position: fixed;
        margin: 0 auto;
        max-width: 794px;
        bottom: 0;
        left: 0;
        right: 0;
        height: 80px;
        width: 100%;
        color: white;
        font-size: 12px;
        z-index: 1000;
        font-size: 12px;
        color: rgba(48, 179, 166, 1);
        padding: 10px 0;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        text-align: center;
        /* background-color: black */
    }

    header .table {
        display: table;
        width: 100%;

    }

    header .left,
    header .logo,
    header .right {
        display: table-cell;
        vertical-align: middle;
    }

    header .left {
        text-align: left;
        margin-left: 10px;
        font-size: 12px;
        max-width: 33%;
    }

    header .right {
        text-align: right;
        margin-right: 10px;
        font-size: 12px;
        max-width: 33%;
    }

    header .logo {
        /* display: table-cell; */
        height: 63px;
        text-align: center;
        vertical-align: middle;
    }

    header .logo img {
        display: block;
    }


    table {
        page-break-before: auto;
        margin-top: 20px;
    }


    .client-info h2 {
        font-weight: 500;
        font-size: 20px;
        color: rgba(32, 53, 50, 1);
        margin-top: 8px;
        margin-bottom: 8px
    }

    .client-info p {
        font-weight: 500;
        font-size: 14px;
        color: #aeaeae;
    }

    .client-info p span {
        font-weight: 600;
        font-size: 20px;
        color: rgba(32, 53, 50, 1);
    }

    .message p {
        font-weight: 600;
        font-size: 14px;
        color: #aeaeae;
        margin: 10px 0px;
    }



    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    thead {
        display: table-header-group;
    }


    thead th {
        background-color: rgba(48, 179, 166, 0.1);
        font-size: 14px;
        text-align: left;
        /* Avoid text-align: start */
        color: #888;
        border: 1px solid #ddd;
        padding: 4px;
    }

    tbody td {
        font-size: 13px;
        color: rgba(0, 0, 0, 0.5);
        text-align: center;
        border: 1px solid #ddd;
        padding: 4px;
    }

    .subtotal td {
        font-weight: bold;
        background-color: #f4f4f4;
    }

    .summary {
        max-width: 100%;
        white-space: nowrap;
        /* Prevents wrapping of text */
    }

    .summary p {
        font-weight: 600;
        font-size: 13px;
        color: #626262;
    }

    .summary div {
        display: inline-block;
        vertical-align: top;
    }

    .summary div.right {
        text-align: right;
        float: right;
        width: 50%;
    }

    .summary div.left {
        text-align: left;
        float: left;
        width: 50%;
    }


    .summary div.right p {
        font-size: 13px;
        color: #aeaeae;
    }

    .summary div.right p span.sub_total {
        font-size: 14px;
        margin-left: 10px;
        color: rgba(32, 53, 50, 1);
    }

    .summary div.right p span.total {
        font-size: 14px;
        margin-left: 10px;
        color: red;
    }

    .summary div.right p span.solde {
        font-size: 14px;
        margin-left: 10px;
        color: rgba(48, 179, 166, 1);
    }

    .main {
        padding: 20px 0px;
    }
</style>

<body>
    <header>
        <table style="width: 100%; table-layout: fixed;">
            <tr>
                <th style="text-align: left; vertical-align: middle;">
                    <div style="text-align: left;">
                        Agadir
                    </div>
                </th>
                <th style="text-align: center; vertical-align: middle;">
                    <div style="text-align: center;">
                        <img src="{{ $logoPath }}" alt="Vacaloc logo" />
                    </div>
                </th>
                <th style="text-align: right; vertical-align: middle;">
                    @php
                        use Carbon\Carbon;

                        $endDateParsed = null;

                        if (!empty($endDate)) {
                            try {
                                $endDateParsed = Carbon::createFromFormat('d/m/Y', $endDate)->startOfDay();
                            } catch (\Exception $e) {
                                $endDateParsed = null;
                            }
                        }
                        $today = now()->startOfDay();
                    @endphp

                    <div style="text-align: right;">
                        @if ($endDateParsed && $today->greaterThan($endDateParsed))
                            {{ $endDateParsed->format('d/m/Y') }}
                        @else
                            {{ $today->format('d/m/Y') }}
                        @endif
                    </div>

                </th>
            </tr>
        </table>
    </header>
    <footer>
        <p>
            ICE : 003563572000087 - IF : 66062548 - TP : 55005036 - CNSS : 5649084
        </p>
        <p>
            Email : contact@vacaloc.com - MA : +212 6 69 57 04 20 - FR : +33 7 54 38 74 54
        </p>
        <p>
            Bureau Nr 842 étage 4 IMM 8 Résidence khalij Annakhil Founty - Agadir
        </p>
    </footer>
    
    <div style="page-break-after:auto;">
        <main class="main">
            @yield('content')
        </main>
