<!doctype html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>관리자 로그인 | zslab shop</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4.0.0-beta3/dist/css/adminlte.min.css">
  <style>
    body { background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card { width: 100%; max-width: 400px; }
    .brand-text { letter-spacing: 0.1em; font-size: 1.6rem; }
  </style>
</head>
<body>
<div class="login-card mx-auto p-3">
  <div class="card shadow-lg border-0">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <h2 class="brand-text fw-bold">zslab</h2>
        <p class="text-muted mb-0">관리자 로그인</p>
      </div>

      @if($errors->any())
        <div class="alert alert-danger py-2">
          <small>{{ $errors->first() }}</small>
        </div>
      @endif

      <form action="{{ route('admin.login.post') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label fw-semibold">이메일</label>
          <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                 value="{{ old('email') }}" placeholder="admin@zslab.com" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">비밀번호</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" name="remember" class="form-check-input" id="remember">
          <label class="form-check-label text-muted" for="remember">로그인 상태 유지</label>
        </div>
        <button type="submit" class="btn btn-dark w-100 py-2 fw-semibold">
          로그인
        </button>
      </form>

      <hr class="my-4">
      <div class="text-center">
        <p class="text-muted small mb-2">계정 없이 둘러보고 싶으신가요?</p>
        <button type="button" class="btn btn-outline-warning w-100 py-2 fw-semibold"
                onclick="fillDemo()">
          <i class="bi bi-eye me-2"></i>데모로 체험하기
        </button>
        <p class="text-muted mt-2" style="font-size:.75rem">읽기 전용 · 데이터 변경 불가</p>
      </div>
    </div>
  </div>
  <p class="text-center mt-3 text-white-50 small">zslab shop admin panel</p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function fillDemo() {
  document.querySelector('input[name=email]').value = '{{ env("DEMO_EMAIL", "demo@zslab.com") }}';
  document.querySelector('input[name=password]').value = '{{ env("DEMO_PASSWORD", "demo1234!") }}';
  document.querySelector('form').submit();
}
</script>
</body>
</html>
