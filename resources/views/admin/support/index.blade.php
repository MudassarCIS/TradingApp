@extends('layouts.admin-layout')

@section('title', 'Support Messages - Admin')

@push('styles')
<style>
    .customer-cards-container {
        padding: 20px;
    }

    .customer-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .customer-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-color: var(--primary-color);
    }

    .customer-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .customer-name {
        font-size: 1.2rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }

    .unread-badge {
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 5px;
    }

    .customer-email {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 10px;
    }

    .last-message {
        font-size: 0.95rem;
        color: #495057;
        margin-bottom: 8px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .last-message-time {
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .pagination-wrapper {
        margin-top: 30px;
        display: flex;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Support Messages</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="customer-cards-container">
                @if($customers->count() > 0)
                    <div class="row">
                        @foreach($customers as $customer)
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="customer-card" onclick="window.location.href='{{ route('admin.support.show', $customer['thread_id']) }}'">
                                    <div class="customer-card-header">
                                        <h5 class="customer-name">{{ $customer['name'] }}</h5>
                                        @if($customer['unread_count'] > 0)
                                            <span class="unread-badge">{{ $customer['unread_count'] }}</span>
                                        @endif
                                    </div>
                                    <div class="customer-email">
                                        <i class="bi bi-envelope"></i> {{ $customer['email'] }}
                                    </div>
                                    @if($customer['last_message'])
                                        <div class="last-message">
                                            {{ Str::limit($customer['last_message'], 80) }}
                                        </div>
                                        <div class="last-message-time">
                                            <i class="bi bi-clock"></i>
                                            {{ $customer['last_message_time'] ? \Carbon\Carbon::parse($customer['last_message_time'])->diffForHumans() : 'N/A' }}
                                        </div>
                                    @else
                                        <div class="last-message text-muted">
                                            No messages yet
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pagination-wrapper">
                        {{ $customers->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="bi bi-chat-dots"></i>
                        <h4>No customer messages</h4>
                        <p>When customers send support messages, they will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
