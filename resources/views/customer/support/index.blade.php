@extends('layouts.customer-layout')

@section('title', 'Support - AI Trade App')
@section('page-title', 'Support')

@php
use Illuminate\Support\Facades\Storage;
@endphp

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
        justify-content: flex-end;
    }

    .message-wrapper.admin {
        justify-content: flex-start;
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
        background: #dcf8c6;
        border-bottom-right-radius: 4px;
    }

    .message-wrapper.admin .message-bubble {
        background: #ffffff;
        border-bottom-left-radius: 4px;
    }

    .message-sender {
        font-size: 0.75rem;
        font-weight: 600;
        margin-bottom: 4px;
        color: #667781;
    }

    .message-wrapper.customer .message-sender {
        text-align: right;
    }

    .message-wrapper.admin .message-sender {
        text-align: left;
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
        text-align: right;
    }

    .message-wrapper.admin .message-time {
        text-align: left;
    }

    .message-input-container {
        background: #f0f2f5;
        padding: 15px 20px;
        border-radius: 0 0 15px 15px;
        border-top: 1px solid #e4e6eb;
    }

    .message-input-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message-input-row {
        display: flex;
        gap: 10px;
        align-items: flex-end;
    }

    .message-input {
        flex: 1;
        border: none;
        border-radius: 24px;
        padding: 10px 20px;
        background: white;
        resize: none;
        max-height: 100px;
        min-height: 45px;
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

    .attachment-button {
        background: #25d366;
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
        margin-right: 10px;
    }

    .attachment-button:hover {
        background: #20ba5a;
        transform: scale(1.05);
    }

    .file-input-wrapper {
        position: relative;
        display: inline-block;
    }

    .file-input-wrapper input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .attachment-preview {
        padding: 8px;
        background: #fff;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        margin-top: 0;
    }

    .attachment-preview .remove-attachment {
        cursor: pointer;
        color: #dc3545;
        font-weight: bold;
    }

    .message-attachment {
        margin-top: 8px;
        padding: 8px;
        background: rgba(0,0,0,0.05);
        border-radius: 8px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .message-attachment:hover {
        background: rgba(0,0,0,0.1);
    }

    .message-attachment img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        cursor: pointer;
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

    .loading {
        text-align: center;
        padding: 20px;
        color: #667781;
    }

    .support-alert {
        margin: 0;
        padding: 12px 20px;
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        border-radius: 0;
        display: none;
        align-items: center;
        justify-content: space-between;
        font-size: 0.9rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .support-alert.show {
        display: flex;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .support-alert .alert-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        margin-left: 15px;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .support-alert .alert-close:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="support-container">
    <div class="card" style="border: none; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="support-header">
            <div>
                <h5 class="mb-0"><i class="bi bi-headset"></i> Support Chat</h5>
            </div>
            @if($hasOldMessages)
            <a href="#" class="show-old-messages-link" id="toggleOldMessages">
                <i class="bi bi-clock-history"></i> Show old messages
            </a>
            @endif
        </div>

        <div id="supportAlert" class="support-alert">
            <div>
                <i class="bi bi-check-circle-fill"></i>
                <span id="alertMessage">Thank you for your message, our support agent will reply shortly to your message. Thanks</span>
            </div>
            <button type="button" class="alert-close" id="closeAlert" aria-label="Close">&times;</button>
        </div>

        <div class="messages-container" id="messagesContainer">
            <div id="oldMessagesContainer" class="old-messages-container"></div>
            <div id="recentMessagesContainer">
                @if($recentMessages->count() > 0)
                    @foreach($recentMessages as $message)
                        <div class="message-wrapper {{ $message->sender_type }}">
                            <div class="message-bubble">
                                @if($message->sender_type === 'admin')
                                    <div class="message-sender">{{ $message->admin ? $message->admin->name : 'Admin' }}</div>
                                @else
                                    <div class="message-sender">You</div>
                                @endif
                                @if($message->message)
                                    <p class="message-text">{{ $message->message }}</p>
                                @endif
                                @if($message->attachment)
                                    <div class="message-attachment" onclick="viewAttachment('{{ route('customer.support.attachment', $message->id) }}', '{{ $message->attachment_type }}')">
                                        @if($message->attachment_type === 'image')
                                            <img src="{{ Storage::url($message->attachment) }}" alt="{{ $message->attachment_name }}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                                        @else
                                            <i class="bi bi-file-earmark"></i>
                                            <span>{{ $message->attachment_name }}</span>
                                        @endif
                                    </div>
                                @endif
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
            <form id="messageForm" class="message-input-form" enctype="multipart/form-data">
                @csrf
                <div class="message-input-row">
                    <textarea 
                        id="messageInput" 
                        class="message-input" 
                        placeholder="Type a message..." 
                        rows="1"></textarea>
                    <div class="file-input-wrapper">
                        <button type="button" class="attachment-button" id="attachmentButton" title="Attach file">
                            <i class="bi bi-paperclip"></i>
                        </button>
                        <input type="file" id="attachmentInput" name="attachment" accept="image/*,.pdf,.doc,.docx" style="display: none;">
                    </div>
                    <button type="submit" class="send-button" id="sendButton">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
                <div id="attachmentPreview" style="display: none;" class="attachment-preview">
                    <span id="attachmentName"></span>
                    <span class="remove-attachment" id="removeAttachment">&times;</span>
                </div>
            </form>
        </div>
    </div>
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
        const url = new URL('{{ route("customer.support.messages") }}', window.location.origin);
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
            // Clear only if we're refreshing recent messages
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
                (message.admin_name || 'Support System') : 
                'You';
            
            let attachmentHtml = '';
            if (message.attachment) {
                const attachmentUrl = `{{ url('/customer/support/attachment') }}/${message.id}`;
                if (message.attachment_type === 'image') {
                    attachmentHtml = `<div class="message-attachment" onclick="viewAttachment('${attachmentUrl}', 'image')">
                        <img src="${message.attachment}" alt="${escapeHtml(message.attachment_name)}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    </div>`;
                } else {
                    attachmentHtml = `<div class="message-attachment" onclick="viewAttachment('${attachmentUrl}', '${message.attachment_type}')">
                        <i class="bi bi-file-earmark"></i>
                        <span>${escapeHtml(message.attachment_name)}</span>
                    </div>`;
                }
            }
            
            wrapper.innerHTML = `
                <div class="message-bubble">
                    <div class="message-sender">${senderName}</div>
                    ${message.message ? `<p class="message-text">${escapeHtml(message.message)}</p>` : ''}
                    ${attachmentHtml}
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

    // Show alert on page load if there's a success message (from redirect)
    @if(session('success'))
        showSupportAlert();
    @endif

    // Update unread count badge
    function updateUnreadCount() {
        fetch('{{ route("customer.support.unread-count") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.support-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => console.error('Error updating unread count:', error));
    }

    // Update unread count every 30 seconds
    setInterval(updateUnreadCount, 30000);
    updateUnreadCount();

    // Support alert functionality
    function showSupportAlert() {
        const alert = document.getElementById('supportAlert');
        alert.classList.add('show');
        // Auto-hide after 5 seconds
        setTimeout(() => {
            hideSupportAlert();
        }, 5000);
    }

    function hideSupportAlert() {
        const alert = document.getElementById('supportAlert');
        alert.classList.remove('show');
    }

    // Close alert button
    document.getElementById('closeAlert').addEventListener('click', function() {
        hideSupportAlert();
    });

    // File attachment handling
    document.getElementById('attachmentButton').addEventListener('click', function() {
        document.getElementById('attachmentInput').click();
    });

    document.getElementById('attachmentInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('attachmentName').textContent = file.name;
            document.getElementById('attachmentPreview').style.display = 'flex';
        }
    });

    document.getElementById('removeAttachment').addEventListener('click', function() {
        document.getElementById('attachmentInput').value = '';
        document.getElementById('attachmentPreview').style.display = 'none';
    });

    // View attachment in browser
    function viewAttachment(url, type) {
        if (type === 'image') {
            // Open image in new tab
            window.open(url, '_blank');
        } else {
            // For PDFs and Word docs, try to open in browser
            window.open(url, '_blank');
        }
    }

    // Update form submission to handle file uploads
    const originalSubmit = document.getElementById('messageForm').onsubmit;
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('messageInput');
        const attachmentInput = document.getElementById('attachmentInput');
        const message = messageInput.value.trim();
        const file = attachmentInput.files[0];
        
        if (!message && !file) {
            alert('Please provide a message or attachment');
            return;
        }
        
        const sendButton = document.getElementById('sendButton');
        sendButton.disabled = true;
        
        const formData = new FormData();
        formData.append('message', message);
        if (file) {
            formData.append('attachment', file);
        }
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                       document.querySelector('input[name="_token"]')?.value);
        
        fetch('{{ route("customer.support.store") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                attachmentInput.value = '';
                document.getElementById('attachmentPreview').style.display = 'none';
                loadMessages(false);
                // Show success alert
                showSupportAlert();
            } else {
                alert(data.message || 'Failed to send message. Please try again.');
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
</script>
@endpush
@endsection
