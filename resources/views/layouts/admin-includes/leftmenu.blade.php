@php
    $setting = \App\Models\Setting::get();
    $logoUrl = $setting->logo_url ?? asset('admin-assets/img/AdminLTELogo.png');
    $projectName = $setting->company_name ?? config('app.name', 'Admin Panel');
@endphp
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ url('/') }}" class="brand-link">
            <img src="{{ $logoUrl }}" alt="Logo" class="brand-image opacity-75 shadow">
            <span class="brand-text fw-light">{{ $projectName }}</span>
        </a>
    </div>
    <div class="sidebar-wrapper">
        {{-- Navigation menu copied from HTML --}}
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview">
                <li class="nav-item">
                    <a href="{{route('dashboard')}}" class="nav-link @if(request()->routeIs('dashboard')) active @endif">
                        <i class="nav-icon bi bi-palette"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.deposits.index')}}" class="nav-link @if(request()->routeIs('admin.deposits.*')) active @endif">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>
                            {{ __('All Deposits') }}
                            @if(isset($pendingDepositsCount) && $pendingDepositsCount > 0)
                                <span class="badge bg-warning float-end">{{ $pendingDepositsCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.withdrawals.index')}}" class="nav-link @if(request()->routeIs('admin.withdrawals.*')) active @endif">
                        <i class="nav-icon bi bi-dash-circle"></i>
                        <p>{{ __('All Withdrawals') }}</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{route('admin.invoices.index')}}" class="nav-link @if(request()->routeIs('admin.invoices.*')) active @endif">
                        <i class="nav-icon bi bi-file-earmark-text"></i>
                        <p>{{ __('Manage Invoices') }}</p>
                    </a>
                </li>

                <li class="nav-item menu-open">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>
                            Settings
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{route('admin.users.index')}}" class="nav-link @if(request()->routeIs('admin.users.index')) active @endif">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>{{ __('Manage Users') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.roles.index')}}" class="nav-link @if(request()->routeIs('admin.roles.index')) active @endif">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>{{ __('Manage Roles') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.plans.index')}}" class="nav-link @if(request()->routeIs('admin.plans.*')) active @endif">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>{{ __('Manage NEXA Plans') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.rent-bot-packages.index')}}" class="nav-link @if(request()->routeIs('admin.rent-bot-packages.*')) active @endif">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>{{ __('Manage PEX Plans') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.wallet-addresses.index')}}" class="nav-link @if(request()->routeIs('admin.wallet-addresses.*')) active @endif">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>{{ __('Deposits Admin Details') }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{route('admin.settings.index')}}" class="nav-link @if(request()->routeIs('admin.settings.*')) active @endif">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>{{ __('Logo & Project Name') }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</aside>
