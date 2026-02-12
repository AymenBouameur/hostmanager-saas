@extends('majormedia.listings::pdf.app')

@section('content')
    <section class="client-info avoid-break">
        <h2>Facture</h2>
        <p>
            Ref client<br />
            <span>{{ $listing->id }}</span>
        </p>
        <p>
            Propriétaire<br />
            <span>{{ $listing->owner_full_name }}</span>
        </p>
        <p>
            Adresse logement<br />
            <span>{{ $listing->address }}</span>
        </p>
    </section>

    <section class="message">
        @php
            use Carbon\Carbon;

            try {
                $startFormatted = Carbon::parse($startDate)->format('d/m/Y');
                $endFormatted = Carbon::parse($endDate)->format('d/m/Y');
            } catch (\Exception $e) {
                $startFormatted = $startDate;
                $endFormatted = $endDate;
            }
        @endphp
        <p>
            Agadir, le {{ \Carbon\Carbon::parse($issue_date)->format('d/m/Y') }}<br>
            Madame, Monsieur,<br>
            Nous vous prions de bien vouloir trouver ci-joint votre facture pour la période du {{ $startDate }} au
            {{ $endDate }}.
            Nous vous en souhaitons bonne réception.
        </p>


    </section>

    <section class="reservation-table">

        <table>
            <thead>
                <tr>
                    <th style="text-align: center">Description</th>
                    <th style="text-align: center">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total HT</td>
                    <td>{{ $totalHT }} €</td>
                </tr>
                <tr>
                    <td>TVA ({{ $tva }} %)</td>
                    <td>{{ $montantTVA }} €</td>
                </tr>
                <tr>
                    <td>Total TTC</td>
                    <td>{{ $totalTTC }} €</td>
                </tr>

            </tbody>
        </table>
        <div style="margin-top: 20px">
            <p style="color:#aeaeae">Facture acquittée. </p>
        </div>

    </section>
@endsection
