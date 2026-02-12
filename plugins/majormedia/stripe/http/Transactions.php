<?php namespace Majormedia\Stripe\Http;

use Backend\Classes\Controller;
use Majormedia\Stripe\Models\Transaction;
use Majormedia\ToolBox\Traits\GetValidatedInput;

class Transactions extends Controller
{
    use GetValidatedInput;
    use \Majormedia\ToolBox\Traits\RenameArrayKey;

    public function index()
    {
        $companyId = \Request::input('company_id');
        $query = Transaction::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($term = \Request::input('term')) {
            $query->where('ref', 'like', "%$term%");
        }

        return response()->json(
            array_merge(
                ['status' => 'success'],
                $this->renameArrayKey(
                    $query->orderBy('created_at', 'desc')
                        ->paginate(min(10, \Request::input('size', 10)))
                        ->toArray(),
                    'data',
                    'transactions',
                ),
            ),
            200
        );
    }

    public function stats()
    {
        $companyId = \Request::input('company_id');
        $now = \Carbon\Carbon::now();
        $year = (int) \Request::input('year', $now->year);
        $month = (int) \Request::input('month', $now->month);

        $baseQuery = Transaction::where('paid', true);
        if ($companyId) {
            $baseQuery->where('company_id', $companyId);
        }

        $totalYear = (clone $baseQuery)->whereYear('created_at', $year)->sum('amount');
        $totalMonth = (clone $baseQuery)->whereYear('created_at', $year)->whereMonth('created_at', $month)->sum('amount');
        $totalWeek = (clone $baseQuery)->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])->sum('amount');
        $totalGain = (clone $baseQuery)->sum('amount');

        return response()->json([
            'status'      => 'success',
            'total_gain'  => (float) $totalGain,
            'total_year'  => (float) $totalYear,
            'total_month' => (float) $totalMonth,
            'total_week'  => (float) $totalWeek,
        ], 200);
    }
}
