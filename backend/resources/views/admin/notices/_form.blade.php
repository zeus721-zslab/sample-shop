<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-semibold">제목 <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
               value="{{ old('title', $notice->title ?? '') }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-4">
        <label class="form-label fw-semibold">카테고리 <span class="text-danger">*</span></label>
        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
          @foreach(['general'=>'일반','event'=>'이벤트','policy'=>'정책','delivery'=>'배송','system'=>'시스템'] as $val => $label)
          <option value="{{ $val }}" {{ old('category', $notice->category ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1"
                 {{ old('is_pinned', $notice->is_pinned ?? false) ? 'checked' : '' }}>
          <label class="form-check-label fw-semibold" for="is_pinned">
            <i class="bi bi-pin-fill text-danger me-1"></i>상단 고정
          </label>
        </div>
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">내용 <span class="text-danger">*</span></label>
        <textarea name="content" rows="12" class="form-control @error('content') is-invalid @enderror"
                  required>{{ old('content', $notice->content ?? '') }}</textarea>
        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">HTML 태그 사용 가능합니다.</div>
      </div>
    </div>
  </div>
  <div class="card-footer bg-transparent d-flex gap-2">
    <button type="submit" class="btn btn-dark">저장</button>
    <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary">취소</a>
  </div>
</div>
