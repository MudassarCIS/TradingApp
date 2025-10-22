<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Http\Controllers\Customer\WalletController;
use Illuminate\Http\Request;

class TestWalletHistory extends Command
{
    protected $signature = 'test:wallet-history {--user-email=}';
    protected $description = 'Test the wallet history functionality';

    public function handle()
    {
        $userEmail = $this->option('user-email') ?? 'main@test.com';
        
        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error("User with email {$userEmail} not found");
            return 1;
        }

        $this->info("Testing wallet history for user: {$user->email}");

        // Test transaction data
        $totalTransactions = Transaction::where('user_id', $user->id)->count();
        $this->info("Total transactions: {$totalTransactions}");

        if ($totalTransactions === 0) {
            $this->warn("No transactions found. Please run the seeders first:");
            $this->line("php artisan db:seed");
            return 1;
        }

        // Test transaction types
        $transactionTypes = Transaction::where('user_id', $user->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $this->info("\nTransaction types:");
        foreach ($transactionTypes as $type) {
            $this->line("  - {$type->type}: {$type->count} transactions");
        }

        // Test transaction statuses
        $transactionStatuses = Transaction::where('user_id', $user->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $this->info("\nTransaction statuses:");
        foreach ($transactionStatuses as $status) {
            $this->line("  - {$status->status}: {$status->count} transactions");
        }

        // Test wallet data
        $wallet = Wallet::where('user_id', $user->id)->where('currency', 'USDT')->first();
        if ($wallet) {
            $this->info("\nWallet information:");
            $this->line("  Balance: {$wallet->balance} USDT");
            $this->line("  Total Deposited: {$wallet->total_deposited} USDT");
            $this->line("  Total Withdrawn: {$wallet->total_withdrawn} USDT");
            $this->line("  Total Profit: {$wallet->total_profit} USDT");
        }

        // Test DataTables functionality
        $this->info("\nTesting DataTables functionality...");
        
        $request = new Request([
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => ''],
            'order' => [['column' => 0, 'dir' => 'desc']]
        ]);

        try {
            $controller = new WalletController();
            $response = $controller->history($request);
            $this->info("✅ Controller method works correctly");
        } catch (\Exception $e) {
            $this->error("❌ Controller error: " . $e->getMessage());
            return 1;
        }

        // Test recent transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        $this->info("\nRecent transactions:");
        foreach ($recentTransactions as $transaction) {
            $this->line("  - {$transaction->type} | {$transaction->status} | $" . number_format($transaction->amount, 2) . " | {$transaction->created_at->format('M d, Y H:i')}");
        }

        $this->info("\n✅ Wallet history functionality is working correctly!");
        $this->info("You can now access the wallet history page at:");
        $this->line("http://127.0.0.1:8000/customer/wallet/history");

        return 0;
    }
}
