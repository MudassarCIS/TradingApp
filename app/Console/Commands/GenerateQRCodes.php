<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WalletAddress;
use Illuminate\Support\Facades\Storage;

class GenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR codes for wallet addresses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating QR codes for wallet addresses...');
        
        $walletAddresses = WalletAddress::all();
        
        foreach ($walletAddresses as $walletAddress) {
            if (!$walletAddress->qr_code_image) {
                // Create a simple QR code using a free API
                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($walletAddress->wallet_address);
                
                // Download and save the QR code
                $qrCodeContent = file_get_contents($qrCodeUrl);
                
                if ($qrCodeContent) {
                    $filename = 'qr-codes/' . time() . '_' . $walletAddress->symbol . '.png';
                    Storage::disk('public')->put($filename, $qrCodeContent);
                    
                    $walletAddress->update(['qr_code_image' => $filename]);
                    $this->info("Generated QR code for {$walletAddress->name}");
                } else {
                    $this->error("Failed to generate QR code for {$walletAddress->name}");
                }
            } else {
                $this->info("QR code already exists for {$walletAddress->name}");
            }
        }
        
        $this->info('QR code generation completed!');
    }
}
