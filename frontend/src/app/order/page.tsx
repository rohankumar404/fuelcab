"use client";

import { useState } from "react";
import Link from "next/link";
import {
  Droplet, MapPin, Package, Truck, ChevronDown, Star, ArrowRight, ShieldCheck, Clock,
} from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

const FUEL_TYPES = [
  { id: "diesel", label: "Diesel (HSD)", icon: "⛽", price: "₹94.72/L", color: "bg-amber-50 border-amber-200 text-amber-700" },
  { id: "petrol", label: "Petrol (MS)",  icon: "🔴", price: "₹102.46/L", color: "bg-red-50 border-red-200 text-red-700" },
  { id: "cng",    label: "CNG",          icon: "🌿", price: "₹76.59/Kg", color: "bg-green-50 border-green-200 text-green-700" },
  { id: "lpo",    label: "LPG",          icon: "🔵", price: "₹801.50/Cyl", color: "bg-blue-50 border-blue-200 text-blue-700" },
];

const QUANTITIES = ["100 L", "250 L", "500 L", "1,000 L", "2,500 L", "5,000 L", "10,000 L+"];
const SLOTS = ["6:00 AM – 9:00 AM", "9:00 AM – 12:00 PM", "12:00 PM – 3:00 PM", "3:00 PM – 6:00 PM", "6:00 PM – 9:00 PM"];

