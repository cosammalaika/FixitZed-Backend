 <meta charset="utf-8" />
 <title>Dashboard | FIXIT Zed</title>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
 <meta content="Themesbrand" name="author" />
 <meta name="csrf-token" content="{{ csrf_token() }}" />
 <!-- App favicon -->
 <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

 <!-- plugin css -->
 <link href="{{ asset('assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet"
     type="text/css" />

 <!-- preloader css -->
 <link rel="stylesheet" href="{{ asset('assets/css/preloader.min.css') }}" type="text/css" />

 <!-- choices css -->
 <link href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet"
     type="text/css" />



 <!-- color picker css -->
 <link rel="stylesheet" href="{{ asset('assets/libs/%40simonwep/pickr/themes/classic.min.css') }}" />
 <link rel="stylesheet" href="{{ asset('assets/libs/%40simonwep/pickr/themes/monolith.min.css') }}" />
 <!-- 'monolith' theme -->
 <link rel="stylesheet" href="{{ asset('assets/libs/%40simonwep/pickr/themes/nano.min.css') }}" />

 <!-- datepicker css -->
 <link rel="stylesheet" href="assets/libs/flatpickr/flatpickr.min.css">

 <!-- preloader css -->
 <link rel="stylesheet" href="assets/css/preloader.min.css" type="text/css" />


 <!-- Bootstrap Css -->
 <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
 <!-- Icons Css -->
 <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
 <!-- App Css-->
 <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
 <!-- Custom overrides -->
 <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
 <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
     type="text/css" />
 <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"
     type="text/css" />

 <!-- Responsive datatable examples -->
 <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
     rel="stylesheet" type="text/css" />

 {{-- Livewire styles --}}
 @livewireStyles

 {{-- Livewire scripts --}}
 @livewireScripts
