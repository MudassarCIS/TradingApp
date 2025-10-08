<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WalletAddress;

class WalletAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $walletAddresses = [
            [
                'name' => 'Bitcoin',
                'symbol' => 'BTC',
                'wallet_address' => '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa',
                'network' => 'BTC',
                'instructions' => 'Only send Bitcoin (BTC) to this address. Other cryptocurrencies will be lost.',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Tether USD',
                'symbol' => 'USDT',
                'wallet_address' => 'TQn9Y2khEsLJW1ChVWFMSMeRDow5KcbLSE',
                'network' => 'TRC20',
                'instructions' => 'Only send USDT (TRC20) to this address. Other cryptocurrencies will be lost.',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Ethereum',
                'symbol' => 'ETH',
                'wallet_address' => '0x742d35Cc6634C0532925a3b8D4C9db96C4b4d8b6',
                'network' => 'ERC20',
                'instructions' => 'Only send Ethereum (ETH) to this address. Other cryptocurrencies will be lost.',
                'is_active' => true,
                'sort_order' => 3
            ]
        ];

        foreach ($walletAddresses as $walletAddress) {
            WalletAddress::create($walletAddress);
        }
    }
}
