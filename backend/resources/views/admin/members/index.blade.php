@extends('admin.layouts.app')
@section('title', '회원 관리')
@section('page-title', '회원 관리')

@section('content')
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
          @foreach(['customer'=>'일반','silver'=>'실버','gold'=>'골드','vip'=>'VIP'] as $v=>$l)
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
            <th>상태</th>
            <th>가입일</th>
            <th class="text-end pe-3">관리</th>
          </tr>
        </thead>
        <tbody>
          @php
            $gradeLabels = ['customer'=>'일반','silver'=>'실버','gold'=>'골드','vip'=>'VIP'];
            $gradeBadges = ['customer'=>'secondary','silver'=>'info','gold'=>'warning','vip'=>'danger'];
          @endphp
          @forelse($members as $member)
          <tr>
            <td class="ps-3 fw-medium">{{ $member->name }}</td>
            <td class="text-muted">{{ $member->email }}</td>
            <td>
              <span class="badge bg-{{ $gradeBadges[$member->role] ?? 'secondary' }}">
                {{ $gradeLabels[$member->role] ?? $member->role }}
              </span>
            </td>
            <td>
              @if($member->is_active ?? true)
                <span class="badge bg-success">활성</span>
              @else
                <span class="badge bg-danger">정지</span>
              @endif
            </td>
            <td class="text-muted small">{{ $member->created_at->format('Y.m.d') }}</td>
            <td class="text-end pe-3">
              <div class="d-flex gap-1 justify-content-end">
                {{-- 등급 변경 --}}
                <form method="POST" action="{{ route('admin.members.grade', $member) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <div class="input-group input-group-sm" style="width:130px">
                    <select name="grade" class="form-select form-select-sm">
                      @foreach($gradeLabels as $v=>$l)
                        <option value="{{ $v }}" {{ $member->role==$v?'selected':'' }}>{{ $l }}</option>
                      @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-primary" type="submit" title="등급 저장">
                      <i class="bi bi-check"></i>
                    </button>
                  </div>
                </form>
                {{-- 활성/정지 토글 --}}
                <form method="POST" action="{{ route('admin.members.toggle', $member) }}" class="d-inline">
                  @csrf @method('PATCH')
                  <button class="btn btn-sm {{ ($member->is_active ?? true) ? 'btn-outline-danger' : 'btn-outline-success' }}"
                          onclick="return confirm('{{ ($member->is_active ?? true) ? '계정을 정지하시겠습니까?' : '계정을 활성화하시겠습니까?' }}')" >
                    {{ ($member->is_active ?? true) ? '정지' : '활성화' }}
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="6" class="text-center text-muted py-5">회원이 없습니다.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($members->hasPages())
  <div class="card-footer bg-transparent">{{ $members->links() }}</div>
  @endif
</div>
@endsection
