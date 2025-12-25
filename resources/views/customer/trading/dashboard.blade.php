@extends('layouts.customer-layout')

@section('title', 'Trades Dashboard - AI Trade App')
@section('page-title', 'Trades Dashboard')

@push('styles')
<style>
    /* Override main-content padding for full-screen iframe */
    .trades-dashboard-page-wrapper .main-content {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .trades-dashboard-page-wrapper .top-nav-section {
        margin-bottom: 0 !important;
        padding-bottom: 10px !important;
        border-bottom: none !important;
    }
    
    .trades-dashboard-page {
        width: 100%;
        height: calc(100vh - 100px);
        min-height: 600px;
        margin: 0;
        padding: 0;
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-wrapper {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        border: none;
        overflow: hidden;
        position: relative;
    }
    
    .dashboard-container {
        width: 100%;
        height: 100%;
        position: relative;
        border: none;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }
    
    .dashboard-iframe {
        width: 100%;
        height: 100%;
        border: none;
        display: block;
        margin: 0;
        padding: 0;
    }
    
    /* Ensure full width on mobile */
    @media (max-width: 767px) {
        .trades-dashboard-page {
            height: calc(100vh - 80px);
        }
    }
</style>
@endpush

@section('content')
<div class="trades-dashboard-page">
    <div class="dashboard-wrapper">
        <div class="dashboard-container">
            <iframe 
                src="http://165.22.59.174:5173/" 
                class="dashboard-iframe"
                title="Trades Dashboard"
                allow="fullscreen"
                allowfullscreen
                frameborder="0"
                scrolling="auto">
            </iframe>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add wrapper class to body for styling
    document.body.classList.add('trades-dashboard-page-wrapper');
</script>
@endpush

