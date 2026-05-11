<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/x-icon" href="{{ asset('images/Pagelogo.png') }}" />

    <!-- FONTAWESOME LINK -->
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.2/css/all.css" />
    <link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.4.2/css/sharp-solid.css" />


    {{-- BOOTSTRAP LINK --}}
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- CUSTOM CSS LINK -->
    <link rel="stylesheet" href="{{ asset('css/style_website.css') }}">



    @stack('css')

    <title>Evite</title>
</head>

<body id="admin">
    <!-- SIDE MENU -->
    <div class="admin sidebar">
        <div class="logo">
            <a href="#">
                {{-- <img src="{{ asset('images/admin_Logo.png') }}" alt="logo"> --}}
                <h1>EVITE</h1>
            </a>

        </div>
        <ul class="menu">
            <li class="{{ Request::is('/') ? 'active' : ' ' }}">
                <a href="{{route('dashboard')}}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="{{ Request::is('admin_invitation*') ? 'active' : ' ' }}">
                <a href="{{route('admin_invitation.index')}}">
                    <i class="fa-solid fa-user"></i>
                    <span>Invitations / Users</span>
                </a>
            </li>
            <li class="{{ Request::is('admin_template*') ? 'active' : ' ' }}">
                <a href="{{route('admin_template.index')}}">
                    <i class="fa-solid fa-grid-2"></i>
                    <span>Templates</span>
                </a>
            </li>
            <li>

                <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">

                                        <i class="fal fa-sign-out-alt"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
            </li>
        </ul>

    </div>
    <!-- SIDE MENU ENDS HERE -->


    <div class="admin main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Admin</span>
                <h2>Dashboard</h2>
            </div>

        </div>
        @yield('content')
    </div>
</body>
    {{-- JQUER LINK --}}
    <script src="{{ asset('js/jquery-3.6.0.js') }}"></script>

    {{-- SWEET ALERT LINK --}}
    <script src="{{ asset('js/sweetAlert/sweetalert.min.js') }}"></script>

    <script>
        var invitation_deleteRoute = "{{ route('invitation.destroy', ['invitation' => 'delete_id']) }}";
        var template_delete = "{{ route('admin_template.destroy', ['admin_template' => 'delete_id']) }}";
    </script>

    <script src="{{ asset('js/custom.js') }}"></script>

@stack('script')

{{-- CUSTOM JAVASCRIPT --}}
@if (session('status'))
    <script>
        swal("{{ session('job') }}", "{{ session('status') }}", "success");
    </script>
@endif

</html>