export default function OrderPage() {
  const [selectedFuel, setSelectedFuel] = useState("diesel");
  const [quantity, setQuantity] = useState("500 L");
  const [slot, setSlot] = useState("");
  const [address, setAddress] = useState("");
  const [name, setName] = useState("");
  const [phone, setPhone] = useState("");
  const [submitted, setSubmitted] = useState(false);

  const fuel = FUEL_TYPES.find((f) => f.id === selectedFuel)!;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitted(true);
  };

  if (submitted) {
    return (
      <div className="min-h-screen bg-[#fafbfa] flex flex-col">
        <Navbar />
        <main className="flex-1 flex items-center justify-center px-4">
          <div className="text-center max-w-md">
            <div className="w-20 h-20 rounded-full bg-[#155c32]/10 flex items-center justify-center mx-auto mb-6">
              <ShieldCheck className="w-10 h-10 text-[#155c32]" />
            </div>
            <h1 className="text-2xl font-bold text-[#1a1a1a] mb-3">Order Placed Successfully!</h1>
            <p className="text-[#555] mb-8">
              Your fuel order has been received. We&apos;ll confirm delivery slot &amp; assign a vendor shortly via SMS and email.
            </p>
            <div className="flex gap-3 justify-center">
              <Link
                href="/"
                className="h-11 px-6 rounded-xl border border-[#e7ece8] text-[#1a1a1a] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition flex items-center"
              >
                Back to Home
              </Link>
              <button
                onClick={() => setSubmitted(false)}
                className="h-11 px-6 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] transition flex items-center gap-2"
              >
                New Order <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-[#fafbfa] flex flex-col">
      <Navbar />
      <main className="flex-1">
        {/* Hero */}
        <section className="bg-gradient-to-r from-[#155c32] to-[#0d3a1f] py-12 px-4">
          <div className="max-w-4xl mx-auto text-center">
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 mb-4">
              <span className="w-1.5 h-1.5 rounded-full bg-[#33b248] animate-pulse" />
              <span className="text-xs font-bold uppercase tracking-widest text-[#33b248]">Instant Booking</span>
            </div>
            <h1 className="text-3xl sm:text-4xl font-extrabold text-white mb-3">Order Bulk Fuel</h1>
            <p className="text-gray-300 text-sm max-w-xl mx-auto">
              Minimum 100 litres · Same-day delivery available · GST invoice included
            </p>
          </div>
        </section>

        <section className="max-w-4xl mx-auto px-4 py-10">
          <form onSubmit={handleSubmit} className="grid grid-cols-1 lg:grid-cols-5 gap-8">
            {/* Left — Order Form */}
            <div className="lg:col-span-3 space-y-8">
              {/* Step 1: Fuel Type */}
              <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                <div className="flex items-center gap-2 mb-5">
                  <span className="w-6 h-6 rounded-full bg-[#155c32] text-white text-xs font-bold flex items-center justify-center">1</span>
                  <h2 className="font-bold text-[#1a1a1a]">Select Fuel Type</h2>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  {FUEL_TYPES.map((f) => (
                    <button
                      key={f.id} type="button"
                      onClick={() => setSelectedFuel(f.id)}
                      className={`p-4 rounded-xl border-2 text-left transition-all duration-150 ${
                        selectedFuel === f.id
                          ? "border-[#155c32] bg-[#155c32]/5"
                          : "border-[#e7ece8] hover:border-[#155c32]/40"
                      }`}
                    >
                      <span className="text-2xl">{f.icon}</span>
                      <p className="text-sm font-bold text-[#1a1a1a] mt-2">{f.label}</p>
                      <p className="text-xs text-[#555] mt-0.5">{f.price}</p>
                    </button>
                  ))}
                </div>
              </div>

              {/* Step 2: Quantity */}
              <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                <div className="flex items-center gap-2 mb-5">
                  <span className="w-6 h-6 rounded-full bg-[#155c32] text-white text-xs font-bold flex items-center justify-center">2</span>
                  <h2 className="font-bold text-[#1a1a1a]">Quantity Required</h2>
                </div>
                <div className="relative">
                  <Package className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999]" />
                  <ChevronDown className="absolute right-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999] pointer-events-none" />
                  <select
                    value={quantity}
                    onChange={(e) => setQuantity(e.target.value)}
                    className="w-full h-11 pl-10 pr-10 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] focus:outline-none focus:border-[#155c32] focus:ring-2 focus:ring-[#155c32]/10 appearance-none transition"
                  >
                    {QUANTITIES.map((q) => <option key={q} value={q}>{q}</option>)}
                  </select>
                </div>
              </div>

              {/* Step 3: Delivery Address */}
              <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                <div className="flex items-center gap-2 mb-5">
                  <span className="w-6 h-6 rounded-full bg-[#155c32] text-white text-xs font-bold flex items-center justify-center">3</span>
                  <h2 className="font-bold text-[#1a1a1a]">Delivery Location</h2>
                </div>
                <div className="space-y-4">
                  <div>
                    <label htmlFor="order-name" className="block text-sm font-semibold text-[#1a1a1a] mb-2">Contact Name</label>
                    <div className="relative">
                      <input
                        id="order-name" type="text" required placeholder="Your name" value={name}
                        onChange={(e) => setName(e.target.value)}
                        className="w-full h-11 px-4 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] transition"
                      />
                    </div>
                  </div>
                  <div>
                    <label htmlFor="order-phone" className="block text-sm font-semibold text-[#1a1a1a] mb-2">Phone Number</label>
                    <input
                      id="order-phone" type="tel" required placeholder="+91 98765 43210" value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      className="w-full h-11 px-4 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] transition"
                    />
                  </div>
                  <div>
                    <label htmlFor="order-address" className="block text-sm font-semibold text-[#1a1a1a] mb-2">Delivery Address</label>
                    <div className="relative">
                      <MapPin className="absolute left-3.5 top-3.5 w-4 h-4 text-[#999]" />
                      <textarea
                        id="order-address" required rows={3} placeholder="Site address, landmark, PIN code..."
                        value={address} onChange={(e) => setAddress(e.target.value)}
                        className="w-full pl-10 pr-4 py-3 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] transition resize-none"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Step 4: Delivery Slot */}
              <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                <div className="flex items-center gap-2 mb-5">
                  <span className="w-6 h-6 rounded-full bg-[#155c32] text-white text-xs font-bold flex items-center justify-center">4</span>
                  <h2 className="font-bold text-[#1a1a1a]">Preferred Delivery Slot</h2>
                </div>
                <div className="grid grid-cols-1 gap-2">
                  {SLOTS.map((s) => (
                    <button
                      key={s} type="button"
                      onClick={() => setSlot(s)}
                      className={`flex items-center gap-3 px-4 py-3 rounded-xl border transition-all duration-150 text-sm font-medium text-left ${
                        slot === s
                          ? "border-[#155c32] bg-[#155c32]/5 text-[#155c32]"
                          : "border-[#e7ece8] text-[#555] hover:border-[#155c32]/40"
                      }`}
                    >
                      <Clock className="w-4 h-4 flex-shrink-0" />
                      {s}
                    </button>
                  ))}
                </div>
              </div>
            </div>

            {/* Right — Order Summary */}
            <div className="lg:col-span-2">
              <div className="sticky top-24 bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                <h2 className="font-bold text-[#1a1a1a] mb-5 flex items-center gap-2">
                  <Truck className="w-4 h-4 text-[#155c32]" />
                  Order Summary
                </h2>

                <div className="space-y-4 text-sm">
                  <div className="flex justify-between text-[#555]">
                    <span>Fuel Type</span>
                    <span className="font-semibold text-[#1a1a1a]">{fuel.label}</span>
                  </div>
                  <div className="flex justify-between text-[#555]">
                    <span>Quantity</span>
                    <span className="font-semibold text-[#1a1a1a]">{quantity}</span>
                  </div>
                  <div className="flex justify-between text-[#555]">
                    <span>Unit Price</span>
                    <span className="font-semibold text-[#1a1a1a]">{fuel.price}</span>
                  </div>
                  <div className="flex justify-between text-[#555]">
                    <span>Delivery</span>
                    <span className="font-semibold text-green-600">Free</span>
                  </div>
                  {slot && (
                    <div className="flex justify-between text-[#555]">
                      <span>Slot</span>
                      <span className="font-semibold text-[#1a1a1a] text-xs text-right max-w-[140px]">{slot}</span>
                    </div>
                  )}
                  <div className="border-t border-[#e7ece8] pt-4 flex justify-between font-bold text-[#1a1a1a]">
                    <span>Estimated Total</span>
                    <span className="text-[#155c32]">Get Quote</span>
                  </div>
                </div>

                {/* Trust badges */}
                <div className="mt-5 space-y-2 text-xs text-[#888]">
                  {["GST Invoice Provided", "Certified Fuel Vendors", "ISO 9001 Compliant"].map((t) => (
                    <div key={t} className="flex items-center gap-2">
                      <Star className="w-3 h-3 text-[#155c32] fill-[#155c32]" />
                      {t}
                    </div>
                  ))}
                </div>

                <button
                  type="submit"
                  className="mt-6 w-full h-12 rounded-xl bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] hover:shadow-lg hover:shadow-[#155c32]/25 transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2"
                >
                  Place Order
                  <ArrowRight className="w-4 h-4" />
                </button>
                <p className="text-center text-[10px] text-[#aaa] mt-3">
                  You&apos;ll receive a confirmation call within 30 mins
                </p>
              </div>
            </div>
          </form>
        </section>
      </main>
      <Footer />
    </div>
  );
}
