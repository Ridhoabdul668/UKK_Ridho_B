<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS System')</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white" style="width: 260px; min-height: 100vh;">
            <div class="p-3">
                <h4 class="text-center mb-4">POS SYSTEM</h4>
                <hr>
                <nav class="nav flex-column">
                    <a href="{{ route('pos.index') }}" class="nav-link text-white">
                        <i class="fas fa-cash-register me-2"></i> Kasir
                    </a>
                    
                    @if(auth()->user() && auth()->user()->isAdmin())
                    <a href="{{ route('products.index') }}" class="nav-link text-white">
                        <i class="fas fa-box me-2"></i> Produk
                    </a>
                    <a href="{{ route('customers.index') }}" class="nav-link text-white">
                        <i class="fas fa-users me-2"></i> Member
                    </a>
                    <a href="{{ route('users.index') }}" class="nav-link text-white">
                        <i class="fas fa-user-shield me-2"></i> User
                    </a>
                    <a href="{{ route('delete-requests.index') }}" class="nav-link text-white">
                        <i class="fas fa-ticket-alt me-2"></i> Request Hapus
                    </a>
                    <a href="{{ route('logs.index') }}" class="nav-link text-white">
                        <i class="fas fa-history me-2"></i> Log Activity
                    </a>
                    @endif
                    
                    <a href="{{ route('reports.index') }}" class="nav-link text-white">
                        <i class="fas fa-chart-line me-2"></i> Laporan
                    </a>
                    
                    <hr>
                    
                    <a href="{{ route('profile.edit') }}" class="nav-link text-white">
                        <i class="fas fa-user me-2"></i> Profile
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow-1">
            <nav class="navbar navbar-light bg-white border-bottom px-4">
                <div class="container-fluid">
                    <span class="navbar-text">
                        <strong>{{ auth()->user()->name ?? 'Guest' }}</strong> 
                        <span class="badge {{ auth()->user() && auth()->user()->isAdmin() ? 'bg-danger' : 'bg-info' }}">
                            {{ ucfirst(auth()->user()->role ?? '') }}
                        </span>
                    </span>
                </div>
            </nav>
            
            <div class="p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>