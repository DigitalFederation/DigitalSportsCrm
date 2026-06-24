<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Contracts\View\View;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentTransactionController extends Controller
{
    public function index(): View
    {
        $transactions = QueryBuilder::for(PaymentTransaction::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('payment_method_id'),
                AllowedFilter::scope('filter_date_from', 'created_after'),
                AllowedFilter::scope('filter_date_to', 'created_before'),
            ])
            ->with(['document', 'paymentMethod'])
            ->orderByDesc('created_at')
            ->paginate(25)
            ->appends(request()->query());

        $paymentMethods = PaymentMethod::withoutGlobalScope('enabled')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $statusOptions = [
            ['id' => 'pending', 'name' => __('Pending')],
            ['id' => 'success', 'name' => __('Success')],
            ['id' => 'failed', 'name' => __('Failed')],
        ];

        $stats = $this->getStatistics();

        return view('web.admin.payment-transactions.index', compact(
            'transactions',
            'paymentMethods',
            'statusOptions',
            'stats'
        ));
    }

    public function show(string $id): View
    {
        $transaction = PaymentTransaction::with(['document', 'paymentMethod'])
            ->findOrFail($id);

        return view('web.admin.payment-transactions.show', compact('transaction'));
    }

    private function getStatistics(): array
    {
        return [
            'total' => PaymentTransaction::count(),
            'pending' => PaymentTransaction::where('status', 'pending')->count(),
            'success' => PaymentTransaction::where('status', 'success')->count(),
            'failed' => PaymentTransaction::where('status', 'failed')->count(),
            'total_amount' => PaymentTransaction::where('status', 'success')->sum('amount'),
        ];
    }
}
