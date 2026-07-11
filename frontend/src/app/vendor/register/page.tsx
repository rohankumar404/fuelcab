"use client";

import { useState, useCallback } from "react";
import Link from "next/link";
import {
  Mail, Lock, Eye, EyeOff, User, Phone, Building2, MapPin,
  FileText, ArrowRight, ShieldCheck, CheckCircle2,
} from "lucide-react";
import { Alert, OtpInput, PasswordStrengthMeter } from "@/components/auth/AuthComponents";
import {
  validateEmail, validatePhone, validateName, validateCompany,
  validateGST, validatePassword, validateConfirmPassword,
  validateOtp, getPasswordStrength,
} from "@/lib/auth-validation";
import { sendOtp, verifyOtp } from "@/lib/authkey";

const FUEL_TYPES = ["Diesel (HSD)", "CNG", "LPG", "Lubricants", "AdBlue"];
const STATES = [
  "Delhi", "Uttar Pradesh", "Maharashtra", "Haryana", "Rajasthan",
  "Punjab", "Gujarat", "Karnataka", "Tamil Nadu", "Telangana", "Madhya Pradesh",
];
const RESEND_COOLDOWN = 30;

type VStep = "business" | "location" | "otp" | "password" | "done";

function VendorLogo() {
  return (
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
        <span className="text-xs font-bold text-[#33b248] uppercase tracking-widest">Vendor Registration</span>
      </div>
      <h1 className="mt-4 text-2xl font-extrabold text-white">Become a FuelCab Supplier</h1>
      <p className="mt-1 text-sm text-gray-400">Join our certified vendor network in 4 easy steps</p>
    </div>
  );
}

const STEPS_LABELS = ["Business", "Location", "Verify", "Secure"];

