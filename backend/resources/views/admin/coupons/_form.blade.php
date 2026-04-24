{{-- 쿠폰 폼 공통 파셜 --}}
<div class="row g-3">
  <div class="col-md-6">
    <label class="form-label fw-semibold">쿠폰 코드 <span class="text-danger">*</span></label>
    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
           value="{{ old('code', $coupon->code ?? '') }}" placeholder="WELCOME10" style="text-transform:uppercase">
    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text">영문+숫자 대문자 조합 권장</div>
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">쿠폰 이름 <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $coupon->name ?? '') }}" placeholder="신규가입 10% 할인">
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">할인 유형 <span class="text-danger">*</span></label>
    <select name="type" class="form-select @error('type') is-invalid @enderror" id="couponType">
      <option value="percent" {{ old('type', $coupon->type ?? '') === 'percent' ? 'selected' : '' }}>정률 (%)</option>
      <option value="fixed"   {{ old('type', $coupon->type ?? '') === 'fixed'   ? 'selected' : '' }}>정액 (원)</option>
    </select>
    @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">할인 값 <span class="text-danger">*</span></label>
    <div class="input-group">
      <input type="number" name="value" class="form-control @error('value') is-invalid @enderror"
             value="{{ old('value', $coupon->value ?? '') }}" min="1" placeholder="10">
      <span class="input-group-text" id="valueUnit">%</span>
    </div>
    @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">최소 주문금액 (원)</label>
    <input type="number" name="min_order_amount" class="form-control"
           value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}" min="0" placeholder="0">
    <div class="form-text">0이면 제한 없음</div>
  </div>

  <div class="col-md-4" id="maxDiscountWrap">
    <label class="form-label fw-semibold">최대 할인금액 (원, 정률 전용)</label>
    <input type="number" name="max_discount_amount" class="form-control"
           value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}" min="0" placeholder="비워두면 제한 없음">
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">최대 사용횟수</label>
    <input type="number" name="max_uses" class="form-control"
           value="{{ old('max_uses', $coupon->max_uses ?? '') }}" min="1" placeholder="비워두면 무제한">
  </div>

  <div class="col-md-4">
    <label class="form-label fw-semibold">상태</label>
    <div class="form-check form-switch mt-2">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive"
             {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="isActive">활성화</label>
    </div>
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">시작일</label>
    <input type="datetime-local" name="starts_at" class="form-control"
           value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}">
  </div>

  <div class="col-md-6">
    <label class="form-label fw-semibold">만료일</label>
    <input type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror"
           value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}">
    @error('expires_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
</div>

@push('scripts')
<script>
const typeSelect  = document.getElementById('couponType');
const valueUnit   = document.getElementById('valueUnit');
const maxDiscount = document.getElementById('maxDiscountWrap');

function updateUI() {
  const isPercent = typeSelect.value === 'percent';
  valueUnit.textContent = isPercent ? '%' : '원';
  maxDiscount.style.display = isPercent ? '' : 'none';
}

typeSelect.addEventListener('change', updateUI);
updateUI();
</script>
@endpush
