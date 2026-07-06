"use client";

import { useState, useCallback } from "react";
import Link from "next/link";
import { Mail, Lock, Eye, EyeOff, ArrowRight, AlertTriangle, ShieldCheck } from "lucide-react";
import {
  AuthLayout, Field, AuthInput, SubmitBtn, Divider, Alert,
} from "@/components/auth/AuthComponents";
import { validateEmail, validatePassword } from "@/lib/auth-validation";

const MAX_ATTEMPTS = 5;
const LOCKOUT_SECONDS = 300;

export default function VendorLoginPage() {
  const [email, setEmail]       = useState("");
  const [password, setPassword] = useState("");
  const [showPwd, setShowPwd]   = useState(false);
  const [touched, setTouched]   = useState({ email: false, password: false });

  const [loading, setLoading]     = useState(false);
  const [apiError, setApiError]   = useState("");
  const [attempts, setAttempts]   = useState(0);
  const [lockedUntil, setLockedUntil] = useState<number | null>(null);
  const [lockSeconds, setLockSeconds] = useState(0);

  const emailV    = validateEmail(email);
  const passwordV = validatePassword(password);
  const isLocked  = lockedUntil !== null && Date.now() < lockedUntil;

  const handleBlur = (f: keyof typeof touched) => setTouched((t) => ({ ...t, [f]: true }));

  const startLockTimer = (until: number) => {
    const tick = () => {
      const s = Math.ceil((until - Date.now()) / 1000);
      if (s <= 0) { setLockedUntil(null); setAttempts(0); setLockSeconds(0); return; }
      setLockSeconds(s);
    };
    tick();
    const id = setInterval(() => {
      const s = Math.ceil((until - Date.now()) / 1000);
      if (s <= 0) { clearInterval(id); setLockedUntil(null); setAttempts(0); setLockSeconds(0); return; }
      setLockSeconds(s);
    }, 1000);
  };

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setTouched({ email: true, password: true });
    if (!emailV.valid || !passwordV.valid || isLocked) return;

    setLoading(true);
    setApiError("");
    try {
      await new Promise((r) => setTimeout(r, 1200));
      const simulateFail = attempts < 2;
      if (simulateFail) {
        const next = attempts + 1;
        setAttempts(next);
        if (next >= MAX_ATTEMPTS) {
          const until = Date.now() + LOCKOUT_SECONDS * 1000;
          setLockedUntil(until);
          startLockTimer(until);
          setApiError("Too many failed attempts. Account locked for 5 minutes.");
        } else {
          setApiError(`Invalid credentials. ${MAX_ATTEMPTS - next} attempt(s) remaining.`);
        }
        return;
      }
      window.location.href = "/vendor/dashboard";
    } catch {
      setApiError("Something went wrong. Try again.");
    } finally {
      setLoading(false);
    }
  }, [emailV, passwordV, isLocked, attempts]);

  const fmt = (s: number) => `${Math.floor(s / 60)}:${String(s % 60).padStart(2, "0")}`;

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#0b2e1a] via-[#0d3a1f] to-[#0b2e1a] flex flex-col">
      {/* Decorative blobs */}
      <div className="fixed inset-0 pointer-events-none">
        <div className="absolute top-0 left-0 w-96 h-96 bg-[#33b248]/8 rounded-full blur-3xl" />
        <div className="absolute bottom-0 right-0 w-64 h-64 bg-[#33b248]/6 rounded-full blur-3xl" />
      </div>

      <div className="relative flex-1 flex items-center justify-center px-4 py-10">
        <div className="w-full max-w-md">
          {/* Logo */}
          <div className="text-center mb-8">
            <Link href="/" className="inline-flex items-center gap-2.5">
              <div className="w-10 h-10 rounded-xl bg-[#33b248] flex items-center justify-center shadow-lg">
                <ShieldCheck className="w-5 h-5 text-white" />
              </div>
              <span className="text-2xl font-extrabold text-white tracking-tight">
                Fuel<span className="text-[#33b248]">Cab</span>
              </span>
            </Link>
            <div className="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-full bg-[#33b248]/15 border border-[#33b248]/30">
              <span className="w-1.5 h-1.5 rounded-full bg-[#33b248] animate-pulse" />
              <span className="text-xs font-bold text-[#33b248] uppercase tracking-widest">Vendor Portal</span>
            </div>
            <h1 className="mt-4 text-2xl font-extrabold text-white">Supplier Sign In</h1>
            <p className="mt-1 text-sm text-gray-400">Access your fuel supply dashboard</p>
          </div>

          <div className="bg-white/5 backdrop-blur-xl rounded-3xl border border-white/10 shadow-2xl p-8">
            {isLocked && (
              <div className="mb-4">
                <Alert type="error" message={`Locked. Try again in ${fmt(lockSeconds)}`} />
              </div>
            )}
            {apiError && !isLocked && (
              <div className="mb-4">
                <Alert type="error" message={apiError} />
              </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-4" noValidate>
              <div>
                <label className="block text-sm font-semibold text-white mb-1.5">Business Email</label>
                <div className={`relative flex items-center rounded-xl border overflow-hidden transition focus-within:ring-2 ${
                  touched.email && !emailV.valid
                    ? "border-red-400 focus-within:ring-red-400/20"
                    : touched.email && emailV.valid
                      ? "border-[#33b248] focus-within:ring-[#33b248]/20"
                      : "border-white/15 focus-within:border-[#33b248] focus-within:ring-[#33b248]/20"
                } bg-white/8`}>
                  <span className="pl-3.5 text-gray-400 flex-shrink-0"><Mail className="w-4 h-4" /></span>
                  <input type="email" autoComplete="email" placeholder="vendor@company.com"
                    value={email} onChange={(e) => setEmail(e.target.value)}
                    onBlur={() => handleBlur("email")}
                    className="flex-1 h-11 px-3 bg-transparent text-sm text-white placeholder-gray-500 focus:outline-none"
                  />
                </div>
                {touched.email && !emailV.valid && (
                  <p className="mt-1.5 text-xs text-red-400">{emailV.message}</p>
                )}
              </div>

              <div>
                <label className="block text-sm font-semibold text-white mb-1.5">Password</label>
                <div className={`relative flex items-center rounded-xl border overflow-hidden transition focus-within:ring-2 ${
                  touched.password && !passwordV.valid
                    ? "border-red-400 focus-within:ring-red-400/20"
                    : "border-white/15 focus-within:border-[#33b248] focus-within:ring-[#33b248]/20"
                } bg-white/8`}>
                  <span className="pl-3.5 text-gray-400 flex-shrink-0"><Lock className="w-4 h-4" /></span>
                  <input type={showPwd ? "text" : "password"} autoComplete="current-password" placeholder="••••••••"
                    value={password} onChange={(e) => setPassword(e.target.value)}
                    onBlur={() => handleBlur("password")}
                    className="flex-1 h-11 px-3 bg-transparent text-sm text-white placeholder-gray-500 focus:outline-none"
                  />
                  <button type="button" onClick={() => setShowPwd((v) => !v)} tabIndex={-1}
                    className="pr-3.5 text-gray-400 hover:text-[#33b248] transition flex-shrink-0">
                    {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                  </button>
                </div>
                {touched.password && !passwordV.valid && (
                  <p className="mt-1.5 text-xs text-red-400">{passwordV.message}</p>
                )}
              </div>

              <div className="flex justify-between items-center">
                <span className="text-xs text-gray-400" />
                <Link href="/forgot-password" className="text-xs text-[#33b248] font-semibold hover:underline">
                  Forgot password?
                </Link>
              </div>

              {attempts > 0 && !isLocked && (
                <div className="flex items-center gap-2 text-xs text-amber-400 bg-amber-400/10 border border-amber-400/20 rounded-xl p-3">
                  <AlertTriangle className="w-3.5 h-3.5 flex-shrink-0" />
                  {MAX_ATTEMPTS - attempts} attempt(s) remaining before lockout
                </div>
              )}

              <button type="submit" disabled={loading || isLocked}
                className="w-full h-12 rounded-xl bg-[#33b248] text-white font-bold text-sm hover:bg-[#2a9a3d] hover:shadow-xl hover:shadow-[#33b248]/25 transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:translate-y-0">
                {loading
                  ? <><div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" /> Processing…</>
                  : <>Sign In to Portal <ArrowRight className="w-4 h-4" /></>
                }
              </button>
            </form>

            <div className="relative my-5">
              <div className="absolute inset-0 flex items-center"><div className="w-full border-t border-white/10" /></div>
              <div className="relative flex justify-center"><span className="px-3 bg-transparent text-xs text-gray-500">Not a vendor yet?</span></div>
            </div>

            <Link href="/vendor/register"
              className="w-full h-11 rounded-xl border border-white/15 text-gray-300 font-semibold text-sm hover:border-[#33b248] hover:text-[#33b248] transition-all duration-200 flex items-center justify-center gap-2">
              Apply to Become a Vendor
            </Link>
          </div>

          <p className="text-center text-xs text-gray-500 mt-5">
            Customer?{" "}
            <Link href="/login" className="text-[#33b248] font-semibold hover:underline">Customer Login →</Link>
          </p>
        </div>
      </div>

      <footer className="relative pb-6 text-center text-xs text-gray-600">
        © {new Date().getFullYear()} FuelCab · <Link href="#privacy" className="hover:text-[#33b248] transition">Privacy</Link>
      </footer>
    </div>
  );
}
