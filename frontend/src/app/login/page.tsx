"use client";

import { useState, useEffect, useCallback } from "react";
import Link from "next/link";
import { Mail, Lock, Eye, EyeOff, ArrowRight, Phone, AlertTriangle } from "lucide-react";
import {
  AuthLayout, Field, AuthInput, SubmitBtn, Divider, Alert,
} from "@/components/auth/AuthComponents";
import {
  validateEmail, validatePhone, validatePassword,
} from "@/lib/auth-validation";

type LoginMode = "email" | "phone";

const MAX_ATTEMPTS = 5;
const LOCKOUT_SECONDS = 300; // 5 minutes

export default function LoginPage() {
  // ── Mode
  const [mode, setMode] = useState<LoginMode>("email");

  // ── Fields
  const [email, setEmail]       = useState("");
  const [phone, setPhone]       = useState("");
  const [password, setPassword] = useState("");
  const [showPwd, setShowPwd]   = useState(false);
  const [remember, setRemember] = useState(false);

  // ── Touched (for showing validation errors on blur)
  const [touched, setTouched] = useState({ email: false, phone: false, password: false });

  // ── UI state
  const [loading, setLoading]       = useState(false);
  const [apiError, setApiError]     = useState("");
  const [attempts, setAttempts]     = useState(0);
  const [lockedUntil, setLockedUntil] = useState<number | null>(null);
  const [lockCountdown, setLockCountdown] = useState(0);

  // ── Restore remember + persisted lockout from localStorage
  useEffect(() => {
    try {
      const saved = localStorage.getItem("fc_remember_email");
      if (saved) { setEmail(saved); setRemember(true); }

      // Restore lockout if it was set before page refresh
      const lockStr = localStorage.getItem("fc_lock_until");
      const attStr  = localStorage.getItem("fc_lock_attempts");
      if (lockStr) {
        const lockTs = parseInt(lockStr, 10);
        if (lockTs > Date.now()) {
          setLockedUntil(lockTs);
          setAttempts(parseInt(attStr ?? "0", 10));
        } else {
          // Expired — clear
          localStorage.removeItem("fc_lock_until");
          localStorage.removeItem("fc_lock_attempts");
        }
      }
    } catch { /* ignore */ }
  }, []);

  // ── Persist lockout to localStorage whenever it changes
  useEffect(() => {
    try {
      if (lockedUntil) {
        localStorage.setItem("fc_lock_until", String(lockedUntil));
        localStorage.setItem("fc_lock_attempts", String(attempts));
      } else {
        localStorage.removeItem("fc_lock_until");
        localStorage.removeItem("fc_lock_attempts");
      }
    } catch { /* ignore */ }
  }, [lockedUntil, attempts]);

  // ── Lockout countdown
  useEffect(() => {
    if (!lockedUntil) return;
    const tick = () => {
      const remaining = Math.ceil((lockedUntil - Date.now()) / 1000);
      if (remaining <= 0) {
        setLockedUntil(null);
        setAttempts(0);
        setLockCountdown(0);
        localStorage.removeItem("fc_lock_until");
        localStorage.removeItem("fc_lock_attempts");
        return;
      }
      setLockCountdown(remaining);
    };
    tick();
    const id = setInterval(tick, 1000);
    return () => clearInterval(id);
  }, [lockedUntil]);

  // ── Manual lockout clear (dev / support helper)
  const clearLockout = () => {
    setLockedUntil(null);
    setAttempts(0);
    setLockCountdown(0);
    setApiError("");
    try {
      localStorage.removeItem("fc_lock_until");
      localStorage.removeItem("fc_lock_attempts");
    } catch { /* ignore */ }
  };

  // ── Validation
  const emailV    = validateEmail(email);
  const phoneV    = validatePhone(phone);
  const passwordV = validatePassword(password);

  const isLocked = lockedUntil !== null && Date.now() < lockedUntil;

  const canSubmit = !isLocked && (
    mode === "email"
      ? emailV.valid && passwordV.valid
      : phoneV.valid && passwordV.valid
  );

  const handleBlur = (field: keyof typeof touched) =>
    setTouched((t) => ({ ...t, [field]: true }));

  const handleSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setTouched({ email: true, phone: true, password: true });
    if (!canSubmit) return;

    setLoading(true);
    setApiError("");

    const API_BASE = (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8002").replace(/\/$/, "");

    try {
      const body: Record<string, string> = { password };
      if (mode === "email") body.email = email;
      else body.phone = phone;

      const res = await fetch(`${API_BASE}/api/v1/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json", "Accept": "application/json" },
        body: JSON.stringify(body),
      });

      const data = await res.json().catch(() => ({}));

      if (res.ok && data?.data?.access_token) {
        // ── SUCCESS ──
        if (remember) localStorage.setItem("fc_remember_email", email);
        else          localStorage.removeItem("fc_remember_email");

        // Store token for subsequent API calls
        localStorage.setItem("fc_token", data.data.access_token);
        localStorage.setItem("fc_user", JSON.stringify(data.data.user ?? {}));

        // Redirect to customer dashboard
        window.location.href = "/dashboard";
        return;
      }

      // ── FAILURE ──
      const newAttempts = attempts + 1;
      setAttempts(newAttempts);
      if (newAttempts >= MAX_ATTEMPTS) {
        setLockedUntil(Date.now() + LOCKOUT_SECONDS * 1000);
        setApiError(`Too many failed attempts. Account locked for ${LOCKOUT_SECONDS / 60} minutes.`);
      } else {
        const msg = data?.message ?? "Invalid credentials.";
        setApiError(`${msg} ${MAX_ATTEMPTS - newAttempts} attempt(s) remaining before lockout.`);
      }
    } catch {
      // Network error — do NOT increment the attempt counter
      setApiError("Could not connect to server. Please check your connection and try again.");
    } finally {
      setLoading(false);
    }
  }, [canSubmit, attempts, remember, email, phone, password, mode]);

  const formatTime = (s: number) => `${Math.floor(s / 60)}:${String(s % 60).padStart(2, "0")}`;

  return (
    <AuthLayout title="Welcome back" subtitle="Sign in to your FuelCab business account">
      {/* ── Mode Toggle ── */}
      <div className="flex rounded-xl border border-[#e2e8e4] overflow-hidden mb-6">
        {(["email", "phone"] as LoginMode[]).map((m) => (
          <button
            key={m} type="button"
            onClick={() => { setMode(m); setApiError(""); setTouched({ email: false, phone: false, password: false }); }}
            className={`flex-1 py-2.5 text-sm font-semibold transition-all duration-150 ${
              mode === m ? "bg-[#155c32] text-white" : "text-[#777] hover:bg-[#f4f8f5]"
            }`}
          >
            {m === "email" ? "📧 Email" : "📱 Phone"}
          </button>
        ))}
      </div>

      {/* ── Lockout alert ── */}
      {isLocked && (
        <div className="mb-5 rounded-xl border border-red-200 bg-red-50 p-4">
          <div className="flex items-start justify-between gap-3">
            <p className="text-sm text-red-700 font-medium">
              🔒 Account temporarily locked. Try again in{" "}
              <span className="font-bold tabular-nums">{formatTime(lockCountdown)}</span>.
            </p>
            <button
              type="button"
              onClick={clearLockout}
              className="shrink-0 text-xs text-red-600 underline hover:text-red-800 font-semibold transition"
            >
              Reset
            </button>
          </div>
        </div>
      )}

      {/* ── API Error ── */}
      {apiError && !isLocked && (
        <div className="mb-5">
          <Alert type="error" message={apiError} />
        </div>
      )}


      <form onSubmit={handleSubmit} className="space-y-4" noValidate>
        {/* Email OR Phone */}
        {mode === "email" ? (
          <Field id="login-email" label="Email Address"
            error={emailV.message} touched={touched.email} success={emailV.valid}>
            <AuthInput
              id="login-email" type="email" autoComplete="email"
              placeholder="you@company.com"
              value={email} onChange={(e) => setEmail(e.target.value)}
              onBlur={() => handleBlur("email")}
              leftIcon={<Mail className="w-4 h-4" />}
              error={!emailV.valid} success={emailV.valid} touched={touched.email}
            />
          </Field>
        ) : (
          <Field id="login-phone" label="Mobile Number"
            error={phoneV.message} touched={touched.phone} success={phoneV.valid}
            hint="10-digit Indian mobile number">
            <div className="flex gap-2">
              <div className="flex items-center h-11 px-3 rounded-xl border border-[#e2e8e4] bg-[#f9fbfa] text-sm text-[#555] font-semibold flex-shrink-0">
                🇮🇳 +91
              </div>
              <AuthInput
                id="login-phone" type="tel" autoComplete="tel"
                placeholder="98765 43210" maxLength={10}
                value={phone} onChange={(e) => setPhone(e.target.value.replace(/\D/g, ""))}
                onBlur={() => handleBlur("phone")}
                leftIcon={<Phone className="w-4 h-4" />}
                error={!phoneV.valid} success={phoneV.valid} touched={touched.phone}
              />
            </div>
          </Field>
        )}

        {/* Password */}
        <Field id="login-password" label="Password"
          error={passwordV.message} touched={touched.password} success={passwordV.valid}>
          <AuthInput
            id="login-password" type={showPwd ? "text" : "password"}
            autoComplete="current-password" placeholder="Enter your password"
            value={password} onChange={(e) => setPassword(e.target.value)}
            onBlur={() => handleBlur("password")}
            leftIcon={<Lock className="w-4 h-4" />}
            rightNode={
              <button type="button" onClick={() => setShowPwd((v) => !v)}
                className="text-[#aaa] hover:text-[#155c32] transition" tabIndex={-1}>
                {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
              </button>
            }
            error={!passwordV.valid} success={passwordV.valid} touched={touched.password}
          />
        </Field>

        {/* Remember me + Forgot */}
        <div className="flex items-center justify-between">
          <label className="flex items-center gap-2 cursor-pointer group">
            <div
              onClick={() => setRemember((v) => !v)}
              className={`w-4.5 h-4.5 rounded-md border-2 flex items-center justify-center transition-all duration-150 cursor-pointer ${
                remember ? "bg-[#155c32] border-[#155c32]" : "border-[#d0d8d4] group-hover:border-[#155c32]"
              }`}
            >
              {remember && <span className="text-white text-[10px] font-bold">✓</span>}
            </div>
            <span className="text-sm text-[#555]">Remember me</span>
          </label>
          <Link href="/forgot-password" className="text-sm text-[#155c32] font-semibold hover:underline">
            Forgot password?
          </Link>
        </div>

        {/* Attempts warning */}
        {attempts > 0 && !isLocked && (
          <div className="flex items-center gap-2 text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-xl p-3">
            <AlertTriangle className="w-3.5 h-3.5 flex-shrink-0" />
            {MAX_ATTEMPTS - attempts} login attempt(s) remaining before temporary lockout
          </div>
        )}

        <SubmitBtn loading={loading} disabled={isLocked}>
          Sign In <ArrowRight className="w-4 h-4" />
        </SubmitBtn>
      </form>

      <Divider label="Don't have an account?" />

      <Link href="/register"
        className="w-full h-11 rounded-xl border-2 border-[#e2e8e4] text-[#1a1a1a] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition-all duration-200 flex items-center justify-center gap-2">
        Create a Business Account
      </Link>

      <p className="text-center text-xs text-[#aaa] mt-5">
        Are you a fuel vendor?{" "}
        <Link href="/vendor/login" className="text-[#155c32] font-semibold hover:underline">Vendor Portal →</Link>
      </p>
    </AuthLayout>
  );
}
