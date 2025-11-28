<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\UserInvoice;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill existing invoices with unique invoice IDs
        $invoices = UserInvoice::whereNull('invoice_id')->get();
        
        foreach ($invoices as $invoice) {
            $invoice->invoice_id = UserInvoice::generateUniqueInvoiceId();
            $invoice->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration doesn't need to be reversed
        // as we're just backfilling data
    }
};

