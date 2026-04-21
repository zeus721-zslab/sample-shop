export function formatPrice(amount: number): string {
  return amount.toLocaleString('ko-KR') + '원'
}

export function formatDiscount(rate: number): string {
  return Math.round(rate) + '%'
}
