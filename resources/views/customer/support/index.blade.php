@extends('layouts.customer-layout')

@section('title', 'Support - AI Trade App')
@section('page-title', 'Support')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-headset"></i> Support Tickets</h5>
            </div>
            <div class="card-body">
                @if($messages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                            <tr>
                                <td>{{ $message->subject }}</td>
                                <td>
                                    <span class="badge bg-{{ $message->status === 'open' ? 'warning' : ($message->status === 'closed' ? 'success' : 'info') }}">
                                        {{ ucfirst($message->status) }}
                                    </span>
                                </td>
                                <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewMessage({{ $message->id }})">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-center">
                    {{ $messages->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="bi bi-headset display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No support tickets</h4>
                    <p class="text-muted">Create a new support ticket if you need help.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Create Support Ticket</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('customer.support.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" value="{{ old('subject') }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send"></i> Submit Ticket
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Support Information</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Email:</strong> support@aitradeapp.com</p>
                <p class="mb-2"><strong>Phone:</strong> +1 (555) 123-4567</p>
                <p class="mb-0"><strong>Hours:</strong> 24/7 Support</p>
            </div>
        </div>
    </div>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Support Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="messageContent">
                <!-- Message content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewMessage(messageId) {
    // This would typically load the message content via AJAX
    document.getElementById('messageContent').innerHTML = '<p>Loading message...</p>';
    var modal = new bootstrap.Modal(document.getElementById('messageModal'));
    modal.show();
}
</script>
@endsection
