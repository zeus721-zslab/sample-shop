<div class="card border-0 shadow-sm">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">카테고리 <span class="text-danger">*</span></label>
        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
          @foreach(['주문/결제','배송','반품/교환','회원/계정'] as $cat)
          <option value="{{ $cat }}" {{ old('category', $faq->category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-md-3">
        <label class="form-label fw-semibold">정렬 순서</label>
        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ old('sort_order', $faq->sort_order ?? 0) }}" min="0">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">질문 <span class="text-danger">*</span></label>
        <input type="text" name="question" class="form-control @error('question') is-invalid @enderror"
               value="{{ old('question', $faq->question ?? '') }}" required>
        @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="col-12">
        <label class="form-label fw-semibold">답변 <span class="text-danger">*</span></label>
        <textarea name="answer" rows="6" class="form-control @error('answer') is-invalid @enderror"
                  required>{{ old('answer', $faq->answer ?? '') }}</textarea>
        @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    </div>
  </div>
  <div class="card-footer bg-transparent d-flex gap-2">
    <button type="submit" class="btn btn-dark">저장</button>
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">취소</a>
  </div>
</div>
