<!doctype html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'zslab 관리자') | zslab shop</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css">
  @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

  {{-- ── Navbar ─────────────────────────────────────────────────────── --}}
  <nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
            <i class="bi bi-list fs-5"></i>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('/') }}" target="_blank">
            <i class="bi bi-shop me-1"></i>사이트
          </a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="dropdown-item text-danger" type="submit">
                  <i class="bi bi-box-arrow-right me-2"></i>로그아웃
                </button>
              </form>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </nav>

  {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
  <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
      <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <span class="brand-text fw-bold fs-5">zslab <small class="opacity-75">admin</small></span>
      </a>
    </div>
    <div class="sidebar-wrapper">
      <nav class="mt-2">
        <ul class="nav sidebar-menu flex-column" role="menu">

          {{-- ── 대시보드 ─────────────────────────────── --}}
          <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
              <i class="nav-icon bi bi-speedometer2"></i>
              <p>대시보드</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.stats') }}"
               class="nav-link {{ request()->routeIs('admin.stats') ? 'active' : '' }}">
              <i class="nav-icon bi bi-bar-chart-line"></i>
              <p>통계</p>
            </a>
          </li>

          {{-- ── 커머스 관리 ──────────────────────────── --}}
          <li class="nav-header small text-uppercase text-muted px-3 py-2 mt-1" style="font-size:.65rem;letter-spacing:.08em;">커머스 관리</li>
          <li class="nav-item">
            <a href="{{ route('admin.products.index') }}"
               class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-box-seam"></i>
              <p>상품 관리</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.orders.index') }}"
               class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-receipt"></i>
              <p>주문 관리</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.coupons.index') }}"
               class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-ticket-perforated"></i>
              <p>쿠폰 관리</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.categories.index') }}"
               class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-tags"></i>
              <p>카테고리 관리</p>
            </a>
          </li>

          {{-- ── 커머스 관리 (멤버십) ─────────────────── --}}
          <li class="nav-item">
            <a href="{{ route('admin.membership.index') }}"
               class="nav-link {{ request()->routeIs('admin.membership.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-award"></i>
              <p>멤버십 설정</p>
            </a>
          </li>

          {{-- ── 고객 관리 ────────────────────────────── --}}
          <li class="nav-header small text-uppercase text-muted px-3 py-2 mt-1" style="font-size:.65rem;letter-spacing:.08em;">고객 관리</li>
          <li class="nav-item">
            <a href="{{ route('admin.members.index') }}"
               class="nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-people"></i>
              <p>회원 관리</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.inquiries.index') }}"
               class="nav-link {{ request()->routeIs('admin.inquiries.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-chat-dots"></i>
              <p>1:1 문의</p>
            </a>
          </li>

          {{-- ── 콘텐츠 관리 ──────────────────────────── --}}
          <li class="nav-header small text-uppercase text-muted px-3 py-2 mt-1" style="font-size:.65rem;letter-spacing:.08em;">콘텐츠 관리</li>
          <li class="nav-item">
            <a href="{{ route('admin.notices.index') }}"
               class="nav-link {{ request()->routeIs('admin.notices.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-megaphone"></i>
              <p>공지사항 관리</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{ route('admin.faqs.index') }}"
               class="nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
              <i class="nav-icon bi bi-question-circle"></i>
              <p>FAQ 관리</p>
            </a>
          </li>

        </ul>
      </nav>
    </div>
  </aside>

  {{-- ── Main ────────────────────────────────────────────────────────── --}}
  <main class="app-main">
    <div class="app-content-header py-3 px-4 border-bottom">
      <div class="container-fluid px-0">
        <div class="row align-items-center">
          <div class="col">
            <h4 class="mb-0 fw-semibold">@yield('page-title', '관리자 패널')</h4>
          </div>
          @if(Auth::user()->role !== 'demo')
          <div class="col-auto">
            @yield('page-actions')
          </div>
          @endif
        </div>
      </div>
    </div>
    <div class="app-content py-3 px-4">
      <div class="container-fluid px-0">

        @if(Auth::user()->role === 'demo')
          <div class="alert alert-warning d-flex align-items-center mb-3 py-2" role="alert">
            <i class="bi bi-eye me-2 fs-5"></i>
            <div><strong>읽기 전용 데모 계정입니다.</strong> 조회만 가능하며 데이터 변경은 제한됩니다.</div>
          </div>
        @endif

        @if(session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif
        @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif

        @yield('content')
      </div>
    </div>
  </main>

  <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">zslab shop admin</div>
    <strong>© {{ date('Y') }} zslab</strong>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>
