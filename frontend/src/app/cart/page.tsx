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

  useEffect(() => {
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
    <div className="min-h-screen bg-[#fafbfa] text-[#1a1a1a]">
      <Navbar />

      {/* Header Banner */}
      <div className="bg-[#155c32] text-white py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <div className="flex items-center gap-2 text-[#33b248] text-sm font-semibold tracking-wider uppercase mb-1">
              <ShoppingCart className="w-4 h-4" />
              <span>Procurement Cart</span>
            </div>
            <h1 className="text-3xl font-bold font-heading">Your Fuel & Energy Cart</h1>
            <p className="text-emerald-100 text-sm mt-1">
              Review line items, seller fulfillment groups, and order summaries.
            </p>
          </div>
          <Link
            href="/marketplace"
            className="inline-flex items-center gap-2 bg-[#33b248] hover:bg-[#2ba03f] text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors"
          >
            <span>Explore Marketplace</span>
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      </div>

      {/* Action Error Alert */}
      {actionError && (
        <div className="max-w-7xl mx-auto px-4 mt-6">
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
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {loading && !cart ? (
          <div className="flex flex-col items-center justify-center py-20">
            <RefreshCw className="w-8 h-8 text-[#155c32] animate-spin mb-3" />
            <p className="text-gray-500 text-sm">Loading your cart items...</p>
          </div>
        ) : !cart || cart.is_empty || cart.items.length === 0 ? (
          /* Empty Cart State */
          <div className="bg-white border border-gray-200 rounded-2xl p-12 text-center max-w-2xl mx-auto shadow-sm">
            <div className="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
              <ShoppingCart className="w-8 h-8 text-[#155c32]" />
            </div>
            <h2 className="text-xl font-bold text-gray-900 mb-2 font-heading">Your Cart is Empty</h2>
            <p className="text-gray-600 text-sm mb-6 max-w-md mx-auto">
              You haven&apos;t added any direct fuel products or marketplace energy solutions to your cart yet.
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center gap-3">
              <Link
                href="/marketplace"
                className="w-full sm:w-auto bg-[#155c32] hover:bg-[#104827] text-white font-medium px-6 py-3 rounded-xl transition-colors text-sm"
              >
                Browse Marketplace
              </Link>
              <Link
                href="/"
                className="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium px-6 py-3 rounded-xl transition-colors text-sm"
              >
                Direct Commerce Fuel
              </Link>
            </div>
          </div>
        ) : (
          /* Active Cart Layout */
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {/* Left Column: Seller-Grouped Items */}
            <div className="lg:col-span-8 space-y-6">

              {/* Multi-Seller Notice Banner */}
              {cart.has_multiple_sellers && (
                <div className="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                  <Store className="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
                  <div className="text-xs text-amber-900 leading-relaxed">
                    <span className="font-semibold block text-sm text-amber-950 mb-0.5">
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
                  className="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden"
                >
                  {/* Seller Header */}
                  <div className="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                      {group.is_first_party ? (
                        <div className="p-2 bg-[#155c32]/10 rounded-lg text-[#155c32]">
                          <ShieldCheck className="w-5 h-5" />
                        </div>
                      ) : (
                        <div className="p-2 bg-sky-50 rounded-lg text-sky-700">
                          <Building2 className="w-5 h-5" />
                        </div>
                      )}
                      <div>
                        <div className="flex items-center gap-2">
                          <h3 className="font-semibold text-gray-900 text-base">
                            {group.seller_name}
                          </h3>
                          {group.is_first_party ? (
                            <span className="bg-[#155c32] text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                              FuelCab Direct
                            </span>
                          ) : (
                            <span className="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                              Verified Supplier
                            </span>
                          )}
                        </div>
                        <span className="text-xs text-gray-500">
                          Channel: {group.sales_channel === "direct" ? "Direct Delivery" : "Marketplace Procurement"}
                        </span>
                      </div>
                    </div>
                    <div className="text-right">
                      <span className="text-xs text-gray-500 block">Subtotal</span>
                      <span className="text-base font-bold text-gray-900">
                        ₹{group.subtotal.toLocaleString("en-IN")}
                      </span>
                    </div>
                  </div>

                  {/* Line Items List */}
                  <div className="divide-y divide-gray-100">
                    {group.items.map((item) => (
                      <div
                        key={item.id}
                        className="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-gray-50/50 transition-colors"
                      >
                        {/* Item Details */}
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <h4 className="font-semibold text-gray-900 text-base">
                              {item.product_name_snapshot}
                            </h4>
                            {item.is_price_stale && (
                              <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-800 text-[11px] font-medium px-2 py-0.5 rounded">
                                <AlertCircle className="w-3 h-3" /> Updated Price
                              </span>
                            )}
                          </div>
                          
                          <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500">
                            {item.product_sku_snapshot && (
                              <span>SKU: {item.product_sku_snapshot}</span>
                            )}
                            <span>Unit: {item.unit_of_measure}</span>
                            <span className="text-gray-900 font-medium">
                              ₹{item.price_snapshot.toLocaleString("en-IN")} / {item.unit_of_measure}
                            </span>
                          </div>
                        </div>

                        {/* Quantity controls & Line Total */}
                        <div className="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto">
                          
                          {/* Quantity Selector */}
                          <div className="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-xs">
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
                              className="w-16 text-center text-sm font-semibold text-gray-900 focus:outline-none border-x border-gray-200 py-1 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
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
                          <div className="text-right min-w-24">
                            <span className="text-base font-bold text-gray-900 block">
                              ₹{item.line_total.toLocaleString("en-IN")}
                            </span>
                          </div>

                          {/* Remove Item Button */}
                          <button
                            onClick={() => handleRemove(item.id)}
                            disabled={busyItemId === item.id}
                            className="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
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
              <div className="flex justify-between items-center pt-2">
                <button
                  onClick={clearCart}
                  className="text-xs text-red-600 hover:text-red-800 font-medium inline-flex items-center gap-1.5"
                >
                  <Trash2 className="w-3.5 h-3.5" />
                  <span>Clear Entire Cart</span>
                </button>
                <span className="text-xs text-gray-500">
                  All items are backed up to your account session.
                </span>
              </div>
            </div>

            {/* Right Column: Order Summary Sidebar */}
            <div className="lg:col-span-4 sticky top-24">
              <div className="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-6">
                <h3 className="text-lg font-bold text-gray-900 font-heading border-b border-gray-100 pb-3">
                  Order Summary
                </h3>

                <div className="space-y-3 text-sm">
                  <div className="flex justify-between text-gray-600">
                    <span>Total Items</span>
                    <span className="font-semibold text-gray-900">{cart.item_count}</span>
                  </div>

                  <div className="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span className="font-semibold text-gray-900">
                      ₹{cart.total.toLocaleString("en-IN")}
                    </span>
                  </div>

                  <div className="flex justify-between text-gray-600">
                    <span>Estimated GST (18%)</span>
                    <span className="font-semibold text-gray-900">
                      ₹{(cart.total * 0.18).toLocaleString("en-IN")}
                    </span>
                  </div>

                  <div className="flex justify-between text-gray-600">
                    <span>Delivery Charge</span>
                    <span className="text-emerald-700 font-medium">Calculated at Checkout</span>
                  </div>
                </div>

                <div className="border-t border-gray-200 pt-4 flex justify-between items-baseline">
                  <span className="text-base font-bold text-gray-900">Total (excl. delivery)</span>
                  <div className="text-right">
                    <span className="text-2xl font-bold text-[#155c32] block">
                      ₹{(cart.total * 1.18).toLocaleString("en-IN")}
                    </span>
                    <span className="text-[11px] text-gray-500">Includes applicable GST</span>
                  </div>
                </div>

                <Link
                  href="/order"
                  className="w-full bg-[#155c32] hover:bg-[#104827] text-white font-semibold py-3.5 px-6 rounded-xl flex items-center justify-center gap-2 transition-colors shadow-md hover:shadow-lg text-sm"
                >
                  <span>Proceed to Checkout</span>
                  <ArrowRight className="w-4 h-4" />
                </Link>

                <div className="bg-gray-50 rounded-xl p-4 text-xs text-gray-500 space-y-2 border border-gray-100">
                  <div className="flex items-center gap-2 text-gray-700 font-medium">
                    <ShieldCheck className="w-4 h-4 text-[#33b248]" />
                    <span>Enterprise Security Guaranteed</span>
                  </div>
                  <p className="leading-relaxed">
                    Verified suppliers, quality test documentation, and location delivery tracking guaranteed for all marketplace orders.
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
