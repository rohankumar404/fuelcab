"use client";

import { useState } from "react";
import Link from "next/link";
import {
  Droplet, Mail, Lock, Eye, EyeOff, User, Phone, Building2,
  MapPin, FileText, ArrowRight, ShieldCheck, CheckCircle2,
} from "lucide-react";

const FUEL_TYPES = ["Diesel (HSD)", "Petrol (MS)", "CNG", "LPG", "Lubricants"];
const STATES = [
  "Delhi", "Uttar Pradesh", "Maharashtra", "Haryana", "Rajasthan",
  "Punjab", "Gujarat", "Karnataka", "Tamil Nadu", "Telangana",
];

export default function VendorRegisterPage() {
  const [step, setStep] = useState(1);
  const [showPwd, setShowPwd] = useState(false);
  const [selectedFuels, setSelectedFuels] = useState<string[]>([]);
  const [form, setForm] = useState({
    name: "", company: "", email: "", phone: "", gst: "",
    state: "", city: "", address: "", password: "", confirmPassword: "",
  });

  const update = (k: string, v: string) => setForm((f) => ({ ...f, [k]: v }));

  const toggleFuel = (fuel: string) =>
    setSelectedFuels((prev) =>
      prev.includes(fuel) ? prev.filter((f) => f !== fuel) : [...prev, fuel]
    );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log("Vendor Register:", { ...form, fuels: selectedFuels });
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#0d3a1f] via-[#155c32] to-[#0d3a1f] flex items-center justify-center px-4 py-12">
      <div className="relative w-full max-w-2xl">
        {/* Logo */}
        <div className="text-center mb-8">
          <Link href="/" className="inline-flex items-center gap-2.5">
            <div className="w-10 h-10 rounded-xl bg-[#33b248] flex items-center justify-center">
              <Droplet className="w-5 h-5 text-white fill-white" />
            </div>
            <span className="text-2xl font-extrabold tracking-tight text-white">
              Fuel<span className="text-[#33b248]">Cab</span>
            </span>
          </Link>
          <div className="inline-flex items-center gap-1.5 mt-4 px-3 py-1.5 rounded-full bg-[#33b248]/20 border border-[#33b248]/30">
            <ShieldCheck className="w-3.5 h-3.5 text-[#33b248]" />
            <span className="text-xs font-bold text-[#33b248] uppercase tracking-widest">Vendor Registration</span>
          </div>
          <h1 className="mt-4 text-2xl font-bold text-white">Join FuelCab as a Supplier</h1>
          <p className="mt-2 text-sm text-gray-400">Expand your reach and manage orders with ease</p>
        </div>

        {/* Step indicator */}
        <div className="flex items-center justify-center gap-3 mb-8">
          {[1, 2].map((s) => (
            <div key={s} className="flex items-center gap-3">
              <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300 ${
                step >= s ? "bg-[#33b248] text-white" : "bg-white/10 text-gray-400"
              }`}>
                {step > s ? <CheckCircle2 className="w-4 h-4" /> : s}
              </div>
              <span className={`text-xs font-semibold ${step >= s ? "text-white" : "text-gray-500"}`}>
                {s === 1 ? "Business Info" : "Account Setup"}
              </span>
              {s < 2 && <div className={`w-12 h-px ${step > s ? "bg-[#33b248]" : "bg-white/10"}`} />}
            </div>
          ))}
        </div>

        {/* Card */}
        <div className="bg-white/5 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/10 p-8">
          <form onSubmit={handleSubmit}>
            {step === 1 && (
              <div className="space-y-5">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  {[
                    { id: "vendor-name",    label: "Contact Person",    icon: User,      type: "text",  key: "name",    placeholder: "Your Name" },
                    { id: "vendor-company", label: "Company Name",      icon: Building2, type: "text",  key: "company", placeholder: "ACME Fuels Pvt. Ltd." },
                    { id: "vendor-email",   label: "Business Email",    icon: Mail,      type: "email", key: "email",   placeholder: "vendor@company.com" },
                    { id: "vendor-phone",   label: "Phone Number",      icon: Phone,     type: "tel",   key: "phone",   placeholder: "+91 98765 43210" },
                    { id: "vendor-gst",     label: "GST Number",        icon: FileText,  type: "text",  key: "gst",     placeholder: "22AAAAA0000A1Z5" },
                    { id: "vendor-city",    label: "City",              icon: MapPin,    type: "text",  key: "city",    placeholder: "Noida" },
                  ].map(({ id, label, icon: Icon, type, key, placeholder }) => (
                    <div key={id}>
                      <label htmlFor={id} className="block text-sm font-semibold text-white mb-2">{label}</label>
                      <div className="relative">
                        <Icon className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                          id={id} type={type} required placeholder={placeholder}
                          value={form[key as keyof typeof form]}
                          onChange={(e) => update(key, e.target.value)}
                          className="w-full h-11 pl-10 pr-4 rounded-xl bg-white/8 border border-white/15 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 transition"
                        />
                      </div>
                    </div>
                  ))}
                </div>

                {/* State dropdown */}
                <div>
                  <label htmlFor="vendor-state" className="block text-sm font-semibold text-white mb-2">State</label>
                  <select
                    id="vendor-state"
                    required
                    value={form.state}
                    onChange={(e) => update("state", e.target.value)}
                    className="w-full h-11 px-4 rounded-xl bg-white/8 border border-white/15 text-sm text-white focus:outline-none focus:border-[#33b248] transition"
                  >
                    <option value="" disabled className="bg-[#0d3a1f]">Select State</option>
                    {STATES.map((s) => (
                      <option key={s} value={s} className="bg-[#0d3a1f]">{s}</option>
                    ))}
                  </select>
                </div>

                {/* Fuel types */}
                <div>
                  <p className="text-sm font-semibold text-white mb-3">Fuels You Supply</p>
                  <div className="flex flex-wrap gap-2">
                    {FUEL_TYPES.map((fuel) => (
                      <button
                        key={fuel} type="button"
                        onClick={() => toggleFuel(fuel)}
                        className={`px-3 py-1.5 rounded-full text-xs font-semibold border transition-all duration-150 ${
                          selectedFuels.includes(fuel)
                            ? "bg-[#33b248] border-[#33b248] text-white"
                            : "bg-white/5 border-white/15 text-gray-300 hover:border-[#33b248]/50"
                        }`}
                      >
                        {fuel}
                      </button>
                    ))}
                  </div>
                </div>

                <button
                  type="button"
                  onClick={() => setStep(2)}
                  className="w-full h-11 rounded-xl bg-[#33b248] text-white font-semibold text-sm hover:bg-[#2a9a3d] transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2"
                >
                  Continue <ArrowRight className="w-4 h-4" />
                </button>
              </div>
            )}

            {step === 2 && (
              <div className="space-y-5">
                <div>
                  <label htmlFor="v-password" className="block text-sm font-semibold text-white mb-2">Password</label>
                  <div className="relative">
                    <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                      id="v-password"
                      type={showPwd ? "text" : "password"}
                      required
                      placeholder="Min. 8 characters"
                      value={form.password}
                      onChange={(e) => update("password", e.target.value)}
                      className="w-full h-11 pl-10 pr-11 rounded-xl bg-white/8 border border-white/15 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] transition"
                    />
                    <button type="button" onClick={() => setShowPwd((v) => !v)}
                      className="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#33b248] transition">
                      {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                </div>

                <div>
                  <label htmlFor="v-confirm" className="block text-sm font-semibold text-white mb-2">Confirm Password</label>
                  <div className="relative">
                    <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                      id="v-confirm"
                      type={showPwd ? "text" : "password"}
                      required
                      placeholder="Re-enter password"
                      value={form.confirmPassword}
                      onChange={(e) => update("confirmPassword", e.target.value)}
                      className="w-full h-11 pl-10 pr-4 rounded-xl bg-white/8 border border-white/15 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] transition"
                    />
                  </div>
                </div>

                {/* Warehouse address */}
                <div>
                  <label htmlFor="v-address" className="block text-sm font-semibold text-white mb-2">Warehouse / Depot Address</label>
                  <textarea
                    id="v-address"
                    required
                    rows={3}
                    placeholder="Full address of your primary depot..."
                    value={form.address}
                    onChange={(e) => update("address", e.target.value)}
                    className="w-full px-4 py-3 rounded-xl bg-white/8 border border-white/15 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] transition resize-none"
                  />
                </div>

                <p className="text-xs text-gray-400">
                  By registering you agree to our{" "}
                  <Link href="#terms" className="text-[#33b248] hover:underline">Terms &amp; Conditions</Link> and{" "}
                  <Link href="#privacy" className="text-[#33b248] hover:underline">Privacy Policy</Link>.
                </p>

                <div className="flex gap-3">
                  <button
                    type="button"
                    onClick={() => setStep(1)}
                    className="flex-1 h-11 rounded-xl border border-white/15 text-gray-300 font-semibold text-sm hover:border-white/40 transition"
                  >
                    ← Back
                  </button>
                  <button
                    type="submit"
                    className="flex-1 h-11 rounded-xl bg-[#33b248] text-white font-semibold text-sm hover:bg-[#2a9a3d] transition-all duration-200 flex items-center justify-center gap-2"
                  >
                    Submit Application <ArrowRight className="w-4 h-4" />
                  </button>
                </div>
              </div>
            )}
          </form>

          <p className="text-center text-sm text-gray-400 mt-6">
            Already registered?{" "}
            <Link href="/vendor/login" className="text-[#33b248] font-semibold hover:underline">
              Vendor Login
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}
