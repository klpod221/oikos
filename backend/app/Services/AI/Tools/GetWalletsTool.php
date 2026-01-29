<?php

namespace App\Services\AI\Tools;

use App\Models\Wallet;

/**
 * Tool to get user's wallets with balance information.
 */
class GetWalletsTool extends AITool
{
    public function name(): string
    {
        return 'get_wallets';
    }

    public function description(): string
    {
        return 'Get the list of user wallets with their current balance. Use this to check available funds or select a wallet for transactions.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => new \stdClass(),
        ];
    }

    public function execute(array $arguments, int $userId): array
    {
        $wallets = Wallet::where('user_id', $userId)
            ->select(['id', 'name', 'balance', 'currency', 'is_default'])
            ->get();

        if ($wallets->isEmpty()) {
            return [
                'success' => true,
                'result' => 'No wallets found. Please create a wallet first.',
            ];
        }

        $list = "Wallets:\n";
        $balancesByCurrency = [];

        foreach ($wallets as $wallet) {
            $balance = number_format($wallet->balance, 0, ',', '.');
            $default = $wallet->is_default ? ' ⭐' : '';
            $list .= "- {$wallet->name}: {$balance} {$wallet->currency}{$default}\n";

            $currency = $wallet->currency ?? 'VND';
            $balancesByCurrency[$currency] = ($balancesByCurrency[$currency] ?? 0) + $wallet->balance;
        }

        // Format totals by currency
        $totals = [];
        foreach ($balancesByCurrency as $currency => $total) {
            $totals[] = number_format($total, 0, ',', '.') . " {$currency}";
        }
        $list .= "\nTotal: " . implode(' | ', $totals);

        return [
            'success' => true,
            'result' => $list,
        ];
    }
}
