"use client";

import { useEffect, useState, useCallback } from "react";
import { useRouter } from "next/navigation";
import Link from "next/link";
import {
  Package, Wallet, MapPin, Bell, LogOut, User, ShoppingCart,
  Clock, ChevronRight, Truck, Fuel, ReceiptText,
  Settings, Plus, Trash2, RefreshCw, CheckCircle2, AlertCircle,
  ChevronLeft, X,
} from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

// ─── Types ────────────────────────────────────────────────────────────────────
interface UserProfile { id?: string; name?: string; email?: string; phone?: string; }
interface Order {
  id: string; order_number: string; status: string;
  total_amount: number; created_at: string; payment_method?: string;
  items?: { quantity: number; product_name_snapshot?: string }[];
}
interface Address {
  id: string; address_line_1: string; address_line_2?: string;
  city: string; state: string; postal_code: string; country: string;
}
interface Notification { id: string; data: { title?: string; body?: string; message?: string }; read_at: string | null; created_at: string; }
interface Ticket { id: string; subject: string; status: string; created_at: string; }

type Panel = "profile" | "addresses" | "notifications" | "support" | "orderDetail" | null;

const API_BASE = (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8002").replace(/\/$/, "");

async function api(path: string, token: string, opts: RequestInit = {}) {
  const res = await fetch(`${API_BASE}${path}`, {
    ...opts,
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      Authorization: `Bearer ${token}`,
      ...(opts.headers ?? {}),
    },
    cache: "no-store",
  });
  return res.json().catch(() => ({}));
}

const STATUS_COLOR: Record<string, string> = {
  pending: "bg-yellow-100 text-yellow-700 border-yellow-200",
  accepted: "bg-blue-100 text-blue-700 border-blue-200",
  assigned: "bg-indigo-100 text-indigo-700 border-indigo-200",
  out_for_delivery: "bg-purple-100 text-purple-700 border-purple-200",
  delivered: "bg-green-100 text-green-700 border-green-200",
  cancelled: "bg-red-100 text-red-700 border-red-200",
};

function Toast({ msg, type }: { msg: string; type: "success" | "error" }) {
  return (
    <div className={`fixed bottom-6 right-6 z-[9999] flex items-center gap-2 px-4 py-3 rounded-xl shadow-xl text-sm font-semibold ${type === "success" ? "bg-[#155c32] text-white" : "bg-red-600 text-white"}`}>
      {type === "success" ? <CheckCircle2 className="w-4 h-4" /> : <AlertCircle className="w-4 h-4" />}
      {msg}
    </div>
  );
}

