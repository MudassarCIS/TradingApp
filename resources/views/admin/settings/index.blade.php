@extends('layouts.admin-layout')

@section('title', 'Settings - Logo & Project Name')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="bi bi-gear"></i> Settings - Logo & Project Name
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Project Name Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="bi bi-tag"></i> Project Name</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="company_name" class="form-label">Project/Company Name</label>
                                            <input type="text" 
                                                   class="form-control @error('company_name') is-invalid @enderror" 
                                                   id="company_name" 
                                                   name="company_name" 
                                                   value="{{ old('company_name', $setting->company_name ?? config('app.name', 'AI Trading Bot')) }}" 
                                                   required>
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">This name will be displayed throughout the application.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Logo Upload Section -->
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0"><i class="bi bi-image"></i> Logo Upload</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="logo" class="form-label">Upload Logo</label>
                                            <input type="file" 
                                                   class="form-control @error('logo') is-invalid @enderror" 
                                                   id="logo" 
                                                   name="logo" 
                                                   accept="image/*">
                                            @error('logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Supported formats: JPEG, PNG, JPG, GIF, SVG (Max: 2MB)
                                            </small>
                                        </div>

                                        <!-- Current Logo Preview -->
                                        @if($setting && $setting->logo_url)
                                            <div class="mb-3">
                                                <label class="form-label">Current Logo:</label>
                                                <div class="border rounded p-3 bg-light text-center">
                                                    <img src="{{ $setting->logo_url }}" 
                                                         alt="Current Logo" 
                                                         class="img-fluid" 
                                                         style="max-height: 150px; max-width: 100%;">
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <label class="form-label">Current Logo:</label>
                                                <div class="border rounded p-3 bg-light text-center">
                                                    <p class="text-muted mb-0">No logo uploaded yet</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="bi bi-eye"></i> Preview</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div id="logo-preview" class="me-3" style="width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd; border-radius: 5px; background: #f8f9fa;">
                                                @if($setting && $setting->logo_url)
                                                    <img src="{{ $setting->logo_url }}" alt="Logo Preview" style="max-width: 100%; max-height: 100%;">
                                                @else
                                                    <span class="text-muted">Logo</span>
                                                @endif
                                            </div>
                                            <div>
                                                <h4 id="project-name-preview" class="mb-0">
                                                    {{ old('company_name', $setting->company_name ?? config('app.name', 'AI Trading Bot')) }}
                                                </h4>
                                                <small class="text-muted">This is how it will appear in the sidebar</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-save"></i> Save Settings
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-lg">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview logo when file is selected
        $('#logo').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#logo-preview').html('<img src="' + e.target.result + '" alt="Logo Preview" style="max-width: 100%; max-height: 100%;">');
                };
                reader.readAsDataURL(file);
            }
        });

        // Preview project name when typing
        $('#company_name').on('input', function() {
            $('#project-name-preview').text($(this).val() || 'Project Name');
        });
    });
</script>
@endpush

