"use client";

import { useState } from "react";
import Link from "next/link";
import { Droplet, Mail, Lock, Eye, EyeOff, ArrowRight } from "lucide-react";

export default function LoginPage() {
  const [showPassword, setShowPassword] = useState(false);
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // TODO: Connect to Laravel Sanctum API
    console.log("Login:", { email, password });
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#f4f8f5] via-white to-[#e8f4ec] flex items-center justify-center px-4">
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-8">
          <Link href="/" className="inline-flex items-center gap-2.5 group">
            <div className="w-10 h-10 rounded-xl bg-[#155c32] flex items-center justify-center">
              <Droplet className="w-5 h-5 text-[#33b248] fill-[#33b248]" />
            </div>
            <span className="text-2xl font-extrabold tracking-tight text-[#1a1a1a]">
              Fuel<span className="text-[#155c32]">Cab</span>
            </span>
          </Link>
          <h1 className="mt-6 text-2xl font-bold text-[#1a1a1a]">Welcome back</h1>
          <p className="mt-2 text-sm text-[#555555]">Sign in to your FuelCab account</p>
        </div>

        {/* Card */}
        <div className="bg-white rounded-2xl shadow-xl shadow-[#155c32]/8 border border-[#e7ece8] p-8">
          <form onSubmit={handleSubmit} className="space-y-5">
            {/* Email */}
            <div>
              <label htmlFor="email" className="block text-sm font-semibold text-[#1a1a1a] mb-2">
                Email Address
              </label>
              <div className="relative">
                <Mail className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999]" />
                <input
                  id="email"
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="you@company.com"
                  className="w-full h-11 pl-10 pr-4 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] focus:ring-2 focus:ring-[#155c32]/10 transition"
                />
              </div>
            </div>

            {/* Password */}
            <div>
              <label htmlFor="password" className="block text-sm font-semibold text-[#1a1a1a] mb-2">
                Password
              </label>
              <div className="relative">
                <Lock className="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#999]" />
                <input
                  id="password"
                  type={showPassword ? "text" : "password"}
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="w-full h-11 pl-10 pr-11 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] focus:ring-2 focus:ring-[#155c32]/10 transition"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword((v) => !v)}
                  className="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#999] hover:text-[#155c32] transition"
                  aria-label={showPassword ? "Hide password" : "Show password"}
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
            </div>

            {/* Forgot */}
            <div className="text-right">
              <Link href="#forgot-password" className="text-xs text-[#155c32] font-semibold hover:underline">
                Forgot password?
              </Link>
            </div>

            {/* Submit */}
            <button
              type="submit"
              className="w-full h-11 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] hover:shadow-lg hover:shadow-[#155c32]/20 transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2"
            >
              Sign In
              <ArrowRight className="w-4 h-4" />
            </button>
          </form>

          {/* Divider */}
          <div className="relative my-6">
            <div className="absolute inset-0 flex items-center">
              <div className="w-full border-t border-[#e7ece8]" />
            </div>
            <div className="relative flex justify-center text-xs text-[#999] bg-white px-3">
              New to FuelCab?
            </div>
          </div>

          <Link
            href="/register"
            className="w-full h-11 rounded-xl border border-[#e7ece8] text-[#1a1a1a] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition-all duration-200 flex items-center justify-center"
          >
            Create an account
          </Link>
        </div>

        <p className="text-center text-xs text-[#999] mt-6">
          Are you a vendor?{" "}
          <Link href="/vendor/login" className="text-[#155c32] font-semibold hover:underline">
            Vendor Login →
          </Link>
        </p>
      </div>
    </div>
  );
}
