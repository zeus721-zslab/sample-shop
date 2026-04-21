@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $err)<li><small>{{ $err }}</small></li>@endforeach
    </ul>
  </div>
@endif

<div class="row g-3">
  <div class="col-12">
    <label class="form-label fw-semibold">상품명 <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">카테고리 <span class="text-danger">*</span></label>
    <select name="category_id" class="form-select" required>
      <option value="">선택하세요</option>
      @foreach($categories as $cat)
        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected':'' }}>
          {{ $cat->name }}
        </option>
      @endforeach
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label fw-semibold">상태 <span class="text-danger">*</span></label>
    <select name="status" class="form-select" required>
      <option value="active"   {{ old('status', $product->status ?? 'active') == 'active'   ? 'selected':'' }}>판매중</option>
      <option value="inactive" {{ old('status', $product->status ?? '') == 'inactive' ? 'selected':'' }}>비활성</option>
      <option value="soldout"  {{ old('status', $product->status ?? '') == 'soldout'  ? 'selected':'' }}>품절</option>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label fw-semibold">재고 <span class="text-danger">*</span></label>
    <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required>
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">정가 (원) <span class="text-danger">*</span></label>
    <input type="number" name="price" class="form-control" value="{{ old('price', $product->price ?? '') }}" min="0" required>
  </div>
  <div class="col-md-6">
    <label class="form-label fw-semibold">할인가 (원)</label>
    <input type="number" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price ?? '') }}" min="0">
  </div>
  <div class="col-12">
    <label class="form-label fw-semibold">상품 요약</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description ?? '') }}</textarea>
  </div>
  <div class="col-12">
    <label class="form-label fw-semibold">상세 설명</label>
    <textarea name="detail" class="form-control" rows="5">{{ old('detail', $product->detail ?? '') }}</textarea>
  </div>
  <div class="col-12">
    <label class="form-label fw-semibold">대표 이미지</label>
    @if(isset($product) && $product->images && count($product->images))
      <div class="mb-2">
        <img src="{{ $product->images[0] }}" class="rounded border" style="height:80px;object-fit:cover">
        <small class="ms-2 text-muted">현재 이미지. 새 파일 선택 시 교체됩니다.</small>
      </div>
    @endif
    <input type="file" name="image" class="form-control" accept="image/*">
    <small class="text-muted">JPG, PNG, GIF / 최대 5MB</small>
  </div>
</div>
