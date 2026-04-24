@extends('admin.layouts.app')
@section('title', '회원 관리')
@section('page-title', '회원 관리')

@section('content')
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-center" method="GET">
      <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="이름 / 이메일" value="{{ request('search') }}">
      </div>
      <div class="col-auto">
        <select name="grade" class="form-select form-select-sm">
          <option value="">전체 등급</option>
          @foreach(['newbie'=>'Newbie','silver'=>'Silver','gold'=>'Gold','vip'=>'VIP'] as $v=>$l)
            <option value="{{ $v }}" {{ request('grade')==$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-secondary btn-sm" type="submit">검색</button>
        <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">이름</th>
            <th>이메일</th>
            <th>등급</th>
            <th>적립금</th>
            <th>상태</th>
            <th>가입일</th>
            <th class="text-end pe-3">관리</th>
          </tr>
        </thead>
        <tbody>
          @php
            $gradeLabels = ['newbie'=>'Newbie','silver'=>'Silver','gold'=>'Gold','vip'=>'VIP'];
            $gradeBadges = ['newbie'=>'secondary','silver'=>'info','gold'=>'warning','vip'=>'danger'];
          @endphp
          @forelse($members as $member)
          <tr>
            <td class="ps-3 fw-medium">{{ $member->name }}</td>
            <td class="text-muted small">{{ $member->email }}</td>
            <td>
              <span class="badge bg-{{ $gradeBadges[$member->grade ?? 'newbie'] ?? 'secondary' }}">
                {{ $gradeLabels[$member->grade ?? 'newbie'] ?? ($member->grade ?? 'newbie') }}
              </span>
            </td>
            <td class="fw-medium">{{ number_format($member->points ?? 0) }}P</td>
            <td>
              @if($member->is_active ?? true)
                <span class="badge bg-success">활성</span>
              @else
                <span class="badge bg-danger">정지</span>
              @endif
            </td>
            <td class="text-muted small">{{ $member->created_at->format('Y.m.d') }}</td>
            <td class="text-end pe-3">
              @if(Auth::user()->role !== 'demo')
              <div class="d-flex gap-1 justify-content-end flex-wrap">
                {{-- 등급 변경 --}}
                <form method="POST" action="{{ route('admin.members.grade', $member) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <div class="input-group input-group-sm" style="width:130px">
                    <select name="grade" class="form-select form-select-sm">
                      @foreach($gradeLabels as $v=>$l)
                        <option value="{{ $v }}" {{ ($member->grade ?? 'newbie')==$v?'selected':'' }}>{{ $l }}</option>
                      @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-primary" type="submit" title="등급 저장">
                      <i class="bi bi-check"></i>
                    </button>
                  </div>
                </form>
                {{-- 적립금 조정 --}}
                <button class="btn btn-sm btn-outline-warning"
                        data-bs-toggle="modal" data-bs-target="#pointsModal"
                        data-user-id="{{ $member->id }}"
                        data-user-name="{{ $member->name }}"
                        data-user-points="{{ $member->points ?? 0 }}">
                  <i class="bi bi-coin"></i>
                </button>
                {{-- 활성/정지 토글 --}}
                <form method="POST" action="{{ route('admin.members.toggle', $member) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <button class="btn btn-sm {{ ($member->is_active ?? true) ? 'btn-outline-danger' : 'btn-outline-success' }}"
                          onclick="return confirm('{{ ($member->is_active ?? true) ? '계정을 정지하시겠습니까?' : '계정을 활성화하시겠습니까?' }}')">
                    {{ ($member->is_active ?? true) ? '정지' : '활성화' }}
                  </button>
                </form>
              </div>
              @else
              <span class="text-muted small">조회 전용</span>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-5">회원이 없습니다.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($members->hasPages())
  <div class="card-footer bg-transparent">{{ $members->links() }}</div>
  @endif
</div>

{{-- 적립금 조정 모달 --}}
<div class="modal fade" id="pointsModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title"><i class="bi bi-coin me-1"></i>적립금 수동 조정</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="pointsForm" method="POST">
        @csrf
        <div class="modal-body">
          <p class="text-muted small mb-3" id="pointsUserInfo"></p>
          <div class="mb-3">
            <label class="form-label">구분</label>
            <select name="type" class="form-select" required>
              <option value="earn">지급 (+)</option>
              <option value="use">차감 (-)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">금액 (P)</label>
            <input type="number" name="amount" min="1" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">사유</label>
            <input type="text" name="description" maxlength="200" class="form-control" required placeholder="이벤트 보상, 오류 보상 등">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">취소</button>
          <button type="submit" class="btn btn-sm btn-dark">적용</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.getElementById('pointsModal').addEventListener('show.bs.modal', function (e) {
  const btn   = e.relatedTarget;
  const id    = btn.dataset.userId;
  const name  = btn.dataset.userName;
  const pts   = parseInt(btn.dataset.userPoints).toLocaleString();
  document.getElementById('pointsUserInfo').textContent = name + ' 님 · 현재 ' + pts + 'P';
  document.getElementById('pointsForm').action = '/zslab-manage/members/' + id + '/points';
});
</script>
@endpush
@endsection
