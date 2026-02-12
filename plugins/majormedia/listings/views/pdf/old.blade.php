@extends('majormedia.listings::pdf.app')
@section('content')
    <section class="client-info avoid-break">
        <h2>Releve Des Reservations</h2>
        <p>
            Ref client<br />
            <span>{{ $listing->id }}</span>
        </p>
        <p>
            Propriétaire<br />
            <span>{{ $listing->user->name }} {{ $listing->user->surname }}</span>
        </p>
        <p>
            adresse logement<br />
            <span>{{ $listing->address }}</span>
        </p>
    </section>
    <section class="message">
        <p>
            Madame, Monsieur,<br />
            Nous vous prions de trouver ci après votre relevé de réservations sur la période du 01/07/2024 au
            31/07/2024. Nous vous en souhaitons bonne réception.
        </p>
    </section>

    <section class="reservation-table">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° de réservation</th>
                    <th>Source</th>
                    <th>Nbs nuit</th>
                    <th>Ménage CO/CI</th>
                    <th>Commissions suit</th>
                    <th>montant séjour</th>
                    <th>Commissions vacaloc</th>
                    <th>montant séjour</th>
                </tr>
            </thead>
            <tbody>
                @php

                    $counter = 0;
                    $index = 0; // Initialize the counter
                @endphp
                @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->created_at->format('Y/m/d') }}</td>
                        <td>{{ $booking->reference }}</td>
                        <td>{{ ((($booking->canal == 1 ? 'vacal' : 2) ? 'airbnb' : 3) ? 'bookings' : 4) ? 'canal 4' : null }}
                        </td>
                        <?php
                        $checkInDate = new DateTime($booking->check_in);
                        $checkOutDate = new DateTime($booking->check_out);
                        $interval = $checkInDate->diff($checkOutDate);
                        ?>
                        <td>{{ $interval->days }}</td>
                        <td>{{ number_format($booking->amount * 0.15, 2) }}</td>
                        <td>{{ number_format($booking->amount, 2) }}</td>
                        <td>{{ number_format($booking->amount * 0.1, 2) }}</td>
                        <td>{{ number_format($booking->amount - $booking->amount * 0.2, 2) }}</td>
                        <td>{{ number_format($booking->amount - $booking->amount * 0.1, 2) }}</td>
                    </tr>
                    @php
                        $counter++; // Increment the counter
                    @endphp
                    @if ($counter == $index + 11)
                        @php
                            $counter = 0;
                            $index = 8;

                        @endphp
            </tbody>
        </table>
        <section class="summary">
            <p>Achat petite fourniture cuisine (30)</p>
            <div>
                <p>Sous total net <span>1771,02</span></p>
                <p>Total Débours <span>-30,00</span></p>
                <p>
                    <strong>Solde net en votre faveur (Eur)</strong> <span>1741,02</span>
                </p>
            </div>
        </section>
        <div style="page-break-after:always;"></div>
        <table style="margin: 50px 0px 0px">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° de réservation</th>
                    <th>Source</th>
                    <th>Nbs nuit</th>
                    <th>Ménage CO/CI</th>
                    <th>Commissions suit</th>
                    <th>montant séjour</th>
                    <th>Commissions vacaloc</th>
                    <th>montant séjour</th>

                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 0; // Reset the counter after the page break
                @endphp
                @endif
                @endforeach
                <tr class="subtotal">
                    <td colspan="3"><strong>Sous Total</strong></td>
                    <td>{{ $bookings->sum('cleaning_fee') }}</td>
                    <td>{{ $bookings->sum('commission') }}</td>
                    <td>{{ $bookings->sum('amount') }}</td>
                    <td>{{ $bookings->sum('vacaloc_commission') }}</td>
                    <td>{{ $bookings->sum('stay_amount_after_commission') }}</td>
                    <td>{{ $bookings->sum('stay_amount_after_commission') }}</td>
                </tr>
            </tbody>
        </table>

    </section>
@endsection
