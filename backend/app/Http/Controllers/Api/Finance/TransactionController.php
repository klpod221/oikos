<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\Finance\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionService $transactionService
    ) {}

    /**
     * List Transactions
     *
     * Get a paginated list of transactions with optional filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $transactions = $this->transactionService->getTransactions(
            $request->user()->id,
            $request->only(['wallet_id', 'category_id', 'type', 'start_date', 'end_date', 'search', 'sort_by', 'sort_order']),
            $request->input('per_page', 10)
        );

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Create Transaction
     *
     * Create a new transaction (Income/Expense) and update wallet balance.
     */
    public function store(TransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->createTransaction(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaction created successfully',
            'data' => new TransactionResource($transaction),
        ], 201);
    }

    /**
     * Get Transaction
     *
     * Get details of a specific transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Update Transaction
     *
     * Update a transaction and adjust wallet balances accordingly.
     */
    public function update(TransactionRequest $request, Transaction $transaction): JsonResponse
    {
        Gate::authorize('update', $transaction);

        $transaction = $this->transactionService->updateTransaction(
            $transaction,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Transaction updated successfully',
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Delete Transaction
     *
     * Delete a transaction and revert its effect on wallet balance.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);

        $this->transactionService->deleteTransaction($transaction);

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted successfully',
        ]);
    }
}