export default function DashboardPage() {
  const router = useRouter();
  const [token, setToken] = useState("");
  const [user, setUser] = useState<UserProfile | null>(null);
  const [orders, setOrders] = useState<Order[]>([]);
  const [walletBalance, setWalletBalance] = useState<number>(0);
  const [addresses, setAddresses] = useState<Address[]>([]);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [tickets, setTickets] = useState<Ticket[]>([]);
  const [loading, setLoading] = useState(true);
  const [activePanel, setActivePanel] = useState<Panel>(null);
  const [selectedOrder, setSelectedOrder] = useState<Order | null>(null);
  const [toast, setToast] = useState<{ msg: string; type: "success" | "error" } | null>(null);
  const [profileForm, setProfileForm] = useState({ name: "", email: "", phone: "" });
  const [profileSaving, setProfileSaving] = useState(false);
  const [addrForm, setAddrForm] = useState({ address_line_1: "", address_line_2: "", city: "", state: "", postal_code: "", country: "India" });
  const [addrSaving, setAddrSaving] = useState(false);
  const [showAddrForm, setShowAddrForm] = useState(false);
  const [ticketForm, setTicketForm] = useState({ subject: "", message: "" });
  const [ticketSaving, setTicketSaving] = useState(false);

  const showToast = (msg: string, type: "success" | "error" = "success") => {
    setToast({ msg, type });
    setTimeout(() => setToast(null), 3500);
  };

  const loadDashboard = useCallback(async (tk: string) => {
    await Promise.allSettled([
      api("/api/v1/orders?per_page=10", tk).then((d) => {
        const items = Array.isArray(d?.data) ? d.data : (d?.data?.data ?? []);
        setOrders(items);
      }),
      api("/api/v1/wallets", tk).then((d) => setWalletBalance(d?.data?.balance ?? 0)),
      api("/api/v1/customer/profile", tk).then((d) => {
        if (d?.data) {
          setUser(d.data);
          setProfileForm({ name: d.data.name ?? "", email: d.data.email ?? "", phone: d.data.phone ?? "" });
          localStorage.setItem("fc_user", JSON.stringify(d.data));
        }
      }),
      api("/api/v1/customer/addresses", tk).then((d) => setAddresses(d?.data ?? [])),
      api("/api/v1/customer/support/tickets", tk).then((d) => setTickets(d?.data ?? [])),
    ]);
  }, []);

  useEffect(() => {
    const tk = localStorage.getItem("fc_token") ?? "";
    const storedUser = localStorage.getItem("fc_user");
    if (!tk) { router.replace("/login"); return; }
    setToken(tk);
    if (storedUser) {
      try {
        const u = JSON.parse(storedUser);
        setUser(u);
        setProfileForm({ name: u.name ?? "", email: u.email ?? "", phone: u.phone ?? "" });
      } catch {}
    }
    loadDashboard(tk).finally(() => setLoading(false));
  }, [router, loadDashboard]);

  const handleLogout = () => {
    api("/api/v1/auth/logout", token, { method: "POST" }).catch(() => {});
    ["fc_token", "fc_user", "fc_remember_email"].forEach((k) => localStorage.removeItem(k));
    router.replace("/login");
  };

  const saveProfile = async () => {
    setProfileSaving(true);
    const d = await api("/api/v1/customer/profile", token, { method: "PUT", body: JSON.stringify(profileForm) });
    setProfileSaving(false);
    if (d?.data) {
      setUser(d.data);
      localStorage.setItem("fc_user", JSON.stringify(d.data));
      showToast("Profile updated successfully!");
    } else {
      showToast(d?.message ?? "Failed to update profile.", "error");
    }
  };

  const addAddress = async () => {
    if (!addrForm.address_line_1 || !addrForm.city || !addrForm.postal_code) {
      showToast("Please fill Address, City and PIN Code.", "error");
      return;
    }
    setAddrSaving(true);
    const d = await api("/api/v1/customer/addresses", token, { method: "POST", body: JSON.stringify(addrForm) });
    setAddrSaving(false);
    if (d?.data) {
      setAddresses((p) => [d.data, ...p]);
      setShowAddrForm(false);
      setAddrForm({ address_line_1: "", address_line_2: "", city: "", state: "", postal_code: "", country: "India" });
      showToast("Address saved!");
    } else {
      showToast(d?.message ?? "Failed to add address.", "error");
    }
  };

  const deleteAddress = async (id: string) => {
    const d = await api(`/api/v1/customer/addresses/${id}`, token, { method: "DELETE" });
    if (d?.success !== false) {
      setAddresses((p) => p.filter((a) => a.id !== id));
      showToast("Address removed.");
    } else {
      showToast("Failed to delete address.", "error");
    }
  };

  const submitTicket = async () => {
    if (!ticketForm.subject || !ticketForm.message) {
      showToast("Subject and message are required.", "error");
      return;
    }
    setTicketSaving(true);
    const d = await api("/api/v1/customer/support/tickets", token, { method: "POST", body: JSON.stringify(ticketForm) });
    setTicketSaving(false);
    if (d?.data || d?.success) {
      if (d?.data) setTickets((p) => [d.data, ...p]);
      setTicketForm({ subject: "", message: "" });
      showToast("Support ticket submitted! We will respond within 24 hours.");
    } else {
      showToast(d?.message ?? "Failed to submit ticket.", "error");
    }
  };

  const closePanel = () => { setActivePanel(null); setSelectedOrder(null); setShowAddrForm(false); };

  const activeDelivery = orders.find((o) => o.status === "out_for_delivery");

  const PanelHeader = ({ title }: { title: string }) => (
    <div className="flex items-center gap-3 mb-6">
      <button onClick={closePanel} className="p-2 rounded-xl hover:bg-[#f4f8f5] text-[#555] transition">
        <ChevronLeft className="w-5 h-5" />
      </button>
      <h2 className="text-lg font-extrabold text-[#1a1a1a]">{title}</h2>
    </div>
  );

  const renderPanel = () => {
    if (activePanel === "profile") return (
      <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6">
        <PanelHeader title="My Profile" />
        <div className="flex flex-col items-center mb-6">
          <div className="w-20 h-20 rounded-full bg-gradient-to-br from-[#155c32] to-[#33b248] flex items-center justify-center text-3xl font-extrabold text-white shadow-lg">
            {(profileForm.name || user?.name || "U").charAt(0).toUpperCase()}
          </div>
        </div>
        <div className="space-y-4">
          {[
            { key: "name", label: "Full Name", type: "text", placeholder: "Your full name" },
            { key: "email", label: "Email Address", type: "email", placeholder: "you@example.com" },
            { key: "phone", label: "Phone Number", type: "tel", placeholder: "+91 98765 43210" },
          ].map(({ key, label, type, placeholder }) => (
            <div key={key}>
              <label className="block text-xs font-semibold text-[#666] uppercase tracking-wide mb-1.5">{label}</label>
              <input
                type={type}
                placeholder={placeholder}
                value={(profileForm as Record<string, string>)[key]}
                onChange={(e) => setProfileForm((p) => ({ ...p, [key]: e.target.value }))}
                className="w-full h-11 px-4 rounded-xl border border-[#e2e8e4] bg-[#f9fbfa] text-sm focus:outline-none focus:ring-2 focus:ring-[#155c32]/30 focus:border-[#155c32] transition"
              />
            </div>
          ))}
          <button
            onClick={saveProfile}
            disabled={profileSaving}
            className="w-full h-11 rounded-xl bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] transition disabled:opacity-60 flex items-center justify-center gap-2"
          >
            {profileSaving ? <><RefreshCw className="w-4 h-4 animate-spin" /> Saving…</> : "Save Changes"}
          </button>
        </div>
      </div>
    );

    if (activePanel === "addresses") return (
      <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6">
        <PanelHeader title="Saved Addresses" />
        <div className="space-y-3 mb-4">
          {addresses.length === 0 && !showAddrForm && (
            <div className="text-center py-8 text-[#bbb]">
              <MapPin className="w-10 h-10 mx-auto mb-2 opacity-40" />
              <p className="text-sm">No saved addresses yet.</p>
            </div>
          )}
          {addresses.map((addr) => (
            <div key={addr.id} className="flex items-start justify-between p-4 rounded-xl border border-[#e7ece8] bg-[#f9fbfa] gap-3">
              <div>
                <p className="font-semibold text-sm text-[#1a1a1a]">{addr.address_line_1}</p>
                {addr.address_line_2 && <p className="text-xs text-[#777]">{addr.address_line_2}</p>}
                <p className="text-xs text-[#777]">{addr.city}, {addr.state} - {addr.postal_code}</p>
              </div>
              <button onClick={() => deleteAddress(addr.id)} className="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition flex-shrink-0">
                <Trash2 className="w-4 h-4" />
              </button>
            </div>
          ))}
        </div>
        {showAddrForm ? (
          <div className="border border-[#e7ece8] rounded-xl p-4 space-y-3 bg-[#f9fbfa]">
            <p className="text-sm font-bold text-[#1a1a1a]">Add New Address</p>
            {[
              { key: "address_line_1", label: "Address Line 1 *", placeholder: "Street, Building no." },
              { key: "address_line_2", label: "Address Line 2", placeholder: "Landmark, Area (optional)" },
              { key: "city", label: "City *", placeholder: "Mumbai" },
              { key: "state", label: "State *", placeholder: "Maharashtra" },
              { key: "postal_code", label: "PIN Code *", placeholder: "400001" },
            ].map(({ key, label, placeholder }) => (
              <div key={key}>
                <label className="block text-[11px] font-semibold text-[#666] uppercase tracking-wide mb-1">{label}</label>
                <input
                  placeholder={placeholder}
                  value={(addrForm as Record<string, string>)[key]}
                  onChange={(e) => setAddrForm((p) => ({ ...p, [key]: e.target.value }))}
                  className="w-full h-10 px-3 rounded-lg border border-[#e2e8e4] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#155c32]/30 focus:border-[#155c32] transition"
                />
              </div>
            ))}
            <div className="flex gap-2 pt-1">
              <button onClick={addAddress} disabled={addrSaving}
                className="flex-1 h-10 rounded-lg bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] transition disabled:opacity-60 flex items-center justify-center gap-1">
                {addrSaving ? <RefreshCw className="w-3.5 h-3.5 animate-spin" /> : <Plus className="w-3.5 h-3.5" />} Save Address
              </button>
              <button onClick={() => setShowAddrForm(false)} className="flex-1 h-10 rounded-lg border border-[#e7ece8] text-[#555] font-semibold text-sm hover:bg-[#f4f4f4] transition">Cancel</button>
            </div>
          </div>
        ) : (
          <button onClick={() => setShowAddrForm(true)}
            className="w-full h-11 rounded-xl border-2 border-dashed border-[#155c32]/30 text-[#155c32] font-semibold text-sm hover:border-[#155c32] hover:bg-[#f0f7f3] transition flex items-center justify-center gap-2">
            <Plus className="w-4 h-4" /> Add New Address
          </button>
        )}
      </div>
    );

    if (activePanel === "notifications") return (
      <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6">
        <PanelHeader title="Notifications" />
        {notifications.length === 0 ? (
          <div className="text-center py-12 text-[#bbb]">
            <Bell className="w-10 h-10 mx-auto mb-2 opacity-40" />
            <p className="text-sm">No notifications yet.</p>
            <p className="text-xs mt-1">Order updates and alerts will appear here.</p>
          </div>
        ) : (
          <div className="divide-y divide-[#f0f4f1]">
            {notifications.map((n) => (
              <div key={n.id} className={`py-3 ${n.read_at ? "opacity-60" : ""}`}>
                <p className="font-semibold text-sm text-[#1a1a1a]">{n.data?.title ?? "Notification"}</p>
                <p className="text-xs text-[#777] mt-0.5">{n.data?.body ?? n.data?.message ?? ""}</p>
                <p className="text-[10px] text-[#bbb] mt-1">{new Date(n.created_at).toLocaleString("en-IN")}</p>
              </div>
            ))}
          </div>
        )}
      </div>
    );

    if (activePanel === "support") return (
      <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6">
        <PanelHeader title="Help & Support" />
        <div className="bg-[#f0f7f3] rounded-xl p-4 mb-6">
          <p className="font-bold text-sm text-[#1a1a1a] mb-3">Submit a Support Request</p>
          <div className="space-y-3">
            <div>
              <label className="block text-[11px] font-semibold text-[#666] uppercase tracking-wide mb-1">Subject *</label>
              <input
                placeholder="e.g. Order not received, Billing issue..."
                value={ticketForm.subject}
                onChange={(e) => setTicketForm((p) => ({ ...p, subject: e.target.value }))}
                className="w-full h-10 px-3 rounded-lg border border-[#e2e8e4] bg-white text-sm focus:outline-none focus:ring-2 focus:ring-[#155c32]/30 focus:border-[#155c32] transition"
              />
            </div>
            <div>
              <label className="block text-[11px] font-semibold text-[#666] uppercase tracking-wide mb-1">Message *</label>
              <textarea
                rows={4}
                placeholder="Describe your issue in detail..."
                value={ticketForm.message}
                onChange={(e) => setTicketForm((p) => ({ ...p, message: e.target.value }))}
                className="w-full px-3 py-2.5 rounded-lg border border-[#e2e8e4] bg-white text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[#155c32]/30 focus:border-[#155c32] transition"
              />
            </div>
            <button onClick={submitTicket} disabled={ticketSaving}
              className="w-full h-10 rounded-lg bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] transition disabled:opacity-60 flex items-center justify-center gap-2">
              {ticketSaving ? <><RefreshCw className="w-3.5 h-3.5 animate-spin" /> Submitting...</> : "Submit Ticket"}
            </button>
          </div>
        </div>
        {tickets.length > 0 && (
          <>
            <p className="font-bold text-sm text-[#1a1a1a] mb-3">Your Tickets</p>
            <div className="space-y-2">
              {tickets.map((t) => (
                <div key={t.id} className="flex items-start justify-between p-3 rounded-xl border border-[#e7ece8] bg-[#f9fbfa]">
                  <div>
                    <p className="font-semibold text-sm text-[#1a1a1a]">{t.subject}</p>
                    <p className="text-[11px] text-[#999] mt-0.5">{new Date(t.created_at).toLocaleDateString("en-IN")}</p>
                  </div>
                  <span className={`text-[11px] font-bold px-2 py-0.5 rounded-full capitalize ${t.status === "open" ? "bg-green-100 text-green-700" : "bg-gray-100 text-gray-600"}`}>{t.status}</span>
                </div>
              ))}
            </div>
          </>
        )}
        <div className="mt-6 pt-4 border-t border-[#e7ece8] text-sm text-[#777] space-y-1">
          <p className="font-medium">Email: <span className="font-semibold text-[#333]">support@fuelcab.com</span></p>
          <p className="font-medium">Hours: <span className="font-semibold text-[#333]">Mon-Sat, 9AM-6PM IST</span></p>
        </div>
      </div>
    );

    if (activePanel === "orderDetail" && selectedOrder) {
      const statusOrder = ["pending", "accepted", "assigned", "out_for_delivery", "delivered"];
      const currentIdx = statusOrder.indexOf(selectedOrder.status);
      const trackLabels: Record<string, string> = {
        pending: "Order Placed", accepted: "Order Confirmed", assigned: "Driver Assigned",
        out_for_delivery: "Out for Delivery", delivered: "Delivered",
      };
      return (
        <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-6">
          <PanelHeader title={`Order ${selectedOrder.order_number}`} />
          <span className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border capitalize mb-4 ${STATUS_COLOR[selectedOrder.status] ?? "bg-gray-100 text-gray-600 border-gray-200"}`}>
            {selectedOrder.status.replace(/_/g, " ")}
          </span>
          <div className="grid grid-cols-2 gap-3 mb-5">
            {[
              { label: "Order Number", value: selectedOrder.order_number },
              { label: "Total Amount", value: `₹${Number(selectedOrder.total_amount).toLocaleString("en-IN")}` },
              { label: "Payment", value: (selectedOrder.payment_method ?? "—").replace(/_/g, " ") },
              { label: "Placed On", value: new Date(selectedOrder.created_at).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" }) },
            ].map(({ label, value }) => (
              <div key={label} className="bg-[#f9fbfa] rounded-xl p-3 border border-[#e7ece8]">
                <p className="text-[11px] font-semibold text-[#888] uppercase tracking-wide mb-1">{label}</p>
                <p className="font-bold text-sm text-[#1a1a1a] capitalize">{value}</p>
              </div>
            ))}
          </div>
          {selectedOrder.items && selectedOrder.items.length > 0 && (
            <div className="mb-5">
              <p className="text-xs font-semibold text-[#888] uppercase tracking-wide mb-2">Items</p>
              {selectedOrder.items.map((item, i) => (
                <div key={i} className="flex items-center justify-between p-3 rounded-xl bg-[#f9fbfa] border border-[#e7ece8] mb-2">
                  <div className="flex items-center gap-2">
                    <div className="w-8 h-8 rounded-lg bg-[#155c32]/10 flex items-center justify-center"><Fuel className="w-4 h-4 text-[#155c32]" /></div>
                    <p className="text-sm font-semibold">{item.product_name_snapshot ?? "Fuel"}</p>
                  </div>
                  <p className="text-sm font-bold text-[#155c32]">{item.quantity}L</p>
                </div>
              ))}
            </div>
          )}
          {selectedOrder.status !== "cancelled" && (
            <div>
              <p className="text-xs font-semibold text-[#888] uppercase tracking-wide mb-3">Delivery Timeline</p>
              {statusOrder.map((s, i) => {
                const done = i <= currentIdx;
                return (
                  <div key={s} className="flex items-center gap-3 mb-3">
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 transition-colors ${done ? "bg-[#155c32] text-white shadow-sm" : "bg-[#f0f4f1] text-[#ccc]"}`}>
                      {done ? <CheckCircle2 className="w-4 h-4" /> : <Clock className="w-4 h-4" />}
                    </div>
                    <div>
                      <p className={`text-sm font-semibold ${done ? "text-[#1a1a1a]" : "text-[#ccc]"}`}>{trackLabels[s]}</p>
                    </div>
                    {i === currentIdx && selectedOrder.status !== "delivered" && (
                      <span className="ml-auto text-[10px] font-bold text-[#155c32] bg-green-50 px-2 py-0.5 rounded-full">Current</span>
                    )}
                  </div>
                );
              })}
            </div>
          )}
        </div>
      );
    }
    return null;
  };

  if (loading) return (
    <div className="min-h-screen bg-[#f4f7f5] flex flex-col">
      <Navbar />
      <div className="flex-1 flex items-center justify-center">
        <div className="flex flex-col items-center gap-3">
          <div className="w-10 h-10 rounded-full border-4 border-[#155c32]/20 border-t-[#155c32] animate-spin" />
          <p className="text-sm text-[#777] font-medium">Loading your dashboard...</p>
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-[#f4f7f5] flex flex-col">
      <Navbar />
      {toast && <Toast msg={toast.msg} type={toast.type} />}
      <main className="flex-1 max-w-5xl mx-auto w-full px-4 py-8 sm:py-10">
        {/* Header */}
        <div className="flex items-center justify-between mb-7">
          <div>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-[#1a1a1a]">
              Hello, {user?.name?.split(" ")[0] ?? "Customer"} 👋
            </h1>
            <p className="text-sm text-[#666] mt-0.5">{user?.email ?? user?.phone ?? ""}</p>
          </div>
          <button
            onClick={handleLogout}
            className="flex items-center gap-2 text-sm text-red-500 font-semibold hover:text-red-700 transition px-3 py-2 rounded-xl hover:bg-red-50"
          >
            <LogOut className="w-4 h-4" />
            <span className="hidden sm:inline">Sign out</span>
          </button>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
          {[
            { label: "Wallet Balance", value: `₹${walletBalance.toLocaleString("en-IN")}`, icon: Wallet, iconCls: "text-[#155c32] bg-green-50", border: "border-green-100" },
            { label: "Total Orders", value: `${orders.length}`, icon: Package, iconCls: "text-blue-600 bg-blue-50", border: "border-blue-100" },
            { label: "Active Delivery", value: activeDelivery ? "En Route" : "None", icon: Truck, iconCls: "text-amber-600 bg-amber-50", border: "border-amber-100" },
            { label: "Fuel Types", value: "HSD · CNG", icon: Fuel, iconCls: "text-orange-600 bg-orange-50", border: "border-orange-100" },
          ].map(({ label, value, icon: Icon, iconCls, border }) => (
            <div key={label} className={`bg-white rounded-2xl border ${border} shadow-sm p-4 hover:shadow-md transition`}>
              <div className={`inline-flex p-2 rounded-xl mb-3 ${iconCls}`}><Icon className="w-5 h-5" /></div>
              <p className="text-xs text-[#888] font-medium">{label}</p>
              <p className="text-xl font-extrabold text-[#1a1a1a] mt-0.5 truncate">{value}</p>
            </div>
          ))}
        </div>

        {/* Two-column layout */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left: Orders */}
          <div className="lg:col-span-2 space-y-6">
            {/* Quick Actions */}
            <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-5">
              <h2 className="text-base font-bold text-[#1a1a1a] mb-4">Quick Actions</h2>
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <Link href="/order"
                  className="flex flex-col items-center justify-center gap-2 rounded-xl p-4 font-semibold text-sm text-white transition hover:opacity-90 hover:-translate-y-0.5 shadow-sm bg-gradient-to-br from-[#155c32] to-[#22863a]">
                  <ShoppingCart className="w-5 h-5" />New Order
                </Link>
                <button
                  onClick={() => { if (orders.length > 0) { setSelectedOrder(orders[0]); setActivePanel("orderDetail"); } else showToast("No orders to track yet.", "error"); }}
                  className="flex flex-col items-center justify-center gap-2 rounded-xl p-4 font-semibold text-sm text-white transition hover:opacity-90 hover:-translate-y-0.5 shadow-sm bg-gradient-to-br from-blue-600 to-blue-500">
                  <MapPin className="w-5 h-5" />Track Order
                </button>
                <button
                  onClick={() => { if (orders.length > 0) showToast(`Invoice for ${orders[0].order_number} — download available in order details.`); else showToast("No orders yet.", "error"); }}
                  className="flex flex-col items-center justify-center gap-2 rounded-xl p-4 font-semibold text-sm text-white transition hover:opacity-90 hover:-translate-y-0.5 shadow-sm bg-gradient-to-br from-amber-500 to-orange-500">
                  <ReceiptText className="w-5 h-5" />Invoice
                </button>
                <button
                  onClick={() => setActivePanel("support")}
                  className="flex flex-col items-center justify-center gap-2 rounded-xl p-4 font-semibold text-sm text-white transition hover:opacity-90 hover:-translate-y-0.5 shadow-sm bg-gradient-to-br from-purple-600 to-purple-500">
                  <Bell className="w-5 h-5" />Support
                </button>
              </div>
            </div>

            {/* Recent Orders */}
            <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-5">
              <div className="flex items-center justify-between mb-4">
                <h2 className="text-base font-bold text-[#1a1a1a]">Recent Orders</h2>
                <Link href="/order" className="text-sm text-[#155c32] font-semibold hover:underline flex items-center gap-1">
                  New Order <ChevronRight className="w-4 h-4" />
                </Link>
              </div>
              {orders.length === 0 ? (
                <div className="text-center py-10 text-[#aaa]">
                  <Clock className="w-10 h-10 mx-auto mb-2 opacity-40" />
                  <p className="text-sm">No orders yet. Place your first fuel order!</p>
                  <Link href="/order" className="mt-4 inline-block px-5 py-2.5 bg-[#155c32] text-white rounded-xl text-sm font-semibold hover:bg-[#0d3a1f] transition">
                    Order Now
                  </Link>
                </div>
              ) : (
                <div className="divide-y divide-[#f0f4f1]">
                  {orders.slice(0, 6).map((order) => (
                    <div
                      key={order.id}
                      onClick={() => { setSelectedOrder(order); setActivePanel("orderDetail"); }}
                      className="flex items-center justify-between py-3.5 cursor-pointer hover:bg-[#fafcfa] rounded-xl px-2 -mx-2 transition group"
                    >
                      <div className="flex items-center gap-3">
                        <div className={`w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold ${STATUS_COLOR[order.status]?.split(" ").slice(0, 1).join(" ") ?? "bg-gray-100"}`}>
                          <Package className="w-4 h-4" />
                        </div>
                        <div>
                          <p className="font-semibold text-sm text-[#1a1a1a]">{order.order_number}</p>
                          <p className="text-xs text-[#999]">
                            {new Date(order.created_at).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" })}
                          </p>
                        </div>
                      </div>
                      <div className="flex items-center gap-3">
                        <span className={`text-xs font-semibold px-2.5 py-1 rounded-full capitalize border ${STATUS_COLOR[order.status] ?? "bg-gray-100 text-gray-600 border-gray-200"}`}>
                          {order.status.replace(/_/g, " ")}
                        </span>
                        <span className="font-bold text-sm text-[#155c32]">&#8377;{Number(order.total_amount).toLocaleString("en-IN")}</span>
                        <ChevronRight className="w-4 h-4 text-[#ccc] group-hover:text-[#155c32] transition" />
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Right: Panel or Account */}
          <div className="space-y-6">
            {activePanel ? renderPanel() : (
              <div className="bg-white rounded-2xl border border-[#e7ece8] shadow-sm p-5">
                <h2 className="text-base font-bold text-[#1a1a1a] mb-4">Account</h2>
                <div className="divide-y divide-[#f0f4f1]">
                  {[
                    { label: "My Profile", icon: User, panel: "profile" as Panel, badge: null },
                    { label: "Saved Addresses", icon: MapPin, panel: "addresses" as Panel, badge: addresses.length > 0 ? `${addresses.length}` : null },
                    { label: "Notifications", icon: Bell, panel: "notifications" as Panel, badge: notifications.filter((n) => !n.read_at).length > 0 ? `${notifications.filter((n) => !n.read_at).length}` : null },
                    { label: "Help & Support", icon: Bell, panel: "support" as Panel, badge: tickets.filter((t) => t.status === "open").length > 0 ? `${tickets.filter((t) => t.status === "open").length}` : null },
                    { label: "Settings", icon: Settings, panel: null as Panel, badge: null },
                  ].map(({ label, icon: Icon, panel, badge }) => (
                    <button
                      key={label}
                      onClick={() => { if (panel) setActivePanel(panel); else showToast("Settings coming soon!", "success"); }}
                      className="flex items-center justify-between w-full py-3.5 hover:text-[#155c32] transition group text-left"
                    >
                      <span className="flex items-center gap-3 text-sm font-medium text-[#333] group-hover:text-[#155c32]">
                        <div className="w-8 h-8 rounded-xl bg-[#f4f8f5] group-hover:bg-[#e8f5ed] flex items-center justify-center transition">
                          <Icon className="w-4 h-4 text-[#888] group-hover:text-[#155c32]" />
                        </div>
                        {label}
                      </span>
                      <div className="flex items-center gap-2">
                        {badge && <span className="bg-[#155c32] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{badge}</span>}
                        <ChevronRight className="w-4 h-4 text-[#ccc] group-hover:text-[#155c32] transition" />
                      </div>
                    </button>
                  ))}
                </div>
                {/* Wallet Card */}
                <div className="mt-5 p-4 rounded-xl bg-gradient-to-br from-[#155c32] to-[#22863a] text-white">
                  <div className="flex items-center justify-between mb-3">
                    <p className="text-xs font-semibold opacity-70 uppercase tracking-wide">Wallet Balance</p>
                    <Wallet className="w-4 h-4 opacity-70" />
                  </div>
                  <p className="text-2xl font-extrabold">&#8377;{walletBalance.toLocaleString("en-IN")}</p>
                  <p className="text-xs opacity-60 mt-1">FuelCab Wallet · INR</p>
                  <button
                    onClick={() => showToast("Wallet top-up coming soon!")}
                    className="mt-3 w-full h-8 rounded-lg bg-white/15 hover:bg-white/25 text-white text-xs font-bold transition border border-white/20"
                  >
                    + Top Up Wallet
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
}
