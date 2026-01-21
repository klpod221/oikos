<?php

namespace App\Http\Controllers\Api\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\WalletRequest;
use App\Http\Resources\WalletResource;
use App\Models\Wallet;
use App\Services\Finance\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * List Wallets
     *
     * Get a list of all wallets belonging to the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $wallets = $this->walletService->getWallets($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => WalletResource::collection($wallets),
        ]);
    }

    /**
     * Create Wallet
     *
     * Create a new wallet for the user.
     */
    public function store(WalletRequest $request): JsonResponse
    {
        $wallet = $this->walletService->createWallet(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Wallet created successfully',
            'data' => new WalletResource($wallet),
        ], 201);
    }

    /**
     * Get Wallet
     *
     * Get details of a specific wallet.
     */
    public function show(Wallet $wallet): JsonResponse
    {
        Gate::authorize('view', $wallet);

        return response()->json([
            'success' => true,
            'data' => new WalletResource($wallet),
        ]);
    }

    /**
     * Update Wallet
     *
     * Update details of an existing wallet.
     */
    public function update(WalletRequest $request, Wallet $wallet): JsonResponse
    {
        Gate::authorize('update', $wallet);

        $wallet = $this->walletService->updateWallet($wallet, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Wallet updated successfully',
            'data' => new WalletResource($wallet),
        ]);
    }

    /**
     * Delete Wallet
     *
     * Delete a wallet.
     */
    public function destroy(Wallet $wallet): JsonResponse
    {
        Gate::authorize('delete', $wallet);

        $this->walletService->deleteWallet($wallet);

        return response()->json([
            'success' => true,
            'message' => 'Wallet deleted successfully',
        ]);
    }
}
