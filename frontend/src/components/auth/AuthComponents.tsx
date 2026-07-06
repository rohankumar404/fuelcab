"use client";

import Link from "next/link";
import { Droplet } from "lucide-react";

interface AuthLayoutProps {
  children: React.ReactNode;
  title: string;
  subtitle: string;
  maxWidth?: string;
}

export function AuthLogo() {
  return (
    <Link href="/" className="inline-flex items-center gap-2.5 group">
      <div className="w-10 h-10 rounded-xl bg-[#155c32] flex items-center justify-center shadow-lg shadow-[#155c32]/30 group-hover:scale-105 transition-transform duration-200">
        <Droplet className="w-5 h-5 text-[#33b248] fill-[#33b248]" />
      </div>
      <span className="text-2xl font-extrabold tracking-tight text-[#1a1a1a]">
        Fuel<span className="text-[#155c32]">Cab</span>
      </span>
    </Link>
  );
}

export function AuthLayout({ children, title, subtitle, maxWidth = "max-w-md" }: AuthLayoutProps) {
  return (
    <div className="min-h-screen bg-gradient-to-br from-[#f0f9f4] via-white to-[#e8f5ee] flex flex-col">
      {/* Decorative blobs */}
      <div className="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden>
        <div className="absolute -top-32 -left-32 w-96 h-96 bg-[#33b248]/8 rounded-full blur-3xl" />
        <div className="absolute -bottom-32 -right-32 w-96 h-96 bg-[#155c32]/6 rounded-full blur-3xl" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#33b248]/4 rounded-full blur-3xl" />
      </div>

      <div className="relative flex-1 flex items-center justify-center px-4 py-10">
        <div className={`w-full ${maxWidth}`}>
          <div className="text-center mb-8">
            <AuthLogo />
            <h1 className="mt-6 text-2xl sm:text-3xl font-extrabold text-[#1a1a1a] tracking-tight">{title}</h1>
            <p className="mt-2 text-sm text-[#666]">{subtitle}</p>
          </div>
          <div className="bg-white/90 backdrop-blur-sm rounded-3xl shadow-2xl shadow-[#155c32]/10 border border-[#e8f0eb] p-8">
            {children}
          </div>
        </div>
      </div>

      <footer className="relative pb-6 text-center text-xs text-[#aaa]">
        © {new Date().getFullYear()} FuelCab. All rights reserved. ·{" "}
        <Link href="#privacy" className="hover:text-[#155c32] transition">Privacy</Link> ·{" "}
        <Link href="#terms" className="hover:text-[#155c32] transition">Terms</Link>
      </footer>
    </div>
  );
}

// ─── Reusable field components ───────────────────────────────

interface FieldProps {
  id: string;
  label: string;
  error?: string;
  touched?: boolean;
  success?: boolean;
  children: React.ReactNode;
  hint?: string;
}

export function Field({ id, label, error, touched, success, children, hint }: FieldProps) {
  return (
    <div>
      <label htmlFor={id} className="block text-sm font-semibold text-[#1a1a1a] mb-1.5">
        {label}
      </label>
      {children}
      {touched && error && (
        <p className="mt-1.5 flex items-center gap-1 text-xs text-red-500">
          <span className="w-3.5 h-3.5 rounded-full bg-red-500 text-white flex items-center justify-center text-[9px] font-bold flex-shrink-0">!</span>
          {error}
        </p>
      )}
      {touched && !error && success && (
        <p className="mt-1.5 flex items-center gap-1 text-xs text-green-600">
          <span className="w-3.5 h-3.5 rounded-full bg-green-500 text-white flex items-center justify-center text-[9px] font-bold flex-shrink-0">✓</span>
          Looks good!
        </p>
      )}
      {hint && !error && <p className="mt-1 text-xs text-[#aaa]">{hint}</p>}
    </div>
  );
}

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  leftIcon?: React.ReactNode;
  rightNode?: React.ReactNode;
  error?: boolean;
  success?: boolean;
  touched?: boolean;
}

export function AuthInput({ leftIcon, rightNode, error, success, touched, className = "", ...props }: InputProps) {
  const border =
    touched && error   ? "border-red-300 focus:border-red-400 focus:ring-red-100/60"
    : touched && success ? "border-green-400 focus:border-green-500 focus:ring-green-100/60"
    :                      "border-[#e2e8e4] focus:border-[#155c32] focus:ring-[#155c32]/10";

  return (
    <div className={`relative flex items-center rounded-xl border bg-[#f9fbfa] overflow-hidden transition-all duration-150 focus-within:ring-2 ${border}`}>
      {leftIcon && (
        <span className="pl-3.5 flex-shrink-0 text-[#aaa]">{leftIcon}</span>
      )}
      <input
        {...props}
        className={`flex-1 h-11 px-3 bg-transparent text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none ${leftIcon ? "" : "pl-4"} ${rightNode ? "" : "pr-4"} ${className}`}
      />
      {rightNode && <span className="pr-3 flex-shrink-0">{rightNode}</span>}
    </div>
  );
}

// ─── Password Strength Meter ─────────────────────────────────

interface StrengthMeterProps {
  score: 0 | 1 | 2 | 3 | 4;
  label: string;
  color: string;
  suggestions: string[];
  visible: boolean;
}

