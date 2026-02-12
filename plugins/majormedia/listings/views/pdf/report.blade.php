@extends('majormedia.listings::pdf.app')

@section('content')
    <section class="client-info avoid-break">
        <h2>Relevé Des Réservations</h2>
        <p>
            Propriétaire<br />
            <span>{{ $listing->owner_full_name }}</span>
        </p>
        <p>
            Nom de la propriété<br />
            <span>{{ $listing->title }}</span>
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
            Madame, Monsieur,<br />
            Nous vous prions de trouver ci-après votre relevé de réservations sur la période du {{ $startFormatted }} au
            {{ $endFormatted }}.
            Nous vous en souhaitons bonne réception.
        </p>

    </section>

    <section class="reservation-table">
        @php
            $counter = 0;
            $index = 0;
            $totalBookings = count($bookings);
            $totalCleaningFee = 0;
            $subTotalCommissionOTA = 0;
            $subTotalMontantSejour = 0;
            $subTotalCommissionVacaLoc = 0;
            $subTotalMontantApresCommission = 0;

        @endphp

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° de réservation</th>
                    <th>Source</th>
                    <th>Nbs nuit</th>
                    <th>Ménage CO/CI</th>
                    <th>Commissions OTA</th>
                    <th>Montant séjour</th> <!-- net amount-->
                    <th>Commissions Vacaloc</th>
                    <th>Montant après commissions</th><!-- owner_profit-->
                </tr>
            </thead>
            <tbody>

                @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('Y/m/d') }}</td>
                        <td>{{ $booking->reference }}</td>
                        <td>
                            @if ($booking->canal == 1)
                                Vacal
                            @elseif ($booking->canal == 2)
                                Airbnb
                            @elseif ($booking->canal == 3)
                                Bookings
                            @else
                                VRBO
                            @endif
                        </td>
                        @php
                            $checkInDate = new DateTime($booking->check_in);
                            $checkOutDate = new DateTime($booking->check_out);
                            $interval = $checkInDate->diff($checkOutDate);
                        @endphp
                        <td>{{ $interval->days }}</td>
                        <td>@php
                            $totalCleaningFee += $booking->clearning_fee;
                        @endphp
                            {{ number_format($booking->clearning_fee, 2) }}</td>
                        @endphp
                        <td>
                            @php
                                $subTotalCommissionOTA += $booking->ota_commission;
                            @endphp
                            {{ number_format($booking->ota_commission, 2) }}
                        </td>

                        <td>
                            @php
                                $subTotalMontantSejour += $booking->net_amount;
                            @endphp
                            {{ number_format($booking->net_amount, 2) }}
                        </td>
                        <td>
                            @php
                                $subTotalCommissionVacaLoc += $booking->vacaloc_commission;
                            @endphp

                            {{ number_format($booking->vacaloc_commission, 2) }}
                        </td>
                        <td>
                            @php
                                $subTotalMontantApresCommission += $booking->owner_profit;
                            @endphp

                            {{ number_format($booking->owner_profit, 2) }}
                        </td>

                    </tr>

                    @php
                        $counter++;
                    @endphp

                    @if ($counter == $index + 12 && !$loop->last)
                        @php
                            $counter = 0;
                            $index = 8;
                        @endphp
            </tbody>
        </table>

        {{-- Insert Summary --}}
        {{-- <section class="summary" style="margin: 20px 0px 0px">
            <div class="left">
                @foreach ($expenses as $expense)
                    <p>{{ $expense->label }} ({{ number_format($expense->amount, 2, ',', ' ') }} €)</p>
                @endforeach
            </div>
            @php
                $subtotalNet = $subTotalMontantApresCommission;
                $totalExpenses = $expenses->sum('amount');
                $netTotal = $subtotalNet - $totalExpenses;
            @endphp

            <div class="right">
                <p>Sous total net <span style="color: #203532">{{ number_format($subtotalNet, 2, ',', ' ') }}</span></p>
                <p>Total Débours <span style="color:#E0565B">-{{ number_format($totalExpenses, 2, ',', ' ') }}</span></p>
                <p><strong>Solde net en votre faveur (Eur)</strong>
                    <span style="color:#30B3A6">{{ number_format($netTotal, 2, ',', ' ') }}</span>
                </p>
            </div>
        </section> --}}

        <div style="page-break-after:always;"></div>

        {{-- Start new table --}}
        <table style="margin: 50px 0px 10px">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° de réservation</th>
                    <th>Source</th>
                    <th>Nbs nuit</th>
                    <th>Ménage CO/CI</th>
                    <th>Commissions OTA</th>
                    <th>Montant séjour</th>
                    <th>Commissions Vacaloc</th>
                    <th>Montant après commissions</th>
                </tr>
            </thead>
            <tbody>
                @endif
                @endforeach

                {{-- Final Summary at the end of the document --}}
                <?php
                // Calculate total nights
                $totalNights = $bookings->sum(function ($booking) {
                    // Ensure check_in and check_out are Carbon instances
                    $checkIn = \Carbon\Carbon::parse($booking->check_in);
                    $checkOut = \Carbon\Carbon::parse($booking->check_out);
                
                    // Get the difference in days
                    return $checkIn->diffInDays($checkOut);
                });
                
                // Calculate 20% commission
                $vacaloc_commission = $bookings->sum('amount') * 0.2;
                
                // Calculate amount after commission
                $stay_amount_after_commission = $bookings->sum('amount') - $vacaloc_commission;
                ?>

                <tr class="subtotal">
                    <td colspan="3"><strong>Sous Total</strong></td>
                    <td>{{ $totalNights }}</td>
                    <td>{{ $totalCleaningFee }}</td>
                    <td>

                        {{ number_format($subTotalCommissionOTA, 2) }}
                    </td>
                    <td>{{ $subTotalMontantSejour }}</td>
                    <td>
                        {{ number_format($subTotalCommissionVacaLoc, 2) }}
                    </td>
                    <td>{{ $subTotalMontantApresCommission }}</td>
                </tr>

            </tbody>
        </table>

        {{-- Final Summary Section --}}
        <section class="summary" style="margin: 20px 0px 0px">
            @if ($expenses->isEmpty())
                <div style="color: #203532; display: block; font-weight: 600;">Débours</div>
                <div class="center">
                    <p>Aucune dépense pour le moment</p>
                </div>
            @else
                <div style="color: #203532; display:block; font-weight:600;">Débours</div>
                <div class="left">
                    @foreach ($expenses as $expense)
                        @php
                            $flippedAmount = -1 * $expense->amount;
                        @endphp
                        <p style="color:#aeaeae">
                            {{ $expense->label }} ({{ number_format($flippedAmount, 2, ',', ' ') }} €)
                        </p>
                    @endforeach
                </div>

                @php
                    $subtotalNet = $subTotalMontantApresCommission;

                    // Flip the sign of each expense before summing
                    $totalExpenses = $expenses->sum(function ($e) {
                        return -1 * $e->amount;
                    });

                    $netTotal = $subtotalNet + $totalExpenses;
                @endphp

                <div class="right">
                    <p>Sous total net <span style="color: #203532">{{ number_format($subtotalNet, 2, ',', ' ') }}</span>
                    </p>
                    <p>Total Débours
                        <span style="color:#E0565B">
                            {{ number_format($totalExpenses, 2, ',', ' ') }}
                        </span>
                    </p>
                    <p><strong>Solde net en votre faveur (Eur)</strong>
                        <span style="color:#30B3A6">{{ number_format($netTotal, 2, ',', ' ') }}</span>
                    </p>
                </div>
            @endif
        </section>


    </section>
@endsection
