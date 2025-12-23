@extends('layouts.admin-layout')

@section('title', 'Support Thread - ' . $customer->name)

@push('styles')
<style>
    .support-container {
        max-width: 900px;
        margin: 0 auto;
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }

    .support-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 15px 20px;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .show-old-messages-link {
        color: white;
        text-decoration: underline;
        cursor: pointer;
        font-size: 0.9rem;
    }

    .show-old-messages-link:hover {
        color: rgba(255, 255, 255, 0.8);
    }

    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #e5ddd5;
        background-image: 
            repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,.03) 2px, rgba(0,0,0,.03) 4px);
    }

    .old-messages-container {
        padding: 20px;
        background: #e5ddd5;
        background-image: 
            repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,.03) 2px, rgba(0,0,0,.03) 4px);
        display: none;
        max-height: 400px;
        overflow-y: auto;
        border-top: 2px dashed #999;
        margin-top: 10px;
    }

    .old-messages-container.show {
        display: block;
    }

    .message-wrapper {
        display: flex;
        margin-bottom: 10px;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-wrapper.customer {
        justify-content: flex-start;
    }

    .message-wrapper.admin {
        justify-content: flex-end;
    }

    .message-bubble {
        max-width: 70%;
        padding: 8px 12px;
        border-radius: 18px;
        position: relative;
        word-wrap: break-word;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message-wrapper.customer .message-bubble {
        background: #ffffff;
        border-bottom-left-radius: 4px;
    }

    .message-wrapper.admin .message-bubble {
        background: #dcf8c6;
        border-bottom-right-radius: 4px;
    }

    .message-sender {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: #667781;
    }

    .message-wrapper.customer .message-sender {
        text-align: left;
    }

    .message-wrapper.admin .message-sender {
        text-align: right;
    }

    .message-text {
        font-size: 0.95rem;
        line-height: 1.4;
        color: #111b21;
        margin: 0;
    }

    .message-time {
        font-size: 0.7rem;
        color: #667781;
        margin-top: 4px;
        text-align: left;
    }

    .message-wrapper.admin .message-time {
        text-align: right;
    }

    .message-input-container {
        background: #f0f2f5;
        padding: 15px 20px;
        border-radius: 0 0 15px 15px;
        border-top: 1px solid #e4e6eb;
    }

    .message-input-form {
        display: flex;
        gap: 10px;
    }

    .message-input {
        flex: 1;
        border: none;
        border-radius: 24px;
        padding: 10px 20px;
        background: white;
        resize: none;
        max-height: 100px;
    }

    .message-input:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }

    .send-button {
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .send-button:hover {
        background: var(--secondary-color);
        transform: scale(1.05);
    }

    .send-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #667781;
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .back-button {
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Support Thread</h1>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="back-button">
                <a href="{{ route('admin.support.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Messages
                </a>
            </div>

            <div class="support-container">
                <div class="card" style="border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div class="support-header">
                        <div>
                            <h5 class="mb-0"><i class="bi bi-person"></i> {{ $customer->name }}</h5>
                            <small>{{ $customer->email }}</small>
                        </div>
                        @if($hasOldMessages)
                        <a href="#" class="show-old-messages-link" id="toggleOldMessages">
                            <i class="bi bi-clock-history"></i> Show old messages
                        </a>
                        @endif
                    </div>

                    <div class="messages-container" id="messagesContainer">
                        <div id="oldMessagesContainer" class="old-messages-container"></div>
                        <div id="recentMessagesContainer">
                            @if($recentMessages->count() > 0)
                                @foreach($recentMessages as $message)
                                    <div class="message-wrapper {{ $message->sender_type }}">
                                        <div class="message-bubble">
                                            @if($message->sender_type === 'admin')
                                                <div class="message-sender">You</div>
                                            @else
                                                <div class="message-sender">{{ $customer->name }}</div>
                                            @endif
                                            <p class="message-text">{{ $message->message }}</p>
                                            <div class="message-time">{{ $message->created_at->format('h:i A') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="empty-state">
                                    <i class="bi bi-chat-dots"></i>
                                    <h5>No messages yet</h5>
                                    <p>Start a conversation by sending a message below.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="message-input-container">
                        <form id="messageForm" class="message-input-form">
                            @csrf
                            <input type="hidden" name="thread_id" value="{{ $threadId }}">
                            <textarea 
                                id="messageInput" 
                                class="message-input" 
                                placeholder="Type a message..." 
                                rows="1"
                                required></textarea>
                            <button type="submit" class="send-button" id="sendButton">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script>
    const threadId = '{{ $threadId }}';
    let showOldMessages = false;
    let oldMessagesLoaded = false;

    // Auto-scroll to bottom
    function scrollToBottom() {
        const container = document.getElementById('messagesContainer');
        container.scrollTop = container.scrollHeight;
    }

    // Load messages via AJAX
    function loadMessages(includeOld = false) {
        const url = new URL(`{{ url('admin/support') }}/${threadId}/messages`, window.location.origin);
        if (includeOld) {
            url.searchParams.append('old', '1');
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderMessages(data.messages, includeOld);
                if (!includeOld) {
                    scrollToBottom();
                }
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
    }

    // Render messages
    function renderMessages(messages, isOld = false) {
        const container = isOld ? 
            document.getElementById('oldMessagesContainer') : 
            document.getElementById('recentMessagesContainer');
        
        if (isOld) {
            container.innerHTML = '';
        } else {
            if (messages.length > 0) {
                container.innerHTML = '';
            }
        }

        if (messages.length === 0 && !isOld) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-chat-dots"></i>
                    <h5>No messages yet</h5>
                    <p>Start a conversation by sending a message below.</p>
                </div>
            `;
            return;
        }

        messages.forEach(message => {
            const wrapper = document.createElement('div');
            wrapper.className = `message-wrapper ${message.sender_type}`;
            
            const senderName = message.sender_type === 'admin' ? 
                'You' : 
                (message.customer_name || 'Customer');
            
            wrapper.innerHTML = `
                <div class="message-bubble">
                    <div class="message-sender">${senderName}</div>
                    <p class="message-text">${escapeHtml(message.message)}</p>
                    <div class="message-time">${formatTime(message.created_at_formatted)}</div>
                </div>
            `;
            
            container.appendChild(wrapper);
        });
    }

    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format time
    function formatTime(timeString) {
        const date = new Date(timeString);
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    // Toggle old messages
    document.getElementById('toggleOldMessages')?.addEventListener('click', function(e) {
        e.preventDefault();
        showOldMessages = !showOldMessages;
        const container = document.getElementById('oldMessagesContainer');
        const link = document.getElementById('toggleOldMessages');
        
        if (showOldMessages) {
            if (!oldMessagesLoaded) {
                loadMessages(true);
                oldMessagesLoaded = true;
            }
            container.classList.add('show');
            link.innerHTML = '<i class="bi bi-chevron-up"></i> Hide old messages';
        } else {
            container.classList.remove('show');
            link.innerHTML = '<i class="bi bi-clock-history"></i> Show old messages';
        }
    });

    // Send message
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        
        if (!message) return;
        
        const sendButton = document.getElementById('sendButton');
        sendButton.disabled = true;
        
        fetch('{{ route("admin.support.reply") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                               document.querySelector('input[name="_token"]')?.value,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                thread_id: threadId,
                message: message 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                loadMessages(false);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        })
        .finally(() => {
            sendButton.disabled = false;
        });
    });

    // Auto-resize textarea
    document.getElementById('messageInput').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Auto-refresh messages every 5 seconds
    setInterval(() => {
        loadMessages(false);
    }, 5000);

    // Initial scroll
    setTimeout(scrollToBottom, 100);
</script>
@endpush
@endsection