export function PasswordStrengthMeter({ score, label, color, suggestions, visible }: StrengthMeterProps) {
  if (!visible) return null;
  const segments = [0, 1, 2, 3];
  return (
    <div className="mt-2 space-y-2">
      <div className="flex gap-1.5">
        {segments.map((s) => (
          <div
            key={s}
            className="h-1.5 flex-1 rounded-full transition-all duration-300"
            style={{ backgroundColor: s < score ? color : "#e2e8e4" }}
          />
        ))}
      </div>
      <div className="flex justify-between items-center">
        <span className="text-xs font-semibold" style={{ color }}>{label}</span>
        {suggestions.length > 0 && (
          <span className="text-[10px] text-[#aaa] text-right max-w-[60%]">{suggestions[0]}</span>
        )}
      </div>
    </div>
  );
}

// ─── Submit button ───────────────────────────────────────────

interface SubmitBtnProps {
  loading?: boolean;
  children: React.ReactNode;
  disabled?: boolean;
  onClick?: () => void;
  type?: "submit" | "button";
  variant?: "primary" | "dark";
}

export function SubmitBtn({ loading, children, disabled, onClick, type = "submit", variant = "primary" }: SubmitBtnProps) {
  const base = variant === "dark"
    ? "bg-[#0d3a1f] hover:bg-black hover:shadow-[#0d3a1f]/30"
    : "bg-[#155c32] hover:bg-[#0d3a1f] hover:shadow-[#155c32]/30";

  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled || loading}
      className={`w-full h-12 rounded-xl ${base} text-white font-bold text-sm hover:shadow-xl transition-all duration-200 hover:-translate-y-px flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed disabled:translate-y-0`}
    >
      {loading ? (
        <>
          <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
          Processing…
        </>
      ) : children}
    </button>
  );
}

// ─── Divider ────────────────────────────────────────────────

export function Divider({ label }: { label: string }) {
  return (
    <div className="relative my-5">
      <div className="absolute inset-0 flex items-center">
        <div className="w-full border-t border-[#e8ecea]" />
      </div>
      <div className="relative flex justify-center">
        <span className="px-3 bg-white text-xs text-[#aaa] font-medium">{label}</span>
      </div>
    </div>
  );
}

// ─── Alert ──────────────────────────────────────────────────

interface AlertProps {
  type: "error" | "success" | "info" | "warning";
  message: string;
}

export function Alert({ type, message }: AlertProps) {
  const styles = {
    error:   "bg-red-50 border-red-200 text-red-700",
    success: "bg-green-50 border-green-200 text-green-700",
    info:    "bg-blue-50 border-blue-200 text-blue-700",
    warning: "bg-amber-50 border-amber-200 text-amber-700",
  };
  const icons = { error: "✕", success: "✓", info: "ℹ", warning: "⚠" };

  return (
    <div className={`flex items-start gap-2.5 p-3.5 rounded-xl border text-sm ${styles[type]}`}>
      <span className="w-5 h-5 rounded-full border-current border flex items-center justify-center text-[11px] font-bold flex-shrink-0 mt-px">
        {icons[type]}
      </span>
      {message}
    </div>
  );
}

// ─── OTP Input ──────────────────────────────────────────────

interface OtpInputProps {
  value: string;
  onChange: (val: string) => void;
  error?: string;
  touched?: boolean;
}

export function OtpInput({ value, onChange, error, touched }: OtpInputProps) {
  const digits = value.split("").concat(Array(6).fill("")).slice(0, 6);

  const handleKey = (e: React.KeyboardEvent<HTMLInputElement>) => {
    const key = e.key;
    if (key === "Backspace") {
      onChange(value.slice(0, -1));
    } else if (/^\d$/.test(key) && value.length < 6) {
      onChange(value + key);
    }
  };

  const handlePaste = (e: React.ClipboardEvent) => {
    const pasted = e.clipboardData.getData("text").replace(/\D/g, "").slice(0, 6);
    onChange(pasted);
    e.preventDefault();
  };

  return (
    <div>
      <div className="flex gap-2 justify-center">
        {digits.map((d, i) => (
          <div
            key={i}
            className={`w-12 h-14 rounded-xl border-2 flex items-center justify-center text-xl font-bold text-[#1a1a1a] transition-all duration-150 ${
              i === value.length
                ? "border-[#155c32] bg-[#155c32]/5 ring-2 ring-[#155c32]/20"
                : d
                  ? (touched && error ? "border-red-300 bg-red-50" : "border-[#155c32]/40 bg-[#f4f8f5]")
                  : "border-[#e2e8e4] bg-[#f9fbfa]"
            }`}
          >
            {d || (i === value.length ? <span className="w-0.5 h-5 bg-[#155c32] animate-pulse rounded" /> : "")}
          </div>
        ))}
      </div>
      {/* Hidden real input for keyboard */}
      <input
        type="number"
        inputMode="numeric"
        pattern="\d*"
        value={value}
        onChange={(e) => onChange(e.target.value.replace(/\D/g, "").slice(0, 6))}
        onKeyDown={handleKey}
        onPaste={handlePaste}
        className="sr-only"
        aria-label="OTP input"
        autoFocus
        autoComplete="one-time-code"
      />
      {touched && error && (
        <p className="mt-2 text-center text-xs text-red-500">{error}</p>
      )}
    </div>
  );
}
