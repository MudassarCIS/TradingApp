@extends('layouts.customer-layout')

@section('title', 'Market - AI Trade App')
@section('page-title', 'Market Overview')

@push('styles')
<style>
    .market-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }
    
    .market-card:hover {
        transform: translateY(-5px);
    }
    
    .price-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .price-card h4 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
    }
    
    .price-card .symbol {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }
    
    .price-card .change {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .price-up {
        color: #28a745;
    }
    
    .price-down {
        color: #dc3545;
    }
    
    .market-stats {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    
    .market-stats h6 {
        color: var(--primary-color);
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #eee;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }
    
    .stat-value {
        font-weight: 600;
        color: #333;
    }
    
    .loading {
        text-align: center;
        padding: 2rem;
        color: #666;
    }
    
    .error {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }
    
    .chart-container h6 {
        color: var(--primary-color);
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .crypto-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
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
    }
    
    .refresh-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 12px rgba(0,0,0,0.3);
    }
    
    .refresh-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
<!-- Market Header -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="market-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Live Market Data</h2>
                    <p>Real-time cryptocurrency prices and market analysis</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-graph-up" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Market Overview -->
<div class="row">
    <div class="col-12">
        <div class="market-stats">
            <h6><i class="bi bi-bar-chart"></i> Market Overview</h6>
            <div id="marketOverview">
                <div class="loading">
                    <i class="bi bi-arrow-clockwise spin"></i> Loading market data...
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cryptocurrency Prices -->
<div class="crypto-grid" id="cryptoGrid">
    <!-- Prices will be loaded here via JavaScript -->
</div>

<!-- Technical Analysis -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="chart-container">
            <h6><i class="bi bi-graph-up"></i> BTC/USDT Chart</h6>
            <div id="btcChart" style="height: 300px;">
                <div class="loading">Loading chart...</div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="chart-container">
            <h6><i class="bi bi-graph-up"></i> ETH/USDT Chart</h6>
            <div id="ethChart" style="height: 300px;">
                <div class="loading">Loading chart...</div>
            </div>
        </div>
    </div>
</div>

<!-- Market Categories -->
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-trophy"></i> Top Gainers</h6>
            </div>
            <div class="card-body">
                <div id="topGainers">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-arrow-down"></i> Top Losers</h6>
            </div>
            <div class="card-body">
                <div id="topLosers">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-volume-up"></i> Most Active</h6>
            </div>
            <div class="card-body">
                <div id="mostActive">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Refresh Button -->
<button class="refresh-btn" id="refreshBtn" onclick="refreshMarketData()">
    <i class="bi bi-arrow-clockwise"></i>
</button>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let marketData = {};
    let charts = {};
    
    // Initialize market data
    document.addEventListener('DOMContentLoaded', function() {
        loadMarketData();
        
        // Auto-refresh every 30 seconds
        setInterval(loadMarketData, 30000);
    });
    
    // Load market data
    async function loadMarketData() {
        try {
            const refreshBtn = document.getElementById('refreshBtn');
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
            
            // Load multiple symbols
            const symbols = ['BTCUSDT', 'ETHUSDT', 'ADAUSDT', 'BNBUSDT', 'XRPUSDT', 'SOLUSDT'];
            const promises = symbols.map(symbol => fetchMarketData(symbol));
            
            await Promise.all(promises);
            
            // Update UI
            updateMarketOverview();
            updateCryptoGrid();
            updateCharts();
            
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
            
        } catch (error) {
            console.error('Error loading market data:', error);
            showError('Failed to load market data. Please try again.');
        }
    }
    
    // Fetch market data for a symbol
    async function fetchMarketData(symbol) {
        try {
            const response = await fetch(`/api/market-data/${symbol}`);
            const data = await response.json();
            
            if (data.success) {
                marketData[symbol] = data.data;
            }
        } catch (error) {
            console.error(`Error fetching data for ${symbol}:`, error);
        }
    }
    
    // Update market overview
    function updateMarketOverview() {
        const overview = document.getElementById('marketOverview');
        const symbols = Object.keys(marketData);
        
        if (symbols.length === 0) {
            overview.innerHTML = '<div class="error">No market data available</div>';
            return;
        }
        
        let totalVolume = 0;
        let totalChange = 0;
        
        symbols.forEach(symbol => {
            const data = marketData[symbol];
            totalVolume += data.volume || 0;
            totalChange += data.price_change_24h || 0;
        });
        
        const avgChange = totalChange / symbols.length;
        
        overview.innerHTML = `
            <div class="stat-item">
                <span class="stat-label">Total Volume (24h)</span>
                <span class="stat-value">$${formatNumber(totalVolume)}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Average Change (24h)</span>
                <span class="stat-value ${avgChange >= 0 ? 'price-up' : 'price-down'}">
                    ${avgChange >= 0 ? '+' : ''}${avgChange.toFixed(2)}%
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Active Symbols</span>
                <span class="stat-value">${symbols.length}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Last Updated</span>
                <span class="stat-value">${new Date().toLocaleTimeString()}</span>
            </div>
        `;
    }
    
    // Update crypto grid
    function updateCryptoGrid() {
        const grid = document.getElementById('cryptoGrid');
        const symbols = Object.keys(marketData);
        
        if (symbols.length === 0) {
            grid.innerHTML = '<div class="col-12"><div class="error">No market data available</div></div>';
            return;
        }
        
        grid.innerHTML = symbols.map(symbol => {
            const data = marketData[symbol];
            const change = data.price_change_24h || 0;
            const changeClass = change >= 0 ? 'price-up' : 'price-down';
            const changeIcon = change >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';
            
            return `
                <div class="price-card">
                    <div class="symbol">${symbol}</div>
                    <h4>$${formatPrice(data.price)}</h4>
                    <div class="change ${changeClass}">
                        <i class="bi ${changeIcon}"></i>
                        ${change >= 0 ? '+' : ''}${change.toFixed(2)}%
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">
                            Vol: $${formatNumber(data.volume)}
                        </small>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Update charts
    function updateCharts() {
        updateChart('btcChart', 'BTCUSDT');
        updateChart('ethChart', 'ETHUSDT');
    }
    
    // Update individual chart
    function updateChart(chartId, symbol) {
        const container = document.getElementById(chartId);
        const data = marketData[symbol];
        
        if (!data) {
            container.innerHTML = '<div class="error">No data available</div>';
            return;
        }
        
        // Simple price display for now
        container.innerHTML = `
            <div class="text-center">
                <h3>$${formatPrice(data.price)}</h3>
                <p class="${data.price_change_24h >= 0 ? 'price-up' : 'price-down'}">
                    ${data.price_change_24h >= 0 ? '+' : ''}${data.price_change_24h.toFixed(2)}%
                </p>
                <div class="row text-center mt-3">
                    <div class="col-6">
                        <small class="text-muted">High 24h</small><br>
                        <strong>$${formatPrice(data.high_24h)}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted">Low 24h</small><br>
                        <strong>$${formatPrice(data.low_24h)}</strong>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Refresh market data
    function refreshMarketData() {
        loadMarketData();
    }
    
    // Utility functions
    function formatPrice(price) {
        return parseFloat(price).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    function formatNumber(num) {
        if (num >= 1e9) {
            return (num / 1e9).toFixed(1) + 'B';
        } else if (num >= 1e6) {
            return (num / 1e6).toFixed(1) + 'M';
        } else if (num >= 1e3) {
            return (num / 1e3).toFixed(1) + 'K';
        }
        return num.toFixed(0);
    }
    
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error';
        errorDiv.textContent = message;
        document.body.insertBefore(errorDiv, document.body.firstChild);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
</script>

<style>
    .spin {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush
