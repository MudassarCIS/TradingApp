<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BinanceApiService;
use App\Services\BingXApiService;
use Illuminate\Support\Facades\Log;

class WebSocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:start {--port=8080}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start WebSocket server for real-time market data';

    protected $binanceApi;
    protected $bingxApi;
    protected $clients = [];
    protected $symbols = ['BTCUSDT', 'ETHUSDT', 'ADAUSDT', 'BNBUSDT', 'XRPUSDT', 'SOLUSDT'];

    public function __construct()
    {
        parent::__construct();
        $this->binanceApi = new BinanceApiService();
        $this->bingxApi = new BingXApiService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $port = $this->option('port');
        
        $this->info("Starting WebSocket server on port {$port}...");
        
        // Create WebSocket server
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($server, '0.0.0.0', $port);
        socket_listen($server);
        
        $this->info("WebSocket server started on port {$port}");
        $this->info("Press Ctrl+C to stop the server");
        
        // Start market data broadcasting
        $this->startMarketDataBroadcast($server);
        
        socket_close($server);
    }
    
    protected function startMarketDataBroadcast($server)
    {
        while (true) {
            // Check for new connections
            $read = array_merge([$server], $this->clients);
            $write = [];
            $except = [];
            
            if (socket_select($read, $write, $except, 0, 100000) > 0) {
                foreach ($read as $socket) {
                    if ($socket === $server) {
                        // New connection
                        $client = socket_accept($server);
                        $this->clients[] = $client;
                        $this->info("New client connected. Total clients: " . count($this->clients));
                    } else {
                        // Handle client data
                        $data = socket_read($socket, 1024);
                        if ($data === false || $data === '') {
                            // Client disconnected
                            $key = array_search($socket, $this->clients);
                            if ($key !== false) {
                                unset($this->clients[$key]);
                                $this->clients = array_values($this->clients);
                            }
                            socket_close($socket);
                        }
                    }
                }
            }
            
            // Broadcast market data every 5 seconds
            static $lastBroadcast = 0;
            if (time() - $lastBroadcast >= 5) {
                $this->broadcastMarketData();
                $lastBroadcast = time();
            }
        }
    }
    
    protected function broadcastMarketData()
    {
        if (empty($this->clients)) {
            return;
        }
        
        $marketData = [];
        
        foreach ($this->symbols as $symbol) {
            try {
                $price = $this->binanceApi->getCurrentPrice($symbol) ?? $this->bingxApi->getCurrentPrice($symbol);
                $ticker = $this->binanceApi->get24hrTicker($symbol) ?? $this->bingxApi->get24hrTicker($symbol);
                
                if ($price && $ticker) {
                    $marketData[] = [
                        'symbol' => $symbol,
                        'price' => $price,
                        'change_24h' => (float) $ticker['priceChangePercent'],
                        'volume' => (float) $ticker['volume'],
                        'high_24h' => (float) $ticker['highPrice'],
                        'low_24h' => (float) $ticker['lowPrice'],
                        'timestamp' => time()
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Error fetching market data for {$symbol}: " . $e->getMessage());
            }
        }
        
        if (!empty($marketData)) {
            $message = json_encode([
                'type' => 'market_data',
                'data' => $marketData,
                'timestamp' => time()
            ]);
            
            foreach ($this->clients as $client) {
                socket_write($client, $message, strlen($message));
            }
        }
    }
}
