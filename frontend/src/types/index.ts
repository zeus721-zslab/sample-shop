export interface Category {
  id: number
  parent_id: number | null
  name: string
  slug: string
  sort_order: number
  is_active: boolean
  children?: Category[]
}

export interface Product {
  id: number
  category_id: number
  seller_id: number | null
  name: string
  slug: string
  description: string | null
  price: number
  sale_price: number | null
  effective_price: number
  discount_rate: number
  stock: number
  status: 'active' | 'soldout' | 'inactive'
  images: string[]
  rating_avg: number
  order_count: number
  view_count: number
  category?: Category
}

export interface CartItem {
  cart_item_id: string
  product_id: number
  name: string
  slug: string
  price: number
  sale_price: number | null
  effective_price: number
  image: string | null
  stock: number
  quantity: number
  options: Record<string, string>
  line_total: number
  is_soldout: boolean
}

export interface CartData {
  items: CartItem[]
  count: number
  subtotal: number
}

export interface User {
  id: number
  name: string
  email: string
  phone: string | null
  created_at: string
}

export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  product_image: string | null
  options: Record<string, string>
  quantity: number
  unit_price: number
  total_price: number
}

export interface Order {
  id: number
  order_number: string
  status: string
  total_amount: number
  discount_amount: number
  final_amount: number
  shipping_address: {
    recipient: string
    phone: string
    address: string
    detail?: string
    postal_code: string
  }
  items: OrderItem[]
  paid_at: string | null
  created_at: string
}

export interface Review {
  id: number
  product_id: number
  user_id: number
  order_item_id: number | null
  rating: number
  title: string | null
  content: string
  images: string[]
  is_verified: boolean
  is_best: boolean
  created_at: string
  user?: { id: number; name: string; avatar: string | null }
}

export interface WishlistItem {
  id: number
  user_id: number
  product_id: number
  created_at: string
  product?: Product
}

export interface PaginatedResponse<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
