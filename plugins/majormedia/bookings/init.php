<?php

use MajorMedia\Bookings\Models\Booking;

Event::listen('majormedia.bookings::hide&showBookingsAttributes', function ($minView) {
    Booking::extend(function ($model) use ($minView) {
        switch ($minView) {
            case 1:
                $hiddenAttributes = [
                    'gross_amount',
                    'clearning_fee',
                    'tax',
                    'vacaloc_commission',
                    'ota_commission',
                    'total_amount_order',
                    'statusAlias',
                    'status'
                ];
                $visibleAttributes = [];
                break;
            case 2:
                break;

        }
        $model->setHidden(array_merge($model->getHidden(), $hiddenAttributes));
        $model->setVisible(array_merge($model->getVisible(), $visibleAttributes));
    });
});