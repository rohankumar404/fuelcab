"use client";

import React, { useEffect, useState } from "react";
import Link from "next/link";
import {
  ShoppingCart,
  Trash2,
  Plus,
  Minus,
  ArrowRight,
  ShieldCheck,
  Building2,
  Store,
  AlertCircle,
  Package,
  RefreshCw,
} from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { useCartStore, SellerGroup } from "@/store/useCartStore";

export default function CartPage() {
  const { cart, loading, error, fetchCart, updateQuantity, removeItem, clearCart } = useCartStore();
  const [actionError, setActionError] = useState<string | null>(null);
  const [busyItemId, setBusyItemId] = useState<string | null>(null);
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
    fetchCart();
  }, [fetchCart]);

  const handleQuantityChange = async (itemId: string, newQty: number) => {
    if (newQty <= 0) return;
    setActionError(null);
    setBusyItemId(itemId);
    const res = await updateQuantity(itemId, newQty);
    setBusyItemId(null);
    if (!res.success && res.message) {
      setActionError(res.message);
    }
  };

  const handleRemove = async (itemId: string) => {
    setActionError(null);
    setBusyItemId(itemId);
    const res = await removeItem(itemId);
    setBusyItemId(null);
    if (!res.success && res.message) {
      setActionError(res.message);
    }
  };

  return (
    <div className="min-h-screen flex flex-col bg-[#fafbfa] text-[#1a1a1a]">
      <Navbar />

      {/* Header Banner — Tightened padding for cleaner layout */}
      <div className="bg-[#155c32] text-white py-8 px-4 sm:px-6 lg:px-8 shadow-sm">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <div className="flex items-center gap-2 text-[#33b248] text-xs font-bold tracking-wider uppercase mb-1">
              <ShoppingCart className="w-4 h-4" />
              <span>Procurement Cart</span>
            </div>
            <h1 className="text-2xl sm:text-3xl font-extrabold tracking-tight">Your Fuel & Energy Cart</h1>
            <p className="text-emerald-100 text-xs sm:text-sm mt-0.5">
              Review line items, seller fulfillment groups, and order summaries.
            </p>
          </div>
          <Link
            href="/marketplace"
            className="inline-flex items-center gap-2 bg-[#33b248] hover:bg-[#2ba03f] text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-md hover:shadow-lg"
          >
            <span>Explore Marketplace</span>
            <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>
      </div>

      {/* Action Error Alert */}
      {actionError && (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
          <div className="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl flex items-start gap-3 text-sm">
            <AlertCircle className="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
            <div className="flex-1 font-medium">{actionError}</div>
            <button
              onClick={() => setActionError(null)}
              className="text-red-500 hover:text-red-700 font-bold text-xs"
            >
              DISMISS
            </button>
          </div>
        </div>
      )}

      {/* Main Cart Content */}
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {!mounted || (loading && !cart) ? (
          <div className="flex flex-col items-center justify-center py-16 bg-white border border-[#e7ece8] rounded-2xl shadow-xs">
            <RefreshCw className="w-8 h-8 text-[#155c32] animate-spin mb-3" />
            <p className="text-gray-500 text-xs font-medium">Loading your cart items...</p>
          </div>
        ) : !cart || cart.is_empty || cart.items.length === 0 ? (
          /* Empty Cart State */
          <div className="bg-white border border-[#e7ece8] rounded-2xl p-10 text-center max-w-xl mx-auto shadow-xs my-6">
            <div className="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-[#e7ece8]">
              <ShoppingCart className="w-7 h-7 text-[#155c32]" />
            </div>
            <h2 className="text-xl font-extrabold text-gray-900 mb-1.5">Your Cart is Empty</h2>
            <p className="text-gray-500 text-xs mb-6 max-w-md mx-auto leading-relaxed">
              You haven&apos;t added any direct fuel products or marketplace energy solutions to your cart yet.
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
              <Link
                href="/marketplace"
                className="w-full sm:w-auto bg-[#155c32] hover:bg-[#104827] text-white font-bold px-5 py-2.5 rounded-xl transition-colors text-xs shadow-md"
              >
                Browse Marketplace
              </Link>
              <Link
                href="/"
                className="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold px-5 py-2.5 rounded-xl transition-colors text-xs"
              >
                Direct Commerce Fuel
              </Link>
            </div>
          </div>
        ) : (
          /* Active Cart Layout */
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {/* Left Column: Seller-Grouped Items */}
            <div className="lg:col-span-8 space-y-4">

              {/* Multi-Seller Notice Banner */}
              {cart.has_multiple_sellers && (
                <div className="bg-amber-50/80 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                  <Store className="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                  <div className="text-xs text-amber-900 leading-relaxed">
                    <span className="font-bold block text-sm text-amber-950 mb-0.5">
                      Multi-Seller Fulfillment Notice
                    </span>
                    Your cart contains items from multiple sellers. Independent fulfillment orders will be generated for each seller group at checkout to ensure transparent dispatch.
                  </div>
                </div>
              )}

              {/* Loop through Seller Groups */}
              {cart.seller_groups.map((group: SellerGroup, idx: number) => (
                <div
                  key={idx}
                  className="bg-white border border-[#e7ece8] rounded-2xl shadow-xs overflow-hidden"
                >
                  {/* Seller Header */}
                  <div className="bg-gray-50/80 border-b border-[#e7ece8] px-5 py-3.5 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      {group.is_first_party ? (
                        <div className="p-2 bg-[#155c32]/10 rounded-xl text-[#155c32]">
                          <ShieldCheck className="w-4 h-4" />
                        </div>
                      ) : (
                        <div className="p-2 bg-sky-50 rounded-xl text-sky-700">
                          <Building2 className="w-4 h-4" />
                        </div>
                      )}
                      <div>
                        <div className="flex items-center gap-2">
                          <h3 className="font-bold text-gray-900 text-sm">
                            {group.seller_name}
                          </h3>
                          {group.is_first_party ? (
                            <span className="bg-[#155c32] text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                              FuelCab Direct
                            </span>
                          ) : (
                            <span className="bg-sky-100 text-sky-800 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                              Verified Supplier
                            </span>
                          )}
                        </div>
                        <span className="text-[11px] text-gray-500">
                          Channel: {group.sales_channel === "direct" ? "Direct Delivery" : "Marketplace Procurement"}
                        </span>
                      </div>
                    </div>
                    <div className="text-right">
                      <span className="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Subtotal</span>
                      <span className="text-sm font-extrabold text-gray-900">
                        ₹{group.subtotal.toLocaleString("en-IN")}
                      </span>
                    </div>
                  </div>

                  {/* Line Items List */}
                  <div className="divide-y divide-gray-100">
                    {group.items.map((item) => (
                      <div
                        key={item.id}
                        className="p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-gray-50/40 transition-colors"
                      >
                        {/* Item Details */}
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-0.5">
                            <h4 className="font-bold text-gray-900 text-sm">
                              {item.product_name_snapshot}
                            </h4>
                            {item.is_price_stale && (
                              <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                <AlertCircle className="w-3 h-3" /> Updated Price
                              </span>
                            )}
                          </div>
                          
                          <div className="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-gray-500">
                            {item.product_sku_snapshot && (
                              <span>SKU: {item.product_sku_snapshot}</span>
                            )}
                            <span>Unit: {item.unit_of_measure}</span>
                            <span className="text-gray-900 font-semibold">
                              ₹{item.price_snapshot.toLocaleString("en-IN")} / {item.unit_of_measure}
                            </span>
                          </div>
                        </div>

                        {/* Quantity controls & Line Total */}
                        <div className="flex items-center justify-between sm:justify-end gap-5 w-full sm:w-auto">
                          
                          {/* Quantity Selector */}
                          <div className="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-white shadow-xs">
                            <button
                              onClick={() => handleQuantityChange(item.id, item.quantity - 1)}
                              disabled={busyItemId === item.id || item.quantity <= 1}
                              className="p-2 text-gray-600 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                              title="Decrease quantity"
                            >
                              <Minus className="w-3.5 h-3.5" />
                            </button>
                            <input
                              type="number"
                              value={item.quantity}
                              onChange={(e) => {
                                const val = parseFloat(e.target.value);
                                if (!isNaN(val) && val > 0) {
                                  handleQuantityChange(item.id, val);
                                }
                              }}
                              className="w-14 text-center text-xs font-bold text-gray-900 focus:outline-none border-x border-gray-200 py-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <button
                              onClick={() => handleQuantityChange(item.id, item.quantity + 1)}
                              disabled={busyItemId === item.id}
                              className="p-2 text-gray-600 hover:bg-gray-100 disabled:opacity-40 transition-colors"
                              title="Increase quantity"
                            >
                              <Plus className="w-3.5 h-3.5" />
                            </button>
                          </div>

                          {/* Line Total Price */}
                          <div className="text-right min-w-20">
                            <span className="text-sm font-extrabold text-gray-900 block">
                              ₹{item.line_total.toLocaleString("en-IN")}
                            </span>
                          </div>

                          {/* Remove Item Button */}
                          <button
                            onClick={() => handleRemove(item.id)}
                            disabled={busyItemId === item.id}
                            className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors"
                            title="Remove item"
                          >
                            <Trash2 className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}

              {/* Clear Cart Link */}
              <div className="flex justify-between items-center pt-1">
                <button
                  onClick={clearCart}
                  className="text-xs text-red-600 hover:text-red-800 font-bold inline-flex items-center gap-1.5"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                  <span>Clear Entire Cart</span>
                </button>
                <span className="text-[11px] text-gray-400 font-medium">
                  All items are backed up to your account session.
                </span>
              </div>
            </div>

            {/* Right Column: Order Summary Sidebar */}
            <div className="lg:col-span-4 sticky top-24">
              <div className="bg-white border border-[#e7ece8] rounded-2xl p-5 shadow-xs space-y-5">
                <h3 className="text-base font-extrabold text-gray-900 border-b border-[#e7ece8] pb-3">
                  Order Summary
                </h3>

                <div className="space-y-2.5 text-xs">
                  <div className="flex justify-between text-gray-600">
                    <span>Total Items</span>
                    <span className="font-bold text-gray-900">{cart.item_count}</span>
                  </div>

                  <div className="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span className="font-bold text-gray-900">
                      ₹{cart.total.toLocaleString("en-IN")}
                    </span>
                  </div>

                  <div className="flex justify-between text-gray-600">
                    <span>Estimated GST (18%)</span>
                    <span className="font-bold text-gray-900">
                      ₹{(cart.total * 0.18).toLocaleString("en-IN")}
                    </span>
                  </div>

                  <div className="flex justify-between text-gray-600">
                    <span>Delivery Charge</span>
                    <span className="text-emerald-700 font-bold">Calculated at Checkout</span>
                  </div>
                </div>

                <div className="border-t border-[#e7ece8] pt-3 flex justify-between items-baseline">
                  <span className="text-xs font-bold text-gray-900">Total (excl. delivery)</span>
                  <div className="text-right">
                    <span className="text-xl font-extrabold text-[#155c32] block">
                      ₹{(cart.total * 1.18).toLocaleString("en-IN")}
                    </span>
                    <span className="text-[10px] text-gray-400">Includes applicable GST</span>
                  </div>
                </div>

                <Link
                  href="/order"
                  className="w-full bg-[#155c32] hover:bg-[#104827] text-white font-bold py-3 px-5 rounded-xl flex items-center justify-center gap-2 transition-all shadow-md hover:shadow-lg text-xs"
                >
                  <span>Proceed to Checkout</span>
                  <ArrowRight className="w-4 h-4" />
                </Link>

                <div className="bg-gray-50/80 rounded-xl p-3.5 text-xs text-gray-500 space-y-1.5 border border-[#e7ece8]">
                  <div className="flex items-center gap-2 text-gray-800 font-bold">
                    <ShieldCheck className="w-4 h-4 text-[#33b248]" />
                    <span>Enterprise Security Guaranteed</span>
                  </div>
                  <p className="text-[11px] leading-relaxed text-gray-500">
                    Verified suppliers, quality test documentation, and location delivery tracking guaranteed for all orders.
                  </p>
                </div>
              </div>
            </div>

          </div>
        )}
      </main>

      <Footer />
    </div>
  );
}
