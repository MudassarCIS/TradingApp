<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TradingEngineService;

class RunTradingEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trading:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the AI trading engine for all active agents';

    protected $tradingEngine;

    public function __construct(TradingEngineService $tradingEngine)
    {
        parent::__construct();
        $this->tradingEngine = $tradingEngine;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting AI Trading Engine...');
        
        try {
            $this->tradingEngine->executeTrading();
            $this->info('Trading engine completed successfully.');
        } catch (\Exception $e) {
            $this->error('Trading engine failed: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
