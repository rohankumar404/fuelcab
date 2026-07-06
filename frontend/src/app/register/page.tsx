"use client";

import { useState, useCallback } from "react";
import Link from "next/link";
import { Mail, Lock, Eye, EyeOff, User, Phone, Building2, ArrowRight, CheckCircle2 } from "lucide-react";
import {
  AuthLayout, Field, AuthInput, SubmitBtn, Divider, Alert,
  OtpInput, PasswordStrengthMeter,
} from "@/components/auth/AuthComponents";
import {
  validateEmail, validatePhone, validateName, validateCompany,
  validatePassword, validateConfirmPassword, validateOtp,
  getPasswordStrength,
} from "@/lib/auth-validation";
import { sendOtp, verifyOtp } from "@/lib/authkey";

type Step = "info" | "otp" | "password" | "done";

const RESEND_COOLDOWN = 30;

export default function RegisterPage() {
  const [step, setStep] = useState<Step>("info");

  // ── Step 1: Basic Info
  const [name, setName]       = useState("");
  const [company, setCompany] = useState("");
  const [email, setEmail]     = useState("");
  const [phone, setPhone]     = useState("");
  const [touched1, setTouched1] = useState({ name: false, company: false, email: false, phone: false });

  // ── Step 2: OTP
  const [otp, setOtp]         = useState("");
  const [otpTouched, setOtpTouched] = useState(false);
  const [otpLogId, setOtpLogId]   = useState("");
  const [resendTimer, setResendTimer] = useState(0);
  const [otpApiError, setOtpApiError] = useState("");

  // ── Step 3: Password
  const [password, setPassword]         = useState("");
  const [confirmPwd, setConfirmPwd]     = useState("");
  const [showPwd, setShowPwd]           = useState(false);
  const [agreeTerms, setAgreeTerms]     = useState(false);
  const [touched3, setTouched3]         = useState({ password: false, confirm: false });

  // ── UI State
  const [loading, setLoading]   = useState(false);
  const [apiError, setApiError] = useState("");

  // ── Validation
  const nameV    = validateName(name);
  const companyV = validateCompany(company);
  const emailV   = validateEmail(email);
  const phoneV   = validatePhone(phone);
  const otpV     = validateOtp(otp);
  const passwordV = validatePassword(password);
  const confirmV  = validateConfirmPassword(password, confirmPwd);
  const strength  = getPasswordStrength(password);

  // ── Step 1 → OTP ────────────────────────────────────────────
  const handleInfoSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setTouched1({ name: true, company: true, email: true, phone: true });
    if (!nameV.valid || !companyV.valid || !emailV.valid || !phoneV.valid) return;

    setLoading(true);
    setApiError("");
    try {
      const result = await sendOtp(phone);
      if (!result.success) { setApiError(result.error ?? "Failed to send OTP"); return; }
      setOtpLogId(result.logId ?? "");
      setStep("otp");
      startResendTimer();
    } catch {
      setApiError("Network error. Try again.");
    } finally {
      setLoading(false);
    }
  }, [nameV, companyV, emailV, phoneV, phone]);

  // ── Resend cooldown timer ──
  const startResendTimer = () => {
    setResendTimer(RESEND_COOLDOWN);
    const id = setInterval(() => {
      setResendTimer((v) => {
        if (v <= 1) { clearInterval(id); return 0; }
        return v - 1;
      });
    }, 1000);
  };

  const handleResendOtp = async () => {
    if (resendTimer > 0) return;
    setLoading(true);
    setOtpApiError("");
    try {
      const result = await sendOtp(phone);
      if (!result.success) { setOtpApiError(result.error ?? "Failed to resend OTP"); return; }
      setOtpLogId(result.logId ?? "");
      setOtp("");
      startResendTimer();
    } finally {
      setLoading(false);
    }
  };

  // ── Step 2 → Password ────────────────────────────────────────
  const handleOtpSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setOtpTouched(true);
    if (!otpV.valid) return;
    setLoading(true);
    setOtpApiError("");
    try {
      const result = await verifyOtp(otp, otpLogId);
      if (!result.success) { setOtpApiError(result.error ?? "Invalid OTP"); return; }
      setStep("password");
    } finally {
      setLoading(false);
    }
  }, [otp, otpLogId, otpV]);

  // ── Step 3 → Done ────────────────────────────────────────────
  const handlePasswordSubmit = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setTouched3({ password: true, confirm: true });
    if (!passwordV.valid || !confirmV.valid || !agreeTerms) return;
    setLoading(true);
    setApiError("");
    try {
      // TODO: POST /api/v1/auth/register with { name, company, email, phone, password }
      await new Promise((r) => setTimeout(r, 1500));
      setStep("done");
    } catch {
      setApiError("Registration failed. Please try again.");
    } finally {
      setLoading(false);
    }
  }, [passwordV, confirmV, agreeTerms]);

  // ─── STEP LABELS ────────────────────────────────────────────
  const STEPS = [
    { id: "info",     label: "Your Info" },
    { id: "otp",      label: "Verify" },
    { id: "password", label: "Secure" },
  ];
  const stepIdx = { info: 0, otp: 1, password: 2, done: 3 };
  const currentIdx = stepIdx[step];

  if (step === "done") {
    return (
      <AuthLayout title="Account Created! 🎉" subtitle="You're all set to order bulk fuel">
        <div className="text-center space-y-4">
          <div className="w-20 h-20 rounded-full bg-gradient-to-br from-[#155c32] to-[#33b248] flex items-center justify-center mx-auto shadow-xl shadow-[#155c32]/30">
            <CheckCircle2 className="w-10 h-10 text-white" strokeWidth={1.5} />
          </div>
          <div>
            <p className="text-sm text-[#555] leading-relaxed">
              Welcome, <strong>{name}</strong>! Your FuelCab business account for{" "}
              <strong>{company}</strong> is ready.
            </p>
            <p className="text-xs text-[#aaa] mt-1">
              A verification email has been sent to <strong>{email}</strong>
            </p>
          </div>
          <div className="pt-2 space-y-3">
            <Link href="/login"
              className="w-full h-12 rounded-xl bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] transition flex items-center justify-center gap-2">
              Sign In Now <ArrowRight className="w-4 h-4" />
            </Link>
            <Link href="/"
              className="w-full h-11 rounded-xl border border-[#e2e8e4] text-[#555] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition flex items-center justify-center">
              Back to Home
            </Link>
          </div>
        </div>
      </AuthLayout>
    );
  }

  return (
    <AuthLayout
      title="Create Business Account"
      subtitle="Get started with bulk fuel delivery for your business"
      maxWidth="max-w-lg"
    >
      {/* Progress bar */}
      <div className="mb-6">
        <div className="flex items-center gap-2 mb-3">
          {STEPS.map((s, i) => (
            <div key={s.id} className="flex items-center flex-1">
              <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 transition-all duration-300 ${
                i < currentIdx ? "bg-[#155c32] text-white"
                : i === currentIdx ? "bg-[#155c32] text-white ring-4 ring-[#155c32]/20"
                : "bg-[#f0f0f0] text-[#bbb]"
              }`}>
                {i < currentIdx ? "✓" : i + 1}
              </div>
              <div className="flex-1 ml-1 hidden sm:block">
                <span className={`text-[11px] font-semibold ${i <= currentIdx ? "text-[#155c32]" : "text-[#ccc]"}`}>
                  {s.label}
                </span>
              </div>
              {i < STEPS.length - 1 && (
                <div className={`h-0.5 flex-1 mx-1 rounded-full transition-all duration-300 ${i < currentIdx ? "bg-[#155c32]" : "bg-[#e2e8e4]"}`} />
              )}
            </div>
          ))}
        </div>
      </div>

      {/* ─── STEP 1: INFO ─── */}
      {step === "info" && (
        <form onSubmit={handleInfoSubmit} className="space-y-4" noValidate>
          {apiError && <Alert type="error" message={apiError} />}

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Field id="reg-name" label="Full Name *"
              error={nameV.message} touched={touched1.name} success={nameV.valid}>
              <AuthInput id="reg-name" type="text" autoComplete="name"
                placeholder="Rahul Sharma"
                value={name} onChange={(e) => setName(e.target.value)}
                onBlur={() => setTouched1((t) => ({ ...t, name: true }))}
                leftIcon={<User className="w-4 h-4" />}
                error={!nameV.valid} success={nameV.valid} touched={touched1.name}
              />
            </Field>

            <Field id="reg-company" label="Company Name *"
              error={companyV.message} touched={touched1.company} success={companyV.valid}>
              <AuthInput id="reg-company" type="text" autoComplete="organization"
                placeholder="ACME Fuels Pvt. Ltd."
                value={company} onChange={(e) => setCompany(e.target.value)}
                onBlur={() => setTouched1((t) => ({ ...t, company: true }))}
                leftIcon={<Building2 className="w-4 h-4" />}
                error={!companyV.valid} success={companyV.valid} touched={touched1.company}
              />
            </Field>
          </div>

          <Field id="reg-email" label="Business Email *"
            error={emailV.message} touched={touched1.email} success={emailV.valid}>
            <AuthInput id="reg-email" type="email" autoComplete="email"
              placeholder="you@yourcompany.com"
              value={email} onChange={(e) => setEmail(e.target.value)}
              onBlur={() => setTouched1((t) => ({ ...t, email: true }))}
              leftIcon={<Mail className="w-4 h-4" />}
              error={!emailV.valid} success={emailV.valid} touched={touched1.email}
            />
          </Field>

          <Field id="reg-phone" label="Mobile Number * (OTP will be sent here)"
            error={phoneV.message} touched={touched1.phone} success={phoneV.valid}
            hint="We'll send a 6-digit OTP to verify your number via AuthKey">
            <div className="flex gap-2">
              <div className="flex items-center h-11 px-3 rounded-xl border border-[#e2e8e4] bg-[#f9fbfa] text-sm text-[#555] font-semibold flex-shrink-0">
                🇮🇳 +91
              </div>
              <AuthInput id="reg-phone" type="tel" autoComplete="tel"
                placeholder="98765 43210" maxLength={10}
                value={phone} onChange={(e) => setPhone(e.target.value.replace(/\D/g, ""))}
                onBlur={() => setTouched1((t) => ({ ...t, phone: true }))}
                leftIcon={<Phone className="w-4 h-4" />}
                error={!phoneV.valid} success={phoneV.valid} touched={touched1.phone}
              />
            </div>
          </Field>

          <SubmitBtn loading={loading}>
            Send OTP to +91 {phone || "XXXXXXXXXX"} <ArrowRight className="w-4 h-4" />
          </SubmitBtn>
        </form>
      )}

      {/* ─── STEP 2: OTP ─── */}
      {step === "otp" && (
        <form onSubmit={handleOtpSubmit} className="space-y-5" noValidate>
          <div className="text-center">
            <div className="w-14 h-14 rounded-2xl bg-[#155c32]/10 flex items-center justify-center mx-auto mb-3">
              <Phone className="w-7 h-7 text-[#155c32]" />
            </div>
            <p className="text-sm text-[#555]">
              A 6-digit OTP was sent to <strong className="text-[#1a1a1a]">+91 {phone}</strong>
            </p>
            <p className="text-xs text-[#aaa] mt-1">Valid for 5 minutes · Powered by AuthKey.io</p>
          </div>

          {otpApiError && <Alert type="error" message={otpApiError} />}

          <OtpInput
            value={otp}
            onChange={(v) => { setOtp(v); setOtpApiError(""); }}
            error={otpV.message}
            touched={otpTouched}
          />

          <SubmitBtn loading={loading}>
            Verify OTP <ArrowRight className="w-4 h-4" />
          </SubmitBtn>

          <div className="flex items-center justify-between text-sm">
            <button type="button" onClick={() => setStep("info")}
              className="text-[#777] hover:text-[#155c32] transition font-medium">
              ← Change number
            </button>
            {resendTimer > 0 ? (
              <span className="text-[#aaa] text-xs">Resend in {resendTimer}s</span>
            ) : (
              <button type="button" onClick={handleResendOtp}
                className="text-[#155c32] font-semibold hover:underline text-xs">
                Resend OTP
              </button>
            )}
          </div>

          <Alert type="info" message="Didn't receive it? Check spam or try resending. OTP expires in 5 minutes." />
        </form>
      )}

      {/* ─── STEP 3: PASSWORD ─── */}
      {step === "password" && (
        <form onSubmit={handlePasswordSubmit} className="space-y-4" noValidate>
          {apiError && <Alert type="error" message={apiError} />}

          <Alert type="success" message={`Phone +91 ${phone} verified ✓. Now set a strong password.`} />

          <Field id="reg-pwd" label="Create Password *"
            error={passwordV.message} touched={touched3.password} success={passwordV.valid}>
            <AuthInput id="reg-pwd" type={showPwd ? "text" : "password"}
              autoComplete="new-password" placeholder="Min. 8 chars, uppercase, number"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              onBlur={() => setTouched3((t) => ({ ...t, password: true }))}
              leftIcon={<Lock className="w-4 h-4" />}
              rightNode={
                <button type="button" onClick={() => setShowPwd((v) => !v)}
                  className="text-[#aaa] hover:text-[#155c32] transition" tabIndex={-1}>
                  {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              }
              error={!passwordV.valid} success={passwordV.valid} touched={touched3.password}
            />
            <PasswordStrengthMeter
              score={strength.score}
              label={strength.label}
              color={strength.color}
              suggestions={strength.suggestions}
              visible={password.length > 0}
            />
          </Field>

          <Field id="reg-confirm" label="Confirm Password *"
            error={confirmV.message} touched={touched3.confirm} success={confirmV.valid}>
            <AuthInput id="reg-confirm" type={showPwd ? "text" : "password"}
              autoComplete="new-password" placeholder="Re-enter your password"
              value={confirmPwd}
              onChange={(e) => setConfirmPwd(e.target.value)}
              onBlur={() => setTouched3((t) => ({ ...t, confirm: true }))}
              leftIcon={<Lock className="w-4 h-4" />}
              error={!confirmV.valid} success={confirmV.valid} touched={touched3.confirm}
            />
          </Field>

          {/* Terms */}
          <label className="flex items-start gap-3 cursor-pointer group">
            <div
              onClick={() => setAgreeTerms((v) => !v)}
              className={`w-5 h-5 rounded-md border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-all cursor-pointer ${
                agreeTerms ? "bg-[#155c32] border-[#155c32]" : "border-[#d0d8d4] group-hover:border-[#155c32]"
              }`}
            >
              {agreeTerms && <span className="text-white text-[10px] font-bold">✓</span>}
            </div>
            <span className="text-sm text-[#555] leading-snug">
              I agree to FuelCab&apos;s{" "}
              <Link href="#terms" className="text-[#155c32] font-semibold hover:underline">Terms &amp; Conditions</Link>{" "}
              and{" "}
              <Link href="#privacy" className="text-[#155c32] font-semibold hover:underline">Privacy Policy</Link>
            </span>
          </label>
          {!agreeTerms && touched3.password && (
            <p className="text-xs text-red-500 -mt-2">You must agree to the terms to continue</p>
          )}

          <SubmitBtn loading={loading} disabled={!agreeTerms}>
            Create My Account <ArrowRight className="w-4 h-4" />
          </SubmitBtn>
        </form>
      )}

      <Divider label="Already have an account?" />
      <Link href="/login"
        className="w-full h-11 rounded-xl border-2 border-[#e2e8e4] text-[#1a1a1a] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition-all duration-200 flex items-center justify-center">
        Sign In
      </Link>

      <p className="text-center text-xs text-[#aaa] mt-4">
        Want to become a vendor?{" "}
        <Link href="/vendor/register" className="text-[#155c32] font-semibold hover:underline">Apply here →</Link>
      </p>
    </AuthLayout>
  );
}
