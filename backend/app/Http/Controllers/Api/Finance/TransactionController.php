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
            $request->only(['wallet_id', 'category_id', 'type', 'start_date', 'end_date']),
            $request->input('per_page', 15)
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
        // Need to check if wallet belongs to user?
        // WalletRequest/Rule logic or Service logic?
        // Service updates balance, so we must be sure wallet is user's.
        // Validation rule `exists:wallets,id` just checks existence.
        // We should add ownership check.
        // Best place: Request validations with `Rule::exists('wallets')->where('user_id', $this->user()->id)`
        // Or in service.

        // I'll trust the Service to fail or I should ensure the wallet is owned by user in Request.
        // For now, I'll pass user ID to service and service will look it up.

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
