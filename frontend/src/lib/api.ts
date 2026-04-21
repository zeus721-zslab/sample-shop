import type { CartData, Category, Order, PaginatedResponse, Product, Review, User, WishlistItem } from '@/types'

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? 'https://zslab-shop.duckdns.org/api'

async function request<T>(path: string, init?: RequestInit): Promise<T> {
  // headers를 먼저 구조분해해서 ...rest에 headers가 포함되지 않도록 함
  // (그렇지 않으면 ...init이 merged headers 객체를 덮어씀)
  const { headers: initHeaders, ...restInit } = init ?? {}
  const res = await fetch(`${API_URL}${path}`, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...(initHeaders as Record<string, string> | undefined),
    },
    ...restInit,
  })

  if (!res.ok) {
    const error = await res.json().catch(() => ({ message: res.statusText }))
    throw new ApiError(res.status, error.message ?? '알 수 없는 오류가 발생했습니다.')
  }

  if (res.status === 204) return null as T
  return res.json()
}

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

// ── Auth ──────────────────────────────────────────────────────────────────────

export const authApi = {
  register(data: { name: string; email: string; password: string; password_confirmation: string }) {
    return request<{ user: User; token: string }>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },

  login(data: { email: string; password: string }) {
    return request<{ user: User; token: string }>('/auth/login', {
      method: 'POST',
      body: JSON.stringify(data),
    })
  },

  logout(token: string) {
    return request<null>('/auth/logout', {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  me(token: string) {
    return request<User>('/auth/me', {
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}

// ── Categories ────────────────────────────────────────────────────────────────

export const categoryApi = {
  async list(flat = false): Promise<Category[]> {
    const res = await request<{ data: Category[] }>(`/categories${flat ? '?flat=1' : ''}`)
    return res.data ?? []
  },

  async get(slug: string): Promise<Category & { children: Category[]; parent?: Category }> {
    const res = await request<{ data: Category & { children: Category[]; parent?: Category } }>(`/categories/${slug}`)
    return res.data
  },
}

// ── Products ──────────────────────────────────────────────────────────────────

export type ProductQuery = {
  category?: string
  search?: string
  min_price?: number
  max_price?: number
  sort?: 'latest' | 'price_asc' | 'price_desc' | 'popular' | 'rating'
  page?: number
  per_page?: number
}

export const productApi = {
  list(query: ProductQuery = {}) {
    const params = new URLSearchParams()
    for (const [k, v] of Object.entries(query)) {
      if (v !== undefined && v !== '') params.set(k, String(v))
    }
    const qs = params.toString()
    return request<PaginatedResponse<Product>>(`/products${qs ? `?${qs}` : ''}`)
  },

  async get(slug: string): Promise<Product & { reviews: unknown[] }> {
    const res = await request<{ data: Product & { reviews: unknown[] } }>(`/products/${slug}`)
    return res.data
  },
}

// ── Cart ──────────────────────────────────────────────────────────────────────

export const cartApi = {
  get(token: string) {
    return request<CartData>('/cart', { headers: { Authorization: `Bearer ${token}` } })
  },

  add(token: string, data: { product_id: number; quantity: number; options?: Record<string, string> }) {
    return request<{ cart_item_id: string }>('/cart', {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify(data),
    })
  },

  update(token: string, cartItemId: string, quantity: number) {
    return request<null>(`/cart/${cartItemId}`, {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify({ quantity }),
    })
  },

  remove(token: string, cartItemId: string) {
    return request<null>(`/cart/${cartItemId}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  clear(token: string) {
    return request<null>('/cart', {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}

// ── Orders ────────────────────────────────────────────────────────────────────

export const orderApi = {
  list(token: string) {
    return request<PaginatedResponse<Order>>('/orders', {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  get(token: string, id: number) {
    return request<Order>(`/orders/${id}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  create(
    token: string,
    data: {
      cart_item_ids?: string[]
      shipping_address: Order['shipping_address']
      coupon_code?: string
    },
  ) {
    return request<{ order: Order; payment: Record<string, unknown> }>('/orders', {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify(data),
    })
  },

  cancel(token: string, id: number) {
    return request<Order>(`/orders/${id}/cancel`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}

// ── Wishlist ──────────────────────────────────────────────────────────────────

export const wishlistApi = {
  list(token: string) {
    return request<WishlistItem[]>('/wishlist', {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  toggle(token: string, productId: number) {
    return request<{ wishlisted: boolean }>(`/wishlist/${productId}`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  check(token: string, productId: number) {
    return request<{ wishlisted: boolean }>(`/wishlist/check/${productId}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  remove(token: string, productId: number) {
    return request<null>(`/wishlist/${productId}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}

// ── Reviews ───────────────────────────────────────────────────────────────────

export const reviewApi = {
  list(productId: number, page = 1) {
    return request<PaginatedResponse<Review>>(`/products/${productId}/reviews?page=${page}`)
  },

  create(token: string, productId: number, data: { rating: number; title?: string; content: string }) {
    return request<Review>(`/products/${productId}/reviews`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify(data),
    })
  },

  delete(token: string, id: number) {
    return request<null>(`/reviews/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}

// ── Search ────────────────────────────────────────────────────────────────────

export const searchApi = {
  search(q: string, page = 1, perPage = 20) {
    const params = new URLSearchParams({ q, page: String(page), per_page: String(perPage) })
    return request<PaginatedResponse<Product>>(`/search?${params}`)
  },
}

// ── My ────────────────────────────────────────────────────────────────────────

export const myApi = {
  profile(token: string) {
    return request<User>('/my/profile', {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  updateProfile(token: string, data: Partial<{ name: string; phone: string; current_password: string; password: string; password_confirmation: string }>) {
    return request<User>('/my/profile', {
      method: 'PATCH',
      headers: { Authorization: `Bearer ${token}` },
      body: JSON.stringify(data),
    })
  },

  orders(token: string, page = 1) {
    return request<PaginatedResponse<Order>>(`/my/orders?page=${page}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  reviews(token: string, page = 1) {
    return request<PaginatedResponse<Review>>(`/my/reviews?page=${page}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
  },

  wishlist(token: string, page = 1) {
    return request<PaginatedResponse<WishlistItem>>(`/my/wishlist?page=${page}`, {
      headers: { Authorization: `Bearer ${token}` },
    })
  },
}
