@extends('admin.layouts.app')
@section('title', '통계')
@section('page-title', '통계 대시보드')

@push('styles')
<style>
  .kpi-card { border-left: 3px solid; }
  .kpi-card.revenue { border-color: #198754; }
  .kpi-card.orders  { border-color: #0d6efd; }
  .kpi-card.today   { border-color: #fd7e14; }
  .kpi-card.users   { border-color: #6f42c1; }
  .chart-wrap { position: relative; height: 260px; }
</style>
@endpush

@section('content')

{{-- ── KPI 카드 ────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm kpi-card revenue h-100">
      <div class="card-body">
        <p class="text-muted small mb-1">누적 매출</p>
        <h4 class="fw-bold mb-0">{{ number_format($kpi->total_revenue) }}원</h4>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm kpi-card orders h-100">
      <div class="card-body">
        <p class="text-muted small mb-1">총 주문 수</p>
        <h4 class="fw-bold mb-0">{{ number_format($kpi->total_orders) }}건</h4>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm kpi-card today h-100">
      <div class="card-body">
        <p class="text-muted small mb-1">오늘 매출</p>
        <h4 class="fw-bold mb-0">{{ number_format($kpi->today_revenue) }}원</h4>
        <small class="text-muted">{{ $kpi->today_orders }}건</small>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card border-0 shadow-sm kpi-card users h-100">
      <div class="card-body">
        <p class="text-muted small mb-1">가입 회원</p>
        <h4 class="fw-bold mb-0">{{ number_format($totalUsers->cnt) }}명</h4>
      </div>
    </div>
  </div>
</div>

{{-- ── 일별 매출 (Line) ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold">최근 30일 매출 추이</span>
        <small class="text-muted">취소 제외</small>
      </div>
      <div class="card-body">
        <div class="chart-wrap">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── 주문 상태 + 시간대별 ──────────────────────────────────────── --}}
<div class="row g-3 mb-4">
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">주문 상태별 비율</div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div style="max-width:260px; width:100%;">
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent fw-semibold">시간대별 주문 분포</div>
      <div class="card-body">
        <div class="chart-wrap">
          <canvas id="hourChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── 인기 상품 TOP 10 ─────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">인기 상품 TOP 10 (주문 수량 기준)</div>
      <div class="card-body">
        <div class="chart-wrap" style="height:300px;">
          <canvas id="topProductsChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── 로그 분석 (Kibana 링크) ──────────────────────────────────── --}}
<div class="row g-3">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <div>
          <span class="fw-semibold">로그 분석 (ELK)</span>
          <span class="badge bg-success ms-2 small">Filebeat → Logstash → ES</span>
        </div>
        <a href="/kibana/" target="_blank" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-box-arrow-up-right me-1"></i>Kibana 열기
        </a>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
              <tr><th>수집 소스</th><th>인덱스 패턴</th><th>내용</th><th>상태</th></tr>
            </thead>
            <tbody>
              <tr>
                <td><i class="bi bi-file-text me-1 text-danger"></i>Laravel 로그</td>
                <td><code>zslab-logs-laravel-*</code></td>
                <td>에러, 경고, 주문 이벤트</td>
                <td><span class="badge bg-success">수집 중</span></td>
              </tr>
              <tr>
                <td><i class="bi bi-hdd me-1 text-primary"></i>Logstash 파이프라인</td>
                <td>port 5044</td>
                <td>Beats → Grok 파싱 → ES</td>
                <td><span class="badge bg-success">실행 중</span></td>
              </tr>
              <tr>
                <td><i class="bi bi-graph-up me-1 text-warning"></i>Kibana</td>
                <td>/kibana/</td>
                <td>시각화 대시보드 (내부망)</td>
                <td><span class="badge bg-info">시작 중 (~1분)</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <p class="text-muted small mt-3 mb-0">
          <i class="bi bi-info-circle me-1"></i>
          Kibana는 내부망에서만 접근 가능합니다. 외부 URL(<code>/kibana/</code>)로 접근하려면 관리자 패널을 통해 접속하세요.
          Kibana 초기 시작에 약 1~2분이 소요됩니다.
        </p>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── 데이터 (PHP → JS) ──────────────────────────────────────────────
const dailyData = @json($dailyRevenue);
const statusData = @json($ordersByStatus);
const hourData   = @json($ordersByHour);
const topData    = @json($topProducts);

// ── 색상 팔레트 ────────────────────────────────────────────────────
const palette = ['#0d6efd','#198754','#fd7e14','#dc3545','#6f42c1','#0dcaf0','#ffc107','#6c757d','#20c997','#d63384'];

// ── 1. 일별 매출 라인 차트 ─────────────────────────────────────────
(function() {
  const labels  = dailyData.map(r => r.date);
  const revenue = dailyData.map(r => r.revenue);
  const counts  = dailyData.map(r => r.order_count);

  new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: '매출 (원)',
          data: revenue,
          borderColor: '#198754',
          backgroundColor: 'rgba(25,135,84,0.08)',
          tension: 0.3,
          fill: true,
          yAxisID: 'y',
        },
        {
          label: '주문 수',
          data: counts,
          borderColor: '#0d6efd',
          borderDash: [4, 4],
          tension: 0.3,
          fill: false,
          yAxisID: 'y1',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: { legend: { position: 'top' } },
      scales: {
        y:  { position: 'left',  ticks: { callback: v => (v/10000).toFixed(0) + '만원' } },
        y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } },
      },
    },
  });
})();

// ── 2. 주문 상태 도넛 ──────────────────────────────────────────────
(function() {
  const labelMap = { pending:'대기', paid:'결제완료', shipping:'배송중', delivered:'배송완료', cancelled:'취소' };
  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
      labels: statusData.map(r => labelMap[r.status] ?? r.status),
      datasets: [{ data: statusData.map(r => r.cnt), backgroundColor: palette }],
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
    },
  });
})();

// ── 3. 시간대별 주문 막대 ──────────────────────────────────────────
(function() {
  const hours = Array.from({length:24}, (_, i) => `${i}시`);
  const counts = Array(24).fill(0);
  hourData.forEach(r => { counts[r.hour] = r.cnt; });

  new Chart(document.getElementById('hourChart'), {
    type: 'bar',
    data: {
      labels: hours,
      datasets: [{
        label: '주문 수',
        data: counts,
        backgroundColor: 'rgba(13,110,253,0.7)',
        borderRadius: 3,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { ticks: { stepSize: 1 } } },
    },
  });
})();

// ── 4. 인기 상품 TOP 10 수평 막대 ─────────────────────────────────
(function() {
  new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
      labels: topData.map(r => r.product_name.length > 20 ? r.product_name.slice(0,20)+'…' : r.product_name),
      datasets: [
        {
          label: '판매 수량',
          data: topData.map(r => r.total_qty),
          backgroundColor: 'rgba(25,135,84,0.75)',
          borderRadius: 3,
          yAxisID: 'y',
        },
        {
          label: '매출 (만원)',
          data: topData.map(r => (r.total_revenue / 10000).toFixed(1)),
          backgroundColor: 'rgba(13,110,253,0.55)',
          borderRadius: 3,
          yAxisID: 'y1',
        },
      ],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'top' } },
      scales: {
        y:  { position: 'left' },
        y1: { position: 'right', grid: { drawOnChartArea: false } },
      },
    },
  });
})();
</script>
@endpush
