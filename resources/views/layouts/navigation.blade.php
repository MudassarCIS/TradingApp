<!-- resources/views/layouts/navigation.blade.php -->
<nav class="bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 w-64 min-h-screen p-4">
    
    <!-- Navigation Links -->
    <ul class="space-y-2">
        <li>
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="block">
                {{ __('Dashboard') }}
            </x-nav-link>
        </li>

        @role('admin')
        <li>
            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.index')" class="block">
                {{ __('Manage Users') }}
            </x-nav-link>
        </li>
        @endrole

        @role('admin')
        <li>
            <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.index')" class="block">
                {{ __('Manage Roles') }}
            </x-nav-link>
        </li>
        @endrole

        @role('admin')
        <li>
            <x-nav-link :href="route('admin.plans.index')" :active="request()->routeIs('admin.plans.*')" class="block">
                {{ __('Manage Plans') }}
            </x-nav-link>
        </li>
        @endrole

        @role('admin')
        <li class="mb-2">
            <div class="text-gray-700 dark:text-gray-300 text-sm font-semibold mb-2">Settings</div>
            <ul class="ml-4 space-y-1">
                <li>
                    <x-nav-link :href="route('admin.wallet-addresses.index')" :active="request()->routeIs('admin.wallet-addresses.*')" class="block text-sm">
                        {{ __('Deposits Admin Details') }}
                    </x-nav-link>
                </li>
            </ul>
        </li>
        @endrole
    </ul>

    <!-- User Dropdown -->
    @auth
    <div class="mt-8 border-t border-gray-200 dark:border-gray-600 pt-4">
        <div class="flex items-center justify-between">
            <span class="text-gray-700 dark:text-gray-300 text-sm">{{ Auth::user()->name ?? 'User' }}</span>
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <svg class="w-4 h-4 text-gray-500 cursor-pointer" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </x-slot>
                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
    @endauth
</nav>
