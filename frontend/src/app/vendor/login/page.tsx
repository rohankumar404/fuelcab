"use client";

import { useState } from "react";
import Link from "next/link";
import { Droplet, Mail, Lock, Eye, EyeOff, ArrowRight, ShieldCheck } from "lucide-react";

export default function VendorLoginPage() {
  const [showPassword, setShowPassword] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    console.log("Vendor Login:", { email, password });
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#0d3a1f] via-[#155c32] to-[#0d3a1f] flex items-center justify-center px-4">
      {/* Background glow */}
      <div className="absolute inset-0 pointer-events-none">
        <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-[#33b248]/10 rounded-full blur-3xl" />
        <div className="absolute bottom-1/4 right-1/4 w-64 h-64 bg-[#33b248]/8 rounded-full blur-3xl" />
      </div>

      <div className="relative w-full max-w-md">
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

          {/* Vendor badge */}
          <div className="inline-flex items-center gap-1.5 mt-4 px-3 py-1.5 rounded-full bg-[#33b248]/20 border border-[#33b248]/30">
            <ShieldCheck className="w-3.5 h-3.5 text-[#33b248]" />
            <span className="text-xs font-bold text-[#33b248] uppercase tracking-widest">Vendor Portal</span>
          </div>

          <h1 className="mt-4 text-2xl font-bold text-white">Vendor Sign In</h1>
          <p className="mt-2 text-sm text-gray-400">Access your fuel supply dashboard</p>
        </div>

        {/* Card */}
        <div className="bg-white/5 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/10 p-8">
          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Email */}
            <div>
              <label htmlFor="vendor-email" className="block text-sm font-semibold text-white mb-2">
                Business Email
              </label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  id="vendor-email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="vendor@company.com"
                  className="w-full h-11 pl-10 pr-4 rounded-xl bg-white/8 border border-white/15 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 transition"
                />
              </div>
            </div>

            {/* Password */}
            <div>
              <label htmlFor="vendor-password" className="block text-sm font-semibold text-white mb-2">
                Password
              </label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input
                  id="vendor-password"
                  type={showPassword ? "text" : "password"}
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full h-11 pl-10 pr-11 rounded-xl bg-white/8 border border-white/15 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 transition"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword((v) => !v)}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#33b248] transition"
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {/* Forgot */}
            <div className="text-right">
              <Link href="#forgot" className="text-xs text-[#33b248] font-semibold hover:underline">
                Forgot password?
              </Link>
            </div>

            {/* Submit */}
            <button
              type="submit"
              className="w-full h-11 rounded-xl bg-[#33b248] text-white font-semibold text-sm hover:bg-[#2a9a3d] hover:shadow-lg hover:shadow-[#33b248]/25 transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2"
            >
              Sign In to Portal
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>

          <p className="text-center text-sm text-gray-400 mt-6">
            Not a vendor yet?{" "}
            <Link href="/vendor/register" className="text-[#33b248] font-semibold hover:underline">
              Apply to Join
            </Link>
          </p>
        </div>

        <p className="text-center text-xs text-gray-500 mt-6">
          Customer?{" "}
          <Link href="/login" className="text-[#33b248] font-semibold hover:underline">
            Customer Login →
          </Link>
        </p>
      </div>
    </div>
  );
}
