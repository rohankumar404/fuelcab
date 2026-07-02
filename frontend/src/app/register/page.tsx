"use client";

import { useState } from "react";
import Link from "next/link";
import {
  Droplet, Mail, Lock, Eye, EyeOff, User, Phone, Building2, ArrowRight,
} from "lucide-react";

export default function RegisterPage() {
  const [showPassword, setShowPassword] = useState(false);
  const [form, setForm] = useState({
    name: "", company: "", email: "", phone: "", password: "", confirmPassword: "",
  });

  const update = (k: string, v: string) => setForm((f) => ({ ...f, [k]: v }));

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log("Register:", form);
  };

  const fields = [
    { id: "name",            label: "Full Name",        icon: User,      type: "text",     key: "name",            placeholder: "John Doe" },
    { id: "company",         label: "Company / Business",icon: Building2, type: "text",     key: "company",         placeholder: "ACME Pvt. Ltd." },
    { id: "email",           label: "Business Email",   icon: Mail,      type: "email",    key: "email",           placeholder: "you@company.com" },
    { id: "phone",           label: "Phone Number",     icon: Phone,     type: "tel",      key: "phone",           placeholder: "+91 98765 43210" },
  ];

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#f4f8f5] via-white to-[#e8f4ec] flex items-center justify-center px-4 py-12">
      <div className="w-full max-w-lg">
        {/* Logo */}
        <div className="text-center mb-8">
          <Link href="/" className="inline-flex items-center gap-2.5">
            <div className="w-10 h-10 rounded-xl bg-[#155c32] flex items-center justify-center">
              <Droplet className="w-5 h-5 text-[#33b248] fill-[#33b248]" />
            </div>
            <span className="text-2xl font-extrabold tracking-tight text-[#1a1a1a]">
              Fuel<span className="text-[#155c32]">Cab</span>
            </span>
          </Link>
          <h1 className="mt-6 text-2xl font-bold text-[#1a1a1a]">Create your account</h1>
          <p className="mt-2 text-sm text-[#555555]">Start ordering bulk fuel for your business</p>
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl shadow-xl shadow-[#155c32]/8 border border-[#e7ece8] p-8">
          <form onSubmit={handleSubmit} className="space-y-5">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {fields.map(({ id, label, icon: Icon, type, key, placeholder }) => (
                <div key={id} className={id === "email" || id === "phone" ? "sm:col-span-1" : ""}>
                  <label htmlFor={id} className="block text-sm font-semibold text-[#1a1a1a] mb-2">
                    {label}
                  </label>
                  <div className="relative">
                    <Icon className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999]" />
                    <input
                      id={id}
                      type={type}
                      required
                      placeholder={placeholder}
                      value={form[key as keyof typeof form]}
                      onChange={(e) => update(key, e.target.value)}
                      className="w-full h-11 pl-10 pr-4 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] focus:ring-2 focus:ring-[#155c32]/10 transition"
                    />
                  </div>
                </div>
              ))}
            </div>

            {/* Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-semibold text-[#1a1a1a] mb-2">Password</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999]" />
                <input
                  id="password"
                  type={showPassword ? "text" : "password"}
                  required
                  placeholder="Min. 8 characters"
                  value={form.password}
                  onChange={(e) => update("password", e.target.value)}
                  className="w-full h-11 pl-10 pr-11 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] focus:ring-2 focus:ring-[#155c32]/10 transition"
                />
                <button type="button" onClick={() => setShowPassword((v) => !v)}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#999] hover:text-[#155c32] transition"
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {/* Confirm Password */}
            <div>
              <label htmlFor="confirmPassword" className="block text-sm font-semibold text-[#1a1a1a] mb-2">Confirm Password</label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999]" />
                <input
                  id="confirmPassword"
                  type={showPassword ? "text" : "password"}
                  required
                  placeholder="Re-enter password"
                  value={form.confirmPassword}
                  onChange={(e) => update("confirmPassword", e.target.value)}
                  className="w-full h-11 pl-10 pr-4 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] focus:ring-2 focus:ring-[#155c32]/10 transition"
                />
              </div>
            </div>

            {/* Terms */}
            <p className="text-xs text-[#888]">
              By creating an account you agree to our{" "}
              <Link href="#terms" className="text-[#155c32] font-semibold hover:underline">Terms &amp; Conditions</Link>{" "}
              and{" "}
              <Link href="#privacy" className="text-[#155c32] font-semibold hover:underline">Privacy Policy</Link>.
            </p>

            {/* Submit */}
            <button
              type="submit"
              className="w-full h-11 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] hover:shadow-lg hover:shadow-[#155c32]/20 transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2"
            >
              Create Account
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>

          <p className="text-center text-sm text-[#888] mt-6">
            Already have an account?{" "}
            <Link href="/login" className="text-[#155c32] font-semibold hover:underline">Sign in</Link>
          </p>
        </div>

        <p className="text-center text-xs text-[#999] mt-6">
          Want to sell fuel?{" "}
          <Link href="/vendor/register" className="text-[#155c32] font-semibold hover:underline">
            Register as a Vendor →
          </Link>
        </p>
      </div>
    </div>
  );
}
