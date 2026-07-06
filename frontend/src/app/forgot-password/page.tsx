"use client";

import { useState, useCallback } from "react";
import Link from "next/link";
import { Mail, Lock, Eye, EyeOff, ArrowRight, Phone } from "lucide-react";
import {
  AuthLayout, Field, AuthInput, SubmitBtn, Divider, Alert, OtpInput,
} from "@/components/auth/AuthComponents";
import {
  validateEmail, validatePhone, validatePassword, validateOtp,
} from "@/lib/auth-validation";
import { sendOtp, verifyOtp } from "@/lib/authkey";

type FPStep = "input" | "otp" | "reset" | "done";
type FPMode = "email" | "phone";

const RESEND_COOLDOWN = 30;

export default function ForgotPasswordPage() {
  const [step, setStep] = useState<FPStep>("input");
  const [mode, setMode] = useState<FPMode>("email");

  // Step 1
  const [email, setEmail]       = useState("");
  const [phone, setPhone]       = useState("");
  const [t1, setT1]             = useState({ email: false, phone: false });

  // Step 2 — OTP
  const [otp, setOtp]           = useState("");
  const [otpTouched, setOtpTouched] = useState(false);
  const [logId, setLogId]       = useState("");
  const [resendTimer, setResendTimer] = useState(0);

  // Step 3 — Reset
  const [newPwd, setNewPwd]     = useState("");
  const [confirmPwd, setConfirmPwd] = useState("");
  const [showPwd, setShowPwd]   = useState(false);
  const [t3, setT3]             = useState({ pwd: false, confirm: false });

  const [loading, setLoading]   = useState(false);
  const [apiError, setApiError] = useState("");
  const [otpError, setOtpError] = useState("");

  const emailV    = validateEmail(email);
  const phoneV    = validatePhone(phone);
  const otpV      = validateOtp(otp);
  const newPwdV   = validatePassword(newPwd);
  const confirmV  = { valid: newPwd === confirmPwd && !!confirmPwd, message: "Passwords do not match" };

  const startTimer = () => {
    setResendTimer(RESEND_COOLDOWN);
    const id = setInterval(() => setResendTimer((v) => { if (v <= 1) { clearInterval(id); return 0; } return v - 1; }), 1000);
  };

  // Step 1 — Send Reset Link / OTP
  const handleStep1 = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setT1({ email: true, phone: true });
    if (mode === "email" && !emailV.valid) return;
    if (mode === "phone" && !phoneV.valid) return;

    setLoading(true);
    setApiError("");
    try {
      if (mode === "phone") {
        const res = await sendOtp(phone);
        if (!res.success) { setApiError(res.error ?? "Failed to send OTP"); return; }
        setLogId(res.logId ?? "");
        startTimer();
      } else {
        // TODO: POST /api/v1/auth/forgot-password { email }
        await new Promise((r) => setTimeout(r, 1000));
      }
      setStep("otp");
    } finally {
      setLoading(false);
    }
  }, [mode, emailV, phoneV, email, phone]);

  // Step 2 — Verify OTP
  const handleVerify = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setOtpTouched(true);
    if (!otpV.valid) return;
    setLoading(true);
    setOtpError("");
    try {
      if (mode === "phone") {
        const res = await verifyOtp(otp, logId);
        if (!res.success) { setOtpError(res.error ?? "Invalid OTP"); return; }
      } else {
        // TODO: verify email OTP via /api/v1/auth/verify-reset-otp
        await new Promise((r) => setTimeout(r, 800));
      }
      setStep("reset");
    } finally {
      setLoading(false);
    }
  }, [otpV, otp, logId, mode]);

  // Step 3 — Set new password
  const handleReset = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setT3({ pwd: true, confirm: true });
    if (!newPwdV.valid || !confirmV.valid) return;
    setLoading(true);
    try {
      // TODO: POST /api/v1/auth/reset-password { token/otp, password, password_confirmation }
      await new Promise((r) => setTimeout(r, 1200));
      setStep("done");
    } finally {
      setLoading(false);
    }
  }, [newPwdV, confirmV]);

  const handleResend = async () => {
    if (resendTimer > 0) return;
    setLoading(true);
    const res = await sendOtp(phone);
    if (res.success) { setLogId(res.logId ?? ""); setOtp(""); startTimer(); }
    else setOtpError(res.error ?? "Failed to resend");
    setLoading(false);
  };

  // Done screen
  if (step === "done") {
    return (
      <AuthLayout title="Password Reset!" subtitle="You can now sign in with your new password">
        <div className="text-center space-y-5">
          <div className="w-16 h-16 rounded-2xl bg-[#155c32]/10 flex items-center justify-center mx-auto text-3xl">🔐</div>
          <p className="text-sm text-[#555]">Your password has been reset successfully.</p>
          <Link href="/login"
            className="w-full h-12 rounded-xl bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] transition flex items-center justify-center gap-2">
            Sign In <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      </AuthLayout>
    );
  }

  return (
    <AuthLayout title="Forgot Password?" subtitle="We'll help you reset it in 3 quick steps">
      {step === "input" && (
        <form onSubmit={handleStep1} className="space-y-4" noValidate>
          {apiError && <Alert type="error" message={apiError} />}

          {/* Mode toggle */}
          <div className="flex rounded-xl border border-[#e2e8e4] overflow-hidden mb-2">
            {(["email", "phone"] as FPMode[]).map((m) => (
              <button key={m} type="button"
                onClick={() => { setMode(m); setApiError(""); setT1({ email: false, phone: false }); }}
                className={`flex-1 py-2.5 text-sm font-semibold transition ${mode === m ? "bg-[#155c32] text-white" : "text-[#777] hover:bg-[#f4f8f5]"}`}>
                {m === "email" ? "📧 Email" : "📱 Phone"}
              </button>
            ))}
          </div>

          {mode === "email" ? (
            <Field id="fp-email" label="Registered Email *"
              error={emailV.message} touched={t1.email} success={emailV.valid}>
              <AuthInput id="fp-email" type="email" autoComplete="email"
                placeholder="you@company.com"
                value={email} onChange={(e) => setEmail(e.target.value)}
                onBlur={() => setT1((t) => ({ ...t, email: true }))}
                leftIcon={<Mail className="w-4 h-4" />}
                error={!emailV.valid} success={emailV.valid} touched={t1.email}
              />
            </Field>
          ) : (
            <Field id="fp-phone" label="Registered Mobile *"
              error={phoneV.message} touched={t1.phone} success={phoneV.valid}>
              <div className="flex gap-2">
                <div className="flex items-center h-11 px-3 rounded-xl border border-[#e2e8e4] bg-[#f9fbfa] text-sm text-[#555] font-semibold flex-shrink-0">
                  🇮🇳 +91
                </div>
                <AuthInput id="fp-phone" type="tel" placeholder="98765 43210" maxLength={10}
                  value={phone} onChange={(e) => setPhone(e.target.value.replace(/\D/g, ""))}
                  onBlur={() => setT1((t) => ({ ...t, phone: true }))}
                  leftIcon={<Phone className="w-4 h-4" />}
                  error={!phoneV.valid} success={phoneV.valid} touched={t1.phone}
                />
              </div>
            </Field>
          )}

          <SubmitBtn loading={loading}>
            {mode === "email" ? "Send Reset Link" : "Send OTP"} <ArrowRight className="w-4 h-4" />
          </SubmitBtn>
          <p className="text-center text-sm">
            <Link href="/login" className="text-[#155c32] font-semibold hover:underline">← Back to Sign In</Link>
          </p>
        </form>
      )}

      {step === "otp" && (
        <form onSubmit={handleVerify} className="space-y-5" noValidate>
          <div className="text-center">
            <div className="w-14 h-14 rounded-2xl bg-[#155c32]/10 flex items-center justify-center mx-auto mb-3 text-2xl">
              {mode === "phone" ? "📱" : "📧"}
            </div>
            <p className="text-sm text-[#555]">
              {mode === "phone"
                ? `OTP sent to +91 ${phone}`
                : `Check your inbox at ${email}`}
            </p>
            <p className="text-xs text-[#aaa] mt-1">
              {mode === "phone" ? "Valid for 5 minutes · via AuthKey.io" : "Check spam if not received"}
            </p>
          </div>

          {otpError && <Alert type="error" message={otpError} />}

          <OtpInput value={otp} onChange={(v) => { setOtp(v); setOtpError(""); }}
            error={otpV.message} touched={otpTouched} />

          <SubmitBtn loading={loading}>Verify <ArrowRight className="w-4 h-4" /></SubmitBtn>

          {mode === "phone" && (
            <div className="text-center text-sm">
              {resendTimer > 0
                ? <span className="text-[#aaa] text-xs">Resend in {resendTimer}s</span>
                : <button type="button" onClick={handleResend} className="text-[#155c32] font-semibold hover:underline text-xs">Resend OTP</button>
              }
            </div>
          )}
        </form>
      )}

      {step === "reset" && (
        <form onSubmit={handleReset} className="space-y-4" noValidate>
          <Alert type="success" message="Identity verified! Set your new password." />

          <Field id="fp-newpwd" label="New Password *"
            error={newPwdV.message} touched={t3.pwd} success={newPwdV.valid}>
            <AuthInput id="fp-newpwd" type={showPwd ? "text" : "password"}
              autoComplete="new-password" placeholder="Min. 8 chars, uppercase, number"
              value={newPwd} onChange={(e) => setNewPwd(e.target.value)}
              onBlur={() => setT3((t) => ({ ...t, pwd: true }))}
              leftIcon={<Lock className="w-4 h-4" />}
              rightNode={
                <button type="button" onClick={() => setShowPwd((v) => !v)} className="text-[#aaa] hover:text-[#155c32] transition" tabIndex={-1}>
                  {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              }
              error={!newPwdV.valid} success={newPwdV.valid} touched={t3.pwd}
            />
          </Field>

          <Field id="fp-confirm" label="Confirm New Password *"
            error={confirmV.message} touched={t3.confirm} success={confirmV.valid}>
            <AuthInput id="fp-confirm" type={showPwd ? "text" : "password"}
              autoComplete="new-password" placeholder="Re-enter new password"
              value={confirmPwd} onChange={(e) => setConfirmPwd(e.target.value)}
              onBlur={() => setT3((t) => ({ ...t, confirm: true }))}
              leftIcon={<Lock className="w-4 h-4" />}
              error={!confirmV.valid} success={confirmV.valid} touched={t3.confirm}
            />
          </Field>

          <SubmitBtn loading={loading}>Reset Password <ArrowRight className="w-4 h-4" /></SubmitBtn>
        </form>
      )}
    </AuthLayout>
  );
}
