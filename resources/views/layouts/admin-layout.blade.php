@php
    $setting = \App\Models\Setting::get();
    $projectName = $setting->company_name ?? config('app.name', 'Admin Panel');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />

    <title>@yield('title', $projectName)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Styles --}}
    <link rel="stylesheet" href="{{ asset('admin-assets/css/adminlte.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <style>
        /* Ensure DataTables expand to full width */
        .dataTables_wrapper {
            width: 100% !important;
        }
        
        .dataTables_wrapper .dataTables_scroll {
            width: 100% !important;
        }
        
        .dataTables_wrapper table.dataTable {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Ensure table containers expand in fullscreen */
        :fullscreen .dataTables_wrapper,
        :-webkit-full-screen .dataTables_wrapper,
        :-moz-full-screen .dataTables_wrapper,
        :-ms-fullscreen .dataTables_wrapper {
            width: 100% !important;
        }
        
        :fullscreen .dataTables_wrapper table.dataTable,
        :-webkit-full-screen .dataTables_wrapper table.dataTable,
        :-moz-full-screen .dataTables_wrapper table.dataTable,
        :-ms-fullscreen .dataTables_wrapper table.dataTable {
            width: 100% !important;
            table-layout: auto !important;
        }
        
        /* Ensure main content area expands in fullscreen */
        :fullscreen .app-main,
        :-webkit-full-screen .app-main,
        :-moz-full-screen .app-main,
        :-ms-fullscreen .app-main {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        :fullscreen .app-main .container-fluid,
        :-webkit-full-screen .app-main .container-fluid,
        :-moz-full-screen .app-main .container-fluid,
        :-ms-fullscreen .app-main .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 15px;
            padding-right: 15px;
        }
    </style>

    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
<div class="app-wrapper">

    {{-- Header --}}
    @include('layouts.admin-includes.header')

    {{-- Sidebar --}}
    @include('layouts.admin-includes.leftmenu')

    {{-- Main content --}}
    <main class="app-main">
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('layouts.admin-includes.footer')

</div>

{{-- Scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"></script>
<script src="{{ asset('admin-assets/js/adminlte.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js"></script>

<script>
// Function to adjust all DataTables when fullscreen is toggled
function adjustAllDataTables() {
    // Wait a bit for the layout to settle after fullscreen change
    setTimeout(function() {
        // Find all DataTables on the page and adjust their columns
        $('.dataTable').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                var table = $(this).DataTable();
                
                // Force table to recalculate width
                table.columns.adjust();
                
                // If table has responsive extension, recalculate
                if (table.responsive) {
                    table.responsive.recalc();
                }
                
                // Redraw the table to apply changes
                table.draw(false);
            }
        });
        
        // Also use the global API if available
        if ($.fn.dataTable && $.fn.dataTable.tables) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    }, 150);
}

// Listen for fullscreen change events
$(document).ready(function() {
    // Listen for native fullscreen change event
    document.addEventListener('fullscreenchange', function() {
        adjustAllDataTables();
    });
    
    // Listen for webkit fullscreen change (Safari)
    document.addEventListener('webkitfullscreenchange', function() {
        adjustAllDataTables();
    });
    
    // Listen for moz fullscreen change (Firefox)
    document.addEventListener('mozfullscreenchange', function() {
        adjustAllDataTables();
    });
    
    // Listen for ms fullscreen change (IE/Edge)
    document.addEventListener('MSFullscreenChange', function() {
        adjustAllDataTables();
    });
    
    // Listen for AdminLTE custom fullscreen events
    $(document).on('maximized.lte.fullscreen', function() {
        adjustAllDataTables();
    });
    
    $(document).on('minimized.lte.fullscreen', function() {
        adjustAllDataTables();
    });
    
    // Also handle window resize events to ensure tables resize properly
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            adjustAllDataTables();
        }, 250);
    });
});
</script>

@stack('scripts')
</body>
</html>
