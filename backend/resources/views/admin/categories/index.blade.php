@extends('admin.layouts.app')
@section('title', '카테고리 관리')
@section('page-title', '카테고리 관리')

@section('content')
<div class="row g-4">
  {{-- 카테고리 추가 --}}
  @if(Auth::user()->role !== 'demo')
  <div class="col-12 col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">카테고리 추가</div>
      <div class="card-body">
        <form method="POST" action="{{ route('admin.categories.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label fw-semibold">이름 <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" placeholder="카테고리명" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">상위 카테고리</label>
            <select name="parent_id" class="form-select">
              <option value="">없음 (대분류)</option>
              @foreach($allParents as $parent)
                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                  {{ $parent->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">정렬 순서</label>
            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}">
          </div>
          <div class="mb-4 form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
            <label class="form-check-label" for="is_active">활성화</label>
          </div>
          <button type="submit" class="btn btn-dark w-100">추가</button>
        </form>
      </div>
    </div>
  </div>
  @endif

  {{-- 카테고리 목록 --}}
  <div class="col-12 {{ Auth::user()->role !== 'demo' ? 'col-lg-8' : '' }}">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">카테고리 목록</div>
      <div class="card-body p-0">
        @forelse($parents as $parent)
        <div class="border-bottom px-3 py-3">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
              <span class="fw-semibold">{{ $parent->name }}</span>
              @if(!$parent->is_active)
                <span class="badge bg-secondary">비활성</span>
              @endif
              <small class="text-muted">순서: {{ $parent->sort_order }}</small>
            </div>
            @if(Auth::user()->role !== 'demo')
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-primary"
                      onclick="openEdit({{ $parent->id }}, '{{ addslashes($parent->name) }}', {{ $parent->sort_order }}, {{ $parent->is_active ? 'true' : 'false' }})">
                수정
              </button>
              <form method="POST" action="{{ route('admin.categories.destroy', $parent) }}"
                    onsubmit="return confirm('삭제하시겠습니까?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">삭제</button>
              </form>
            </div>
            @endif
          </div>
          @if($parent->children->count())
          <div class="ms-4 row g-1">
            @foreach($parent->children as $child)
            <div class="col-auto">
              <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                {{ $child->name }}
                @if(!$child->is_active) <i class="bi bi-eye-slash text-muted"></i> @endif
                @if(Auth::user()->role !== 'demo')
                <button class="btn btn-link btn-sm p-0 text-primary ms-1"
                        onclick="openEdit({{ $child->id }}, '{{ addslashes($child->name) }}', {{ $child->sort_order }}, {{ $child->is_active ? 'true' : 'false' }})"
                        title="수정">
                  <i class="bi bi-pencil" style="font-size:.7rem"></i>
                </button>
                <form method="POST" action="{{ route('admin.categories.destroy', $child) }}"
                      class="d-inline" onsubmit="return confirm('삭제하시겠습니까?')">
                  @csrf @method('DELETE')
                  <button class="btn btn-link btn-sm p-0 text-danger"
                          title="삭제"><i class="bi bi-x" style="font-size:.9rem"></i></button>
                </form>
                @endif
              </span>
            </div>
            @endforeach
          </div>
          @endif
        </div>
        @empty
        <div class="text-center text-muted py-5">카테고리가 없습니다.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

{{-- 수정 모달 --}}
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editForm">
        @csrf @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">카테고리 수정</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">이름 <span class="text-danger">*</span></label>
            <input type="text" name="name" id="editName" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">정렬 순서</label>
            <input type="number" name="sort_order" id="editSortOrder" class="form-control">
          </div>
          <div class="form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editIsActive">
            <label class="form-check-label" for="editIsActive">활성화</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
          <button type="submit" class="btn btn-dark">저장</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function openEdit(id, name, sortOrder, isActive) {
  const base = "{{ url('zslab-manage/categories') }}/";
  document.getElementById('editForm').action = base + id;
  document.getElementById('editName').value = name;
  document.getElementById('editSortOrder').value = sortOrder;
  document.getElementById('editIsActive').checked = isActive;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
