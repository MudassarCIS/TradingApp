@extends('layouts.admin-layout')

@section('title', 'Manage Rent a Bot Plans')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Rent a Bot Packages</h3>
                    <a href="{{ route('admin.rent-bot-packages.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Package
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Allowed Bots</th>
                                    <th>Allowed Trades</th>
                                    <th>Amount ($)</th>
                                    <th>Validity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($packages as $package)
                                    <tr>
                                        <td>{{ $package->id }}</td>
                                        <td>{{ $package->allowed_bots }}</td>
                                        <td>{{ $package->allowed_trades }}</td>
                                        <td>{{ number_format($package->amount, 2) }}</td>
                                        <td class="text-capitalize">{{ $package->validity }}</td>
                                        <td>
                                            @if($package->status)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Disabled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.rent-bot-packages.edit', $package->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                            <form action="{{ route('admin.rent-bot-packages.destroy', $package->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No packages found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $packages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


