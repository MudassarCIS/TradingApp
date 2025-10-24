@extends('layouts.customer-layout')

@section('title', 'Live Market Data - AI Trade App')
@section('page-title', 'Live Market Data')

@push('styles')
<style>
    .market-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .market-header h1 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .market-header p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }
    
    .connection-status {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .connection-status.connected {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .connection-status.disconnected {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .connection-status.connecting {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
        position: relative;
    }
    
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .chart-title {
        color: var(--primary-color);
        font-weight: bold;
        font-size: 1.2rem;
        margin: 0;
    }
    
    .chart-controls {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    
    .interval-btn {
        padding: 0.25rem 0.75rem;
        border: 1px solid #ddd;
        background: white;
        border-radius: 5px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .interval-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .interval-btn:hover {
        background: #f8f9fa;
    }
    
    .interval-btn.active:hover {
        background: var(--primary-color);
    }
    
    .chart-wrapper {
        position: relative;
        height: 500px;
        width: 100%;
    }
    
    .price-display {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: bold;
        z-index: 10;
    }
    
    .price-change {
        font-size: 0.9rem;
        margin-left: 0.5rem;
    }
    
    .price-up {
        color: #00d4aa;
    }
    
    .price-down {
        color: #ff6b6b;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 20;
    }
    
    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    .market-stats {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .stat-card {
        text-align: center;
        padding: 1rem;
        border-radius: 10px;
        background: #f8f9fa;
    }
    
    .stat-card h6 {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-card .value {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
    }
    
    .refresh-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .refresh-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }
    
    .refresh-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .error-message {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px solid #f5c6cb;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('content')
<!-- Connection Status -->
<div class="connection-status" id="connectionStatus">
    <i class="bi bi-wifi"></i> Connecting...
</div>

<!-- Market Header -->
<div class="market-header">
    <h1><i class="bi bi-graph-up"></i> Live Market Data</h1>
    <p>Real-time BTC-USDT candlestick charts with WebSocket streaming from Binance</p>
</div>

<!-- Market Statistics -->
<div class="row">
    <div class="col-12">
        <div class="market-stats">
            <h6><i class="bi bi-bar-chart"></i> Market Statistics</h6>
            <div class="stats-grid" id="marketStats">
                <div class="stat-card">
                    <h6>Current Price</h6>
                    <div class="value" id="currentPrice">Loading...</div>
                </div>
                <div class="stat-card">
                    <h6>24h Change</h6>
                    <div class="value" id="priceChange">Loading...</div>
                </div>
                <div class="stat-card">
                    <h6>24h High</h6>
                    <div class="value" id="high24h">Loading...</div>
                </div>
                <div class="stat-card">
                    <h6>24h Low</h6>
                    <div class="value" id="low24h">Loading...</div>
                </div>
                <div class="stat-card">
                    <h6>24h Volume</h6>
                    <div class="value" id="volume24h">Loading...</div>
                </div>
                <div class="stat-card">
                    <h6>Last Update</h6>
                    <div class="value" id="lastUpdate">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Candlestick Chart -->
<div class="row">
    <div class="col-12">
        <div class="chart-container">
            <div class="chart-header">
                <h6 class="chart-title">
                    <i class="bi bi-graph-up"></i> BTC/USDT Live Chart
                </h6>
                <div class="chart-controls">
                    <button class="interval-btn active" data-interval="1m">1m</button>
                    <button class="interval-btn" data-interval="5m">5m</button>
                    <button class="interval-btn" data-interval="15m">15m</button>
                    <button class="interval-btn" data-interval="1h">1h</button>
                    <button class="interval-btn" data-interval="4h">4h</button>
                    <button class="interval-btn" data-interval="1d">1d</button>
                </div>
            </div>
            <div class="chart-wrapper">
                <div class="price-display" id="priceDisplay">
                    <span id="currentPriceDisplay">$0.00</span>
                    <span class="price-change" id="priceChangeDisplay">+0.00%</span>
                </div>
                <div class="loading-overlay" id="chartLoading">
                    <div class="loading-spinner"></div>
                </div>
                <canvas id="candlestickChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Refresh Button -->
<button class="refresh-btn" id="refreshBtn" title="Refresh Data">
    <i class="bi bi-arrow-clockwise"></i>
</button>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script>
    // Global variables
    let ws = null;
    let chart = null;
    let currentInterval = '1m';
    let marketData = {
        price: 0,
        change: 0,
        high24h: 0,
        low24h: 0,
        volume24h: 0,
        lastUpdate: null
    };
    let candlestickData = [];
    let isConnected = false;
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeChart();
        setupEventListeners();
        connectWebSocket();
        loadInitialData();
    });
    
    // Initialize Chart.js candlestick chart
    function initializeChart() {
        const ctx = document.getElementById('candlestickChart').getContext('2d');
        
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'BTC/USDT',
                    data: [],
                    borderColor: '#00d4aa',
                    backgroundColor: 'rgba(0, 212, 170, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return `Price: $${context.parsed.y.toFixed(2)}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'minute',
                            displayFormats: {
                                minute: 'HH:mm',
                                hour: 'MMM dd HH:mm',
                                day: 'MMM dd'
                            }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        position: 'right',
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 0
                    }
                }
            }
        });
    }
    
    // Setup event listeners
    function setupEventListeners() {
        // Interval buttons
        document.querySelectorAll('.interval-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.interval-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentInterval = this.dataset.interval;
                loadInitialData();
            });
        });
        
        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            loadInitialData();
        });
    }
    
    // Connect to Binance WebSocket
    function connectWebSocket() {
        updateConnectionStatus('connecting');
        
        // Binance WebSocket URL for BTCUSDT ticker
        const wsUrl = 'wss://stream.binance.com:9443/ws/btcusdt@ticker';
        
        try {
            ws = new WebSocket(wsUrl);
            
            ws.onopen = function() {
                console.log('WebSocket connected to Binance');
                updateConnectionStatus('connected');
                isConnected = true;
            };
            
            ws.onmessage = function(event) {
                const data = JSON.parse(event.data);
                updateMarketData(data);
            };
            
            ws.onclose = function() {
                console.log('WebSocket disconnected');
                updateConnectionStatus('disconnected');
                isConnected = false;
                
                // Attempt to reconnect after 5 seconds
                setTimeout(() => {
                    if (!isConnected) {
                        connectWebSocket();
                    }
                }, 5000);
            };
            
            ws.onerror = function(error) {
                console.error('WebSocket error:', error);
                updateConnectionStatus('disconnected');
                isConnected = false;
            };
            
        } catch (error) {
            console.error('Failed to connect to WebSocket:', error);
            updateConnectionStatus('disconnected');
        }
    }
    
    // Update connection status display
    function updateConnectionStatus(status) {
        const statusEl = document.getElementById('connectionStatus');
        statusEl.className = `connection-status ${status}`;
        
        switch(status) {
            case 'connected':
                statusEl.innerHTML = '<i class="bi bi-wifi"></i> Connected';
                break;
            case 'connecting':
                statusEl.innerHTML = '<i class="bi bi-wifi pulse"></i> Connecting...';
                break;
            case 'disconnected':
                statusEl.innerHTML = '<i class="bi bi-wifi-off"></i> Disconnected';
                break;
        }
    }
    
    // Update market data from WebSocket
    function updateMarketData(data) {
        marketData = {
            price: parseFloat(data.c),
            change: parseFloat(data.P),
            high24h: parseFloat(data.h),
            low24h: parseFloat(data.l),
            volume24h: parseFloat(data.v),
            lastUpdate: new Date()
        };
        
        updateUI();
        updateChart();
    }
    
    // Update UI elements
    function updateUI() {
        document.getElementById('currentPrice').textContent = formatPrice(marketData.price);
        document.getElementById('priceChange').innerHTML = formatChange(marketData.change);
        document.getElementById('high24h').textContent = formatPrice(marketData.high24h);
        document.getElementById('low24h').textContent = formatPrice(marketData.low24h);
        document.getElementById('volume24h').textContent = formatVolume(marketData.volume24h);
        document.getElementById('lastUpdate').textContent = marketData.lastUpdate.toLocaleTimeString();
        
        // Update price display on chart
        document.getElementById('currentPriceDisplay').textContent = formatPrice(marketData.price);
        document.getElementById('priceChangeDisplay').innerHTML = formatChange(marketData.change);
        document.getElementById('priceChangeDisplay').className = `price-change ${marketData.change >= 0 ? 'price-up' : 'price-down'}`;
    }
    
    // Update chart with new data
    function updateChart() {
        if (!chart) return;
        
        const now = new Date();
        const newDataPoint = {
            x: now,
            y: marketData.price
        };
        
        // Add new data point
        chart.data.datasets[0].data.push(newDataPoint);
        
        // Keep only last 100 data points
        if (chart.data.datasets[0].data.length > 100) {
            chart.data.datasets[0].data.shift();
        }
        
        // Update chart
        chart.update('none');
    }
    
    // Load initial historical data
    async function loadInitialData() {
        showChartLoading(true);
        
        try {
            const response = await fetch(`/api/klines/BTCUSDT/${currentInterval}?limit=100`);
            const result = await response.json();
            
            if (result.success && result.data) {
                const klines = result.data;
                const chartData = klines.map(kline => ({
                    x: new Date(kline[0]),
                    y: parseFloat(kline[4]) // Close price
                }));
                
                chart.data.datasets[0].data = chartData;
                chart.update();
            }
        } catch (error) {
            console.error('Error loading initial data:', error);
            showError('Failed to load historical data');
        } finally {
            showChartLoading(false);
        }
    }
    
    // Show/hide chart loading overlay
    function showChartLoading(show) {
        const loadingEl = document.getElementById('chartLoading');
        loadingEl.style.display = show ? 'flex' : 'none';
    }
    
    // Format price for display
    function formatPrice(price) {
        return parseFloat(price).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    // Format change percentage
    function formatChange(change) {
        const sign = change >= 0 ? '+' : '';
        const colorClass = change >= 0 ? 'price-up' : 'price-down';
        return `<span class="${colorClass}">${sign}${change.toFixed(2)}%</span>`;
    }
    
    // Format volume
    function formatVolume(volume) {
        if (volume >= 1e9) {
            return (volume / 1e9).toFixed(1) + 'B';
        } else if (volume >= 1e6) {
            return (volume / 1e6).toFixed(1) + 'M';
        } else if (volume >= 1e3) {
            return (volume / 1e3).toFixed(1) + 'K';
        }
        return volume.toFixed(0);
    }
    
    // Show error message
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.body.insertBefore(errorDiv, document.body.firstChild);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (ws) {
            ws.close();
        }
    });
</script>
@endpush
