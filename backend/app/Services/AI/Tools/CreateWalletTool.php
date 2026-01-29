<?php

namespace App\Services\AI\Tools;

use App\Models\Wallet;
use Illuminate\Support\Facades\Log;

/**
 * Tool to create a new wallet.
 */
class CreateWalletTool extends AITool
{
    public function name(): string
    {
        return 'create_wallet';
    }

    public function description(): string
    {
        return 'Create a new wallet for the user. Useful for organizing finances (e.g., separate wallet for savings, daily expenses).';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Name of the wallet (e.g., "Savings", "Cash")',
                ],
                'initial_balance' => [
                    'type' => 'number',
                    'description' => 'Initial balance in VND (default: 0)',
                ],
                'currency' => [
                    'type' => 'string',
                    'description' => 'Currency code (default: VND)',
                ],
            ],
            'required' => ['name'],
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        try {
            $name = $arguments['name'] ?? '';
            $initialBalance = (float) ($arguments['initial_balance'] ?? 0);
            $currency = $arguments['currency'] ?? 'VND';

            if (empty($name)) {
                return [
                    'success' => false,
                    'result' => 'Wallet name is required.',
                ];
            }

            // Check if wallet with same name exists
            $exists = Wallet::where('user_id', $userId)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                return [
                    'success' => false,
                    'result' => "A wallet named '{$name}' already exists.",
                ];
            }

            $wallet = Wallet::create([
                'user_id' => $userId,
                'name' => $name,
                'balance' => $initialBalance,
                'currency' => $currency,
                'is_default' => false,
            ]);

            $balance = number_format($initialBalance, 0, ',', '.');

            return [
                'success' => true,
                'result' => "Created wallet '{$name}' with balance {$balance} {$currency}.",
                'data' => $wallet,
            ];
        } catch (\Exception $e) {
            Log::error('CreateWalletTool failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'result' => 'Failed to create wallet: ' . $e->getMessage(),
            ];
        }
    }
}
