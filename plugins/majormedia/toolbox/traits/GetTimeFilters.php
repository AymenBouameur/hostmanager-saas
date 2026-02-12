<?php namespace MajorMedia\ToolBox\Traits;

use Event;

trait GetTimeFilters{
    use JsonAbort;

    public function getTimeFilters($maxIntervalSeconds=2764800){
        try {
            $from = \Request::input('from', \Carbon\Carbon::today()->startOfMonth()->timestamp);
            $to = \Request::input('to', \Carbon\Carbon::today()->endOfMonth()->timestamp);
        } catch (\Exception $e) {
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::TIMESTAMP_INVALID,
            ], 400);
        }

        if($to < $from)
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::FROM_AFTER_TO,
            ], 400);

        if($to - $from > $maxIntervalSeconds)
            return $this->JsonAbort([
                'status' => 'error',
                'code' => \ErrorCodes::FILTER_INTERVAL_TOO_BIG,
            ], 400);

        return compact('from', 'to');
    }
}