export default function VendorRegisterPage() {
  const [step, setStep] = useState<VStep>("business");

  // Step 1
  const [contactName, setContactName] = useState("");
  const [company, setCompany]         = useState("");
  const [email, setEmail]             = useState("");
  const [phone, setPhone]             = useState("");
  const [gst, setGst]                 = useState("");
  const [fuels, setFuels]             = useState<string[]>([]);
  const [t1, setT1] = useState({ name: false, company: false, email: false, phone: false, gst: false });

  // Step 2
  const [city, setCity]         = useState("");
  const [state, setState]       = useState("");
  const [address, setAddress]   = useState("");
  const [pincode, setPincode]   = useState("");
  const [t2, setT2] = useState({ city: false, state: false, address: false, pincode: false });

  // Step 3 OTP
  const [otp, setOtp]           = useState("");
  const [otpTouched, setOtpTouched] = useState(false);
  const [logId, setLogId]       = useState("");
  const [resendTimer, setResendTimer] = useState(0);
  const [otpError, setOtpError] = useState("");

  // Step 4 Password
  const [password, setPassword]   = useState("");
  const [confirmPwd, setConfirmPwd] = useState("");
  const [showPwd, setShowPwd]     = useState(false);
  const [agreeTerms, setAgreeTerms] = useState(false);
  const [t4, setT4] = useState({ password: false, confirm: false });

  const [loading, setLoading]   = useState(false);
  const [apiError, setApiError] = useState("");

  // Validators
  const nameV    = validateName(contactName);
  const companyV = validateCompany(company);
  const emailV   = validateEmail(email);
  const phoneV   = validatePhone(phone);
  const gstV     = validateGST(gst);
  const otpV     = validateOtp(otp);
  const passwordV = validatePassword(password);
  const confirmV  = validateConfirmPassword(password, confirmPwd);
  const strength  = getPasswordStrength(password);

  const pinValid  = { valid: /^\d{6}$/.test(pincode), message: "6-digit PIN required" };
  const cityValid = { valid: city.trim().length > 1, message: "City is required" };
  const stateValid = { valid: !!state, message: "State is required" };
  const addrValid = { valid: address.trim().length > 5, message: "Full address required" };

  const stepIdx: Record<VStep, number> = { business: 0, location: 1, otp: 2, password: 3, done: 4 };
  const currentIdx = stepIdx[step];

  const startTimer = () => {
    setResendTimer(RESEND_COOLDOWN);
    const id = setInterval(() => setResendTimer((v) => { if (v <= 1) { clearInterval(id); return 0; } return v - 1; }), 1000);
  };

  // Step 1 → 2
  const handleBusiness = (e: React.FormEvent) => {
    e.preventDefault();
    setT1({ name: true, company: true, email: true, phone: true, gst: true });
    if (!nameV.valid || !companyV.valid || !emailV.valid || !phoneV.valid || !gstV.valid) return;
    setStep("location");
  };

  // Step 2 → OTP
  const handleLocation = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setT2({ city: true, state: true, address: true, pincode: true });
    if (!cityValid.valid || !stateValid.valid || !addrValid.valid || !pinValid.valid) return;
    setLoading(true);
    setApiError("");
    try {
      const res = await sendOtp(phone);
      if (!res.success) { setApiError(res.error ?? "Failed to send OTP"); return; }
      setLogId(res.logId ?? "");
      startTimer();
      setStep("otp");
    } finally {
      setLoading(false);
    }
  }, [phone, cityValid, stateValid, addrValid, pinValid]);

  // OTP → Password
  const handleOtp = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setOtpTouched(true);
    if (!otpV.valid) return;
    setLoading(true);
    setOtpError("");
    try {
      const res = await verifyOtp(otp, logId);
      if (!res.success) { setOtpError(res.error ?? "Invalid OTP"); return; }
      setStep("password");
    } finally {
      setLoading(false);
    }
  }, [otp, logId, otpV]);

  // Password → Done
  const handlePassword = useCallback(async (e: React.FormEvent) => {
    e.preventDefault();
    setT4({ password: true, confirm: true });
    if (!passwordV.valid || !confirmV.valid || !agreeTerms) return;
    setLoading(true);
    try {
      // TODO: POST /api/v1/auth/vendor/register
      await new Promise((r) => setTimeout(r, 1500));
      setStep("done");
    } finally {
      setLoading(false);
    }
  }, [passwordV, confirmV, agreeTerms]);

  const handleResend = async () => {
    if (resendTimer > 0) return;
    setLoading(true);
    const res = await sendOtp(phone);
    if (res.success) { setLogId(res.logId ?? ""); setOtp(""); startTimer(); }
    else setOtpError(res.error ?? "Resend failed");
    setLoading(false);
  };

  // Dark input helper
  const DarkInput = ({ id, type = "text", placeholder, value, onChange, onBlur, icon: Icon, error, touched, maxLength }: {
    id: string; type?: string; placeholder: string; value: string;
    onChange: (v: string) => void; onBlur?: () => void;
    icon?: React.FC<{ className?: string }>; error?: boolean; touched?: boolean; maxLength?: number;
  }) => (
    <div className={`flex items-center rounded-xl border overflow-hidden transition focus-within:ring-2 bg-white/8 ${
      touched && error ? "border-red-400 focus-within:ring-red-400/20" : "border-white/15 focus-within:border-[#33b248] focus-within:ring-[#33b248]/20"
    }`}>
      {Icon && <span className="pl-3.5 text-gray-400 flex-shrink-0"><Icon className="w-4 h-4" /></span>}
      <input id={id} type={type} placeholder={placeholder} value={value}
        maxLength={maxLength}
        onChange={(e) => onChange(e.target.value)}
        onBlur={onBlur}
        className="flex-1 h-11 px-3 bg-transparent text-sm text-white placeholder-gray-500 focus:outline-none"
      />
    </div>
  );

  const ErrMsg = ({ msg, visible }: { msg: string; visible: boolean }) =>
    visible ? <p className="mt-1.5 text-xs text-red-400">{msg}</p> : null;

  const DarkLabel = ({ children }: { children: React.ReactNode }) => (
    <label className="block text-sm font-semibold text-white mb-1.5">{children}</label>
  );

  if (step === "done") {
    return (
      <div className="min-h-screen bg-gradient-to-br from-[#0b2e1a] via-[#0d3a1f] to-[#0b2e1a] flex items-center justify-center px-4">
        <div className="text-center max-w-sm">
          <div className="w-24 h-24 rounded-full bg-gradient-to-br from-[#33b248] to-[#155c32] flex items-center justify-center mx-auto mb-6 shadow-2xl shadow-[#33b248]/30">
            <CheckCircle2 className="w-12 h-12 text-white" strokeWidth={1.5} />
          </div>
          <h1 className="text-2xl font-extrabold text-white mb-2">Application Submitted!</h1>
          <p className="text-gray-400 text-sm leading-relaxed mb-2">
            Thank you, <strong className="text-white">{contactName}</strong>! Your vendor application for{" "}
            <strong className="text-white">{company}</strong> is under review.
          </p>
          <p className="text-xs text-gray-500 mb-8">
            We&apos;ll verify your GST, depot address, and fuel licenses within 24–48 hours. You&apos;ll be notified at <strong className="text-gray-300">{email}</strong>
          </p>
          <div className="space-y-3">
            <Link href="/vendor/login"
              className="w-full h-11 rounded-xl bg-[#33b248] text-white font-bold text-sm hover:bg-[#2a9a3d] transition flex items-center justify-center gap-2">
              Vendor Login <ArrowRight className="w-4 h-4" />
            </Link>
            <Link href="/"
              className="w-full h-11 rounded-xl border border-white/15 text-gray-300 text-sm font-semibold hover:border-[#33b248] hover:text-[#33b248] transition flex items-center justify-center">
              Back to Home
            </Link>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-[#0b2e1a] via-[#0d3a1f] to-[#0b2e1a] flex flex-col">
      <div className="fixed inset-0 pointer-events-none">
        <div className="absolute -top-32 -left-32 w-96 h-96 bg-[#33b248]/8 rounded-full blur-3xl" />
        <div className="absolute -bottom-32 -right-32 w-96 h-96 bg-[#33b248]/6 rounded-full blur-3xl" />
      </div>

      <div className="relative flex-1 flex items-center justify-center px-4 py-10">
        <div className="w-full max-w-xl">
          <VendorLogo />

          {/* Stepper */}
          <div className="flex items-center mb-6">
            {STEPS_LABELS.map((label, i) => (
              <div key={label} className="flex items-center flex-1">
                <div className="flex flex-col items-center gap-1">
                  <div className={`w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all ${
                    i < currentIdx ? "bg-[#33b248] text-white"
                    : i === currentIdx ? "bg-[#33b248] text-white ring-4 ring-[#33b248]/25"
                    : "bg-white/10 text-gray-500"
                  }`}>
                    {i < currentIdx ? "✓" : i + 1}
                  </div>
                  <span className={`text-[10px] font-semibold hidden sm:block ${i <= currentIdx ? "text-[#33b248]" : "text-gray-600"}`}>{label}</span>
                </div>
                {i < STEPS_LABELS.length - 1 && (
                  <div className={`flex-1 h-0.5 mx-1 rounded-full transition-all duration-300 ${i < currentIdx ? "bg-[#33b248]" : "bg-white/10"}`} />
                )}
              </div>
            ))}
          </div>

          <div className="bg-white/5 backdrop-blur-xl rounded-3xl border border-white/10 shadow-2xl p-8">
            {apiError && <div className="mb-4"><Alert type="error" message={apiError} /></div>}

            {/* ── STEP 1: BUSINESS INFO ── */}
            {step === "business" && (
              <form onSubmit={handleBusiness} className="space-y-4" noValidate>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <DarkLabel>Contact Person *</DarkLabel>
                    <DarkInput id="v-name" placeholder="Your full name" value={contactName}
                      onChange={setContactName} onBlur={() => setT1((t) => ({ ...t, name: true }))}
                      icon={User} error={!nameV.valid} touched={t1.name} />
                    <ErrMsg msg={nameV.message} visible={t1.name && !nameV.valid} />
                  </div>
                  <div>
                    <DarkLabel>Company Name *</DarkLabel>
                    <DarkInput id="v-company" placeholder="ACME Fuels Pvt. Ltd." value={company}
                      onChange={setCompany} onBlur={() => setT1((t) => ({ ...t, company: true }))}
                      icon={Building2} error={!companyV.valid} touched={t1.company} />
                    <ErrMsg msg={companyV.message} visible={t1.company && !companyV.valid} />
                  </div>
                </div>

                <div>
                  <DarkLabel>Business Email *</DarkLabel>
                  <DarkInput id="v-email" type="email" placeholder="vendor@company.com" value={email}
                    onChange={setEmail} onBlur={() => setT1((t) => ({ ...t, email: true }))}
                    icon={Mail} error={!emailV.valid} touched={t1.email} />
                  <ErrMsg msg={emailV.message} visible={t1.email && !emailV.valid} />
                </div>

                <div>
                  <DarkLabel>Mobile Number * (OTP verification)</DarkLabel>
                  <div className="flex gap-2">
                    <div className="flex items-center h-11 px-3 rounded-xl border border-white/15 bg-white/8 text-sm text-gray-300 font-semibold flex-shrink-0">
                      🇮🇳 +91
                    </div>
                    <DarkInput id="v-phone" type="tel" placeholder="98765 43210" value={phone}
                      onChange={(v) => setPhone(v.replace(/\D/g, ""))} onBlur={() => setT1((t) => ({ ...t, phone: true }))}
                      icon={Phone} error={!phoneV.valid} touched={t1.phone} maxLength={10} />
                  </div>
                  <ErrMsg msg={phoneV.message} visible={t1.phone && !phoneV.valid} />
                </div>

                <div>
                  <DarkLabel>GST Number *</DarkLabel>
                  <DarkInput id="v-gst" placeholder="22AAAAA0000A1Z5" value={gst}
                    onChange={(v) => setGst(v.toUpperCase())} onBlur={() => setT1((t) => ({ ...t, gst: true }))}
                    icon={FileText} error={!gstV.valid} touched={t1.gst} maxLength={15} />
                  <ErrMsg msg={gstV.message} visible={t1.gst && !gstV.valid} />
                </div>

                <div>
                  <DarkLabel>Fuels You Supply</DarkLabel>
                  <div className="flex flex-wrap gap-2">
                    {FUEL_TYPES.map((f) => (
                      <button key={f} type="button"
                        onClick={() => setFuels((prev) => prev.includes(f) ? prev.filter((x) => x !== f) : [...prev, f])}
                        className={`px-3 py-1.5 rounded-full text-xs font-semibold border transition-all ${
                          fuels.includes(f) ? "bg-[#33b248] border-[#33b248] text-white" : "bg-white/5 border-white/15 text-gray-300 hover:border-[#33b248]/50"
                        }`}>
                        {f}
                      </button>
                    ))}
                  </div>
                  {fuels.length === 0 && <p className="text-xs text-amber-400 mt-1.5">Select at least one fuel type</p>}
                </div>

                <button type="submit" disabled={fuels.length === 0}
                  className="w-full h-12 rounded-xl bg-[#33b248] text-white font-bold text-sm hover:bg-[#2a9a3d] transition hover:-translate-y-px disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                  Next: Location Details <ArrowRight className="w-4 h-4" />
                </button>
              </form>
            )}

            {/* ── STEP 2: LOCATION ── */}
            {step === "location" && (
              <form onSubmit={handleLocation} className="space-y-4" noValidate>
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <DarkLabel>City *</DarkLabel>
                    <DarkInput id="v-city" placeholder="Noida" value={city}
                      onChange={setCity} onBlur={() => setT2((t) => ({ ...t, city: true }))}
                      icon={MapPin} error={!cityValid.valid} touched={t2.city} />
                    <ErrMsg msg={cityValid.message} visible={t2.city && !cityValid.valid} />
                  </div>
                  <div>
                    <DarkLabel>PIN Code *</DarkLabel>
                    <DarkInput id="v-pin" type="tel" placeholder="201301" value={pincode}
                      onChange={(v) => setPincode(v.replace(/\D/g, ""))} onBlur={() => setT2((t) => ({ ...t, pincode: true }))}
                      error={!pinValid.valid} touched={t2.pincode} maxLength={6} />
                    <ErrMsg msg={pinValid.message} visible={t2.pincode && !pinValid.valid} />
                  </div>
                </div>

                <div>
                  <DarkLabel>State *</DarkLabel>
                  <select value={state} onChange={(e) => setState(e.target.value)}
                    onBlur={() => setT2((t) => ({ ...t, state: true }))}
                    className="w-full h-11 px-4 rounded-xl border border-white/15 bg-white/8 text-sm text-white focus:outline-none focus:border-[#33b248] transition">
                    <option value="" className="bg-[#0d3a1f]">Select State</option>
                    {STATES.map((s) => <option key={s} value={s} className="bg-[#0d3a1f]">{s}</option>)}
                  </select>
                  <ErrMsg msg={stateValid.message} visible={t2.state && !stateValid.valid} />
                </div>

                <div>
                  <DarkLabel>Depot / Warehouse Address *</DarkLabel>
                  <textarea value={address} onChange={(e) => setAddress(e.target.value)}
                    onBlur={() => setT2((t) => ({ ...t, address: true }))}
                    rows={3} placeholder="Full address of your primary depot or storage facility..."
                    className="w-full px-4 py-3 rounded-xl border border-white/15 bg-white/8 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] transition resize-none" />
                  <ErrMsg msg={addrValid.message} visible={t2.address && !addrValid.valid} />
                </div>

                <div className="flex gap-3">
                  <button type="button" onClick={() => setStep("business")}
                    className="flex-1 h-11 rounded-xl border border-white/15 text-gray-300 font-semibold text-sm hover:border-[#33b248] hover:text-[#33b248] transition">
                    ← Back
                  </button>
                  <button type="submit" disabled={loading}
                    className="flex-1 h-12 rounded-xl bg-[#33b248] text-white font-bold text-sm hover:bg-[#2a9a3d] transition hover:-translate-y-px flex items-center justify-center gap-2 disabled:opacity-60">
                    {loading
                      ? <><div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" /> Sending OTP…</>
                      : <>Send OTP to Verify <ArrowRight className="w-4 h-4" /></>
                    }
                  </button>
                </div>
              </form>
            )}

            {/* ── STEP 3: OTP ── */}
            {step === "otp" && (
              <form onSubmit={handleOtp} className="space-y-5" noValidate>
                <div className="text-center">
                  <div className="w-14 h-14 rounded-2xl bg-[#33b248]/15 flex items-center justify-center mx-auto mb-3">
                    <Phone className="w-7 h-7 text-[#33b248]" />
                  </div>
                  <p className="text-sm text-gray-300">OTP sent to <strong className="text-white">+91 {phone}</strong></p>
                  <p className="text-xs text-gray-500 mt-1">Valid 5 minutes · via AuthKey.io</p>
                </div>

                {otpError && <Alert type="error" message={otpError} />}

                <OtpInput value={otp} onChange={(v) => { setOtp(v); setOtpError(""); }}
                  error={otpV.message} touched={otpTouched} />

                <button type="submit" disabled={loading}
                  className="w-full h-12 rounded-xl bg-[#33b248] text-white font-bold text-sm hover:bg-[#2a9a3d] transition flex items-center justify-center gap-2 disabled:opacity-60">
                  {loading
                    ? <><div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" /> Verifying…</>
                    : <>Verify OTP <ArrowRight className="w-4 h-4" /></>
                  }
                </button>

                <div className="flex justify-between text-sm">
                  <button type="button" onClick={() => setStep("location")} className="text-gray-400 hover:text-white transition">← Change</button>
                  {resendTimer > 0
                    ? <span className="text-xs text-gray-500">Resend in {resendTimer}s</span>
                    : <button type="button" onClick={handleResend} className="text-xs text-[#33b248] font-semibold hover:underline">Resend OTP</button>
                  }
                </div>
              </form>
            )}

            {/* ── STEP 4: PASSWORD ── */}
            {step === "password" && (
              <form onSubmit={handlePassword} className="space-y-4" noValidate>
                <Alert type="success" message={`+91 ${phone} verified ✓ — Set a strong password to complete registration`} />

                <div>
                  <DarkLabel>Create Password *</DarkLabel>
                  <div className={`flex items-center rounded-xl border overflow-hidden transition focus-within:ring-2 bg-white/8 ${
                    t4.password && !passwordV.valid ? "border-red-400 focus-within:ring-red-400/20" : "border-white/15 focus-within:border-[#33b248] focus-within:ring-[#33b248]/20"
                  }`}>
                    <span className="pl-3.5 text-gray-400 flex-shrink-0"><Lock className="w-4 h-4" /></span>
                    <input type={showPwd ? "text" : "password"} autoComplete="new-password"
                      placeholder="Min. 8 chars, uppercase, number"
                      value={password} onChange={(e) => setPassword(e.target.value)}
                      onBlur={() => setT4((t) => ({ ...t, password: true }))}
                      className="flex-1 h-11 px-3 bg-transparent text-sm text-white placeholder-gray-500 focus:outline-none" />
                    <button type="button" onClick={() => setShowPwd((v) => !v)} tabIndex={-1}
                      className="pr-3.5 text-gray-400 hover:text-[#33b248] transition">
                      {showPwd ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                  <PasswordStrengthMeter score={strength.score} label={strength.label}
                    color={strength.color} suggestions={strength.suggestions} visible={password.length > 0} />
                  <ErrMsg msg={passwordV.message} visible={t4.password && !passwordV.valid} />
                </div>

                <div>
                  <DarkLabel>Confirm Password *</DarkLabel>
                  <div className={`flex items-center rounded-xl border overflow-hidden transition focus-within:ring-2 bg-white/8 ${
                    t4.confirm && !confirmV.valid ? "border-red-400 focus-within:ring-red-400/20" : "border-white/15 focus-within:border-[#33b248] focus-within:ring-[#33b248]/20"
                  }`}>
                    <span className="pl-3.5 text-gray-400 flex-shrink-0"><Lock className="w-4 h-4" /></span>
                    <input type={showPwd ? "text" : "password"} autoComplete="new-password"
                      placeholder="Re-enter password"
                      value={confirmPwd} onChange={(e) => setConfirmPwd(e.target.value)}
                      onBlur={() => setT4((t) => ({ ...t, confirm: true }))}
                      className="flex-1 h-11 px-3 bg-transparent text-sm text-white placeholder-gray-500 focus:outline-none" />
                  </div>
                  <ErrMsg msg={confirmV.message} visible={t4.confirm && !confirmV.valid} />
                </div>

                <label className="flex items-start gap-3 cursor-pointer group">
                  <div onClick={() => setAgreeTerms((v) => !v)}
                    className={`w-5 h-5 rounded-md border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition cursor-pointer ${
                      agreeTerms ? "bg-[#33b248] border-[#33b248]" : "border-gray-500 group-hover:border-[#33b248]"
                    }`}>
                    {agreeTerms && <span className="text-white text-[10px] font-bold">✓</span>}
                  </div>
                  <span className="text-sm text-gray-400 leading-snug">
                    I agree to FuelCab&apos;s{" "}
                    <Link href="#terms" className="text-[#33b248] font-semibold hover:underline">Vendor Terms</Link>{" "}
                    and <Link href="#privacy" className="text-[#33b248] font-semibold hover:underline">Privacy Policy</Link>
                  </span>
                </label>

                <button type="submit" disabled={loading || !agreeTerms}
                  className="w-full h-12 rounded-xl bg-[#33b248] text-white font-bold text-sm hover:bg-[#2a9a3d] transition hover:-translate-y-px flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                  {loading
                    ? <><div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" /> Submitting…</>
                    : <>Submit Vendor Application <ArrowRight className="w-4 h-4" /></>
                  }
                </button>
              </form>
            )}
          </div>

          <p className="text-center text-xs text-gray-600 mt-5">
            Already registered?{" "}
            <Link href="/vendor/login" className="text-[#33b248] font-semibold hover:underline">Vendor Login →</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
