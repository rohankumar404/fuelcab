import { create } from "zustand";

export interface CartItem {
  id: string;
  sales_channel: "direct" | "marketplace";
  vendor_id: string | null;
  seller_name: string;
  product_id: string | null;
  vendor_listing_id: string | null;
  product_name_snapshot: string;
  product_sku_snapshot: string | null;
  quantity: number;
  price_snapshot: number;
  unit_of_measure: string;
  line_total: number;
  is_price_stale: boolean;
}

export interface SellerGroup {
  sales_channel: "direct" | "marketplace";
  vendor_id: string | null;
  seller_name: string;
  is_first_party: boolean;
  subtotal: number;
  items: CartItem[];
}

export interface CartData {
  id: string;
  user_id: string | null;
  guest_token: string | null;
  items: CartItem[];
  seller_groups: SellerGroup[];
  item_count: number;
  total: number;
  has_multiple_sellers: boolean;
  is_empty: boolean;
}

interface CartStore {
  cart: CartData | null;
  loading: boolean;
  error: string | null;

  // Guest Token handling for guest sessions
  getGuestToken: () => string;

  // Core Actions
  fetchCart: () => Promise<void>;
  addItem: (payload: { productId?: string; vendorListingId?: string; quantity: number }) => Promise<{ success: boolean; message?: string }>;
  updateQuantity: (itemId: string, quantity: number) => Promise<{ success: boolean; message?: string }>;
  removeItem: (itemId: string) => Promise<{ success: boolean; message?: string }>;
  clearCart: () => Promise<void>;
  mergeGuestCart: () => Promise<void>;
}

const API_BASE = (process.env.NEXT_PUBLIC_API_URL ? `${process.env.NEXT_PUBLIC_API_URL.replace(/\/$/, "")}/api/v1` : null) || "http://localhost:8000/api/v1";

export const useCartStore = create<CartStore>((set, get) => ({
  cart: null,
  loading: false,
  error: null,

  getGuestToken: () => {
    if (typeof window === "undefined") return "guest-ssrsession";
    let token = localStorage.getItem("fuelcab_guest_token");
    if (!token) {
      token = "guest_" + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
      localStorage.setItem("fuelcab_guest_token", token);
    }
    return token;
  },

  fetchCart: async () => {
    set({ loading: true, error: null });
    try {
      const guestToken = get().getGuestToken();
      const authToken = typeof window !== "undefined" ? localStorage.getItem("fuelcab_auth_token") : null;

      const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Guest-Token": guestToken,
      };

      let url = `${API_BASE}/cart/guest`;
      if (authToken) {
        headers["Authorization"] = `Bearer ${authToken}`;
        url = `${API_BASE}/cart`;
      }

      const res = await fetch(url, { headers });
      const json = await res.json();

      if (json.success && json.data) {
        set({ cart: json.data, loading: false });
      } else {
        set({ loading: false });
      }
    } catch (err: any) {
      set({ error: err.message || "Failed to load cart", loading: false });
    }
  },

  addItem: async ({ productId, vendorListingId, quantity }) => {
    set({ loading: true, error: null });
    try {
      const guestToken = get().getGuestToken();
      const authToken = typeof window !== "undefined" ? localStorage.getItem("fuelcab_auth_token") : null;

      const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Guest-Token": guestToken,
      };

      let url = `${API_BASE}/cart/guest/items`;
      if (authToken) {
        headers["Authorization"] = `Bearer ${authToken}`;
        url = `${API_BASE}/cart/items`;
      }

      const body: Record<string, any> = { quantity };
      if (productId) body.product_id = productId;
      if (vendorListingId) body.vendor_listing_id = vendorListingId;

      const res = await fetch(url, {
        method: "POST",
        headers,
        body: JSON.stringify(body),
      });

      const json = await res.json();

      if (json.success && json.data) {
        set({ cart: json.data, loading: false });
        return { success: true, message: json.message || "Item added to cart" };
      } else {
        set({ loading: false, error: json.message });
        return { success: false, message: json.message || "Failed to add item to cart" };
      }
    } catch (err: any) {
      set({ loading: false, error: err.message });
      return { success: false, message: err.message || "Network error adding item" };
    }
  },

  updateQuantity: async (itemId: string, quantity: number) => {
    set({ loading: true, error: null });
    try {
      const guestToken = get().getGuestToken();
      const authToken = typeof window !== "undefined" ? localStorage.getItem("fuelcab_auth_token") : null;

      const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Guest-Token": guestToken,
      };

      let url = `${API_BASE}/cart/guest/items/${itemId}`;
      if (authToken) {
        headers["Authorization"] = `Bearer ${authToken}`;
        url = `${API_BASE}/cart/items/${itemId}`;
      }

      const res = await fetch(url, {
        method: "PATCH",
        headers,
        body: JSON.stringify({ quantity }),
      });

      const json = await res.json();

      if (json.success && json.data) {
        set({ cart: json.data, loading: false });
        return { success: true, message: json.message || "Quantity updated" };
      } else {
        set({ loading: false, error: json.message });
        return { success: false, message: json.message || "Failed to update quantity" };
      }
    } catch (err: any) {
      set({ loading: false, error: err.message });
      return { success: false, message: err.message };
    }
  },

  removeItem: async (itemId: string) => {
    set({ loading: true, error: null });
    try {
      const guestToken = get().getGuestToken();
      const authToken = typeof window !== "undefined" ? localStorage.getItem("fuelcab_auth_token") : null;

      const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Guest-Token": guestToken,
      };

      let url = `${API_BASE}/cart/guest/items/${itemId}`;
      if (authToken) {
        headers["Authorization"] = `Bearer ${authToken}`;
        url = `${API_BASE}/cart/items/${itemId}`;
      }

      const res = await fetch(url, {
        method: "DELETE",
        headers,
      });

      const json = await res.json();

      if (json.success && json.data) {
        set({ cart: json.data, loading: false });
        return { success: true, message: "Item removed" };
      } else {
        set({ loading: false });
        return { success: false, message: json.message };
      }
    } catch (err: any) {
      set({ loading: false, error: err.message });
      return { success: false, message: err.message };
    }
  },

  clearCart: async () => {
    set({ loading: true, error: null });
    try {
      const guestToken = get().getGuestToken();
      const authToken = typeof window !== "undefined" ? localStorage.getItem("fuelcab_auth_token") : null;

      const headers: Record<string, string> = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-Guest-Token": guestToken,
      };

      let url = `${API_BASE}/cart/guest`;
      if (authToken) {
        headers["Authorization"] = `Bearer ${authToken}`;
        url = `${API_BASE}/cart`;
      }

      await fetch(url, {
        method: "DELETE",
        headers,
      });

      set({
        cart: null,
        loading: false,
      });
    } catch (err: any) {
      set({ loading: false, error: err.message });
    }
  },

  mergeGuestCart: async () => {
    const guestToken = localStorage.getItem("fuelcab_guest_token");
    const authToken = localStorage.getItem("fuelcab_auth_token");
    if (!guestToken || !authToken) return;

    try {
      const res = await fetch(`${API_BASE}/cart/merge`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": `Bearer ${authToken}`,
        },
        body: JSON.stringify({ guest_token: guestToken }),
      });

      const json = await res.json();
      if (json.success && json.data) {
        set({ cart: json.data });
      }
    } catch (err) {
      console.error("Cart merge error", err);
    }
  },
}));
