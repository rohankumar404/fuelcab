"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Package, Wallet, MapPin, Bell, LogOut, User,
  ShoppingCart, Clock, ChevronRight, Truck, Fuel,
  ReceiptText, Star, Settings,
} from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

interface UserProfile {
  name?: string;
  email?: string;
  phone?: string;
}

interface RecentOrder {
  id: string;
  order_number: string;
  status: string;
  total_amount: number;
  created_at: string;
}

const API_BASE = (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8002").replace(/\/$/, "");

async function fetchWithAuth(path: string, token: string) {
  const res = await fetch(`${API_BASE}${path}`, {
    headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
    cache: "no-store",
  });
  if (!res.ok) throw new Error(`HTTP ${res.status}`);
  return res.json();
}

export default function DashboardPage() {
  const router = useRouter();
  const [user, setUser] = useState<UserProfile | null>(null);
  const [orders, setOrders] = useState<RecentOrder[]>([]);
  const [walletBalance, setWalletBalance] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem("fc_token");
    const storedUser = localStorage.getItem("fc_user");

    if (!token) {
      router.replace("/login");
      return;
    }

    if (storedUser) {
      try { setUser(JSON.parse(storedUser)); } catch {}
    }

    // Load dashboard data from backend
    Promise.allSettled([
      fetchWithAuth("/api/v1/customer/orders?per_page=5", token).then((d) => {
        setOrders(d?.data?.data ?? d?.data ?? []);
      }),
      fetchWithAuth("/api/v1/wallet/balance", token).then((d) => {
        setWalletBalance(d?.data?.balance ?? d?.balance ?? 0);
      }),
      fetchWithAuth("/api/v1/profile", token).then((d) => {
        if (d?.data) {
          setUser(d.data);
          localStorage.setItem("fc_user", JSON.stringify(d.data));
        }
      }),
    ]).finally(() => setLoading(false));
  }, [router]);

  const handleLogout = () => {
    const token = localStorage.getItem("fc_token");
    if (token) {
      fetch(`${API_BASE}/api/v1/auth/logout`, {
        method: "POST",
        headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
      }).catch(() => {});
    }
    localStorage.removeItem("fc_token");
    localStorage.removeItem("fc_user");
    localStorage.removeItem("fc_remember_email");
    router.replace("/login");
  };

  const statusColor: Record<string, string> = {
    pending: "bg-yellow-100 text-yellow-700",
    accepted: "bg-blue-100 text-blue-700",
    assigned: "bg-indigo-100 text-indigo-700",
    out_for_delivery: "bg-purple-100 text-purple-700",
    delivered: "bg-green-100 text-green-700",
    cancelled: "bg-red-100 text-red-700",
  };

  return (
    <div className="min-h-screen bg-[#f4f7f5] flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-5xl mx-auto w-full px-4 py-10">
        {/* Header */}
        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-2xl font-extrabold text-[#1a1a1a]">
              {loading ? "Loading…" : `Hello, ${user?.name ?? "Customer"} 👋`}
            </h1>
            <p className="text-sm text-[#666] mt-0.5">{user?.email ?? user?.phone ?? ""}</p>
          </div>
          <button
            onClick={handleLogout}
            className="flex items-center gap-2 text-sm text-red-500 font-semibold hover:text-red-700 transition"
          >
            <LogOut className="w-4 h-4" />
            Sign out
          </button>
        </div>

        {/* Stats cards */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
          {[
            {
              label: "Wallet Balance",
              value: walletBalance !== null ? `₹${walletBalance.toLocaleString("en-IN")}` : "—",
              icon: Wallet,
              color: "bg-green-50 text-[#155c32]",
            },
            {
              label: "Total Orders",
              value: orders.length > 0 ? `${orders.length}+` : "0",
              icon: Package,
              color: "bg-blue-50 text-blue-600",
            },
            {
              label: "Active Delivery",
              value: orders.find((o) => o.status === "out_for_delivery") ? "1" : "None",
              icon: Truck,
              color: "bg-amber-50 text-amber-600",
            },
            {
              label: "Fuel Types",
              value: "HSD",
              icon: Fuel,
              color: "bg-orange-50 text-orange-600",
            },
          ].map(({ label, value, icon: Icon, color }) => (
            <div key={label} className={`rounded-2xl border border-white bg-white shadow-sm p-4`}>
              <div className={`inline-flex p-2 rounded-xl mb-3 ${color}`}>
                <Icon className="w-5 h-5" />
              </div>
              <p className="text-xs text-[#888] font-medium">{label}</p>
              <p className="text-xl font-extrabold text-[#1a1a1a] mt-0.5">{loading ? "…" : value}</p>
            </div>
          ))}
        </div>

        {/* Quick actions */}
        <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6 mb-6">
          <h2 className="text-base font-bold text-[#1a1a1a] mb-4">Quick Actions</h2>
          <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {[
              { label: "New Order", href: "/order", icon: ShoppingCart, color: "bg-[#155c32] text-white" },
              { label: "Track Order", href: "/order", icon: MapPin, color: "bg-blue-600 text-white" },
              { label: "Invoice", href: "/order", icon: ReceiptText, color: "bg-amber-500 text-white" },
              { label: "Support", href: "/", icon: Star, color: "bg-purple-600 text-white" },
            ].map(({ label, href, icon: Icon, color }) => (
              <Link
                key={label}
                href={href}
                className={`flex flex-col items-center justify-center gap-2 rounded-xl p-4 font-semibold text-sm transition hover:opacity-90 ${color}`}
              >
                <Icon className="w-5 h-5" />
                {label}
              </Link>
            ))}
          </div>
        </div>

        {/* Recent orders */}
        <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6 mb-6">
          <div className="flex items-center justify-between mb-4">
            <h2 className="text-base font-bold text-[#1a1a1a]">Recent Orders</h2>
            <Link href="/order" className="text-sm text-[#155c32] font-semibold hover:underline flex items-center gap-1">
              New Order <ChevronRight className="w-4 h-4" />
            </Link>
          </div>

          {loading ? (
            <div className="space-y-3">
              {[1, 2, 3].map((i) => (
                <div key={i} className="h-14 rounded-xl bg-[#f4f4f4] animate-pulse" />
              ))}
            </div>
          ) : orders.length === 0 ? (
            <div className="text-center py-10 text-[#aaa]">
              <Clock className="w-10 h-10 mx-auto mb-2 opacity-40" />
              <p className="text-sm">No orders yet. Place your first fuel order!</p>
              <Link href="/order" className="mt-4 inline-block px-5 py-2.5 bg-[#155c32] text-white rounded-xl text-sm font-semibold hover:bg-[#0d3a1f] transition">
                Order Now
              </Link>
            </div>
          ) : (
            <div className="divide-y divide-[#f0f4f1]">
              {orders.map((order) => (
                <div key={order.id} className="flex items-center justify-between py-3">
                  <div>
                    <p className="font-semibold text-sm text-[#1a1a1a]">{order.order_number}</p>
                    <p className="text-xs text-[#999]">
                      {new Date(order.created_at).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" })}
                    </p>
                  </div>
                  <div className="flex items-center gap-3">
                    <span className={`text-xs font-semibold px-2.5 py-1 rounded-full capitalize ${statusColor[order.status] ?? "bg-gray-100 text-gray-600"}`}>
                      {order.status.replace(/_/g, " ")}
                    </span>
                    <span className="font-bold text-sm text-[#155c32]">
                      ₹{Number(order.total_amount).toLocaleString("en-IN")}
                    </span>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>

        {/* Account links */}
        <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6">
          <h2 className="text-base font-bold text-[#1a1a1a] mb-4">Account</h2>
          <div className="divide-y divide-[#f0f4f1]">
            {[
              { label: "My Profile", icon: User, href: "/" },
              { label: "Saved Addresses", icon: MapPin, href: "/" },
              { label: "Notifications", icon: Bell, href: "/" },
              { label: "Settings", icon: Settings, href: "/" },
            ].map(({ label, icon: Icon, href }) => (
              <Link
                key={label}
                href={href}
                className="flex items-center justify-between py-3 hover:text-[#155c32] transition group"
              >
                <span className="flex items-center gap-3 text-sm font-medium text-[#333] group-hover:text-[#155c32]">
                  <Icon className="w-4 h-4 text-[#888] group-hover:text-[#155c32]" />
                  {label}
                </span>
                <ChevronRight className="w-4 h-4 text-[#ccc] group-hover:text-[#155c32]" />
              </Link>
            ))}
          </div>
        </div>
      </main>

      <Footer />
    </div>
  );
}
