<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Uji Petik - Admin')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo_dishub.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboardadmin.css') }}">
    @yield('extra_css')
</head>
<body class="flex bg-[#F5F7FA]">

    @include('admin.template.navbar')

    <main class="main-content lg:ml-64 p-4 md:p-8 flex-1 min-w-0 overflow-x-hidden">
        @yield('content')
    </main>

    <script src="{{ asset('js/navbar.js') }}"></script>
    @yield('extra_js')
</body>
</html>
