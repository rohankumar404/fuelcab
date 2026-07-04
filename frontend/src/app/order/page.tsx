"use client";

import { useState, useCallback, useRef, useEffect } from "react";
import Link from "next/link";
import {
  Droplet, MapPin, Clock, ArrowRight, ArrowLeft, CheckCircle2,
  Plus, Minus, AlertCircle, ShieldCheck, Truck, Building2,
  CreditCard, Smartphone, Landmark, Banknote, ChevronRight,
  BadgeCheck, Flame, Star, RotateCcw, Package, Phone, User,
  Calendar, Info, Receipt, Zap, CircleCheck,
} from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

// ─── DATA ────────────────────────────────────────────────────────────────────

const FUEL_TYPES = [
  {
    id: "diesel", label: "Diesel (HSD)", shortLabel: "HSD",
    icon: "⛽", emoji: "🛢️",
    pricePerL: 94.72, unit: "L", unitLabel: "per litre",
    minQty: 100, maxQty: 50000, step: 50,
    tag: "Most Popular", tagColor: "bg-amber-100 text-amber-700",
    gradient: "from-amber-400 to-orange-500",
    cardBg: "bg-amber-50 border-amber-200",
    activeBg: "border-[#155c32] bg-[#155c32]/5",
    desc: "High Speed Diesel for generators, machinery & fleet vehicles",
    available: true,
  },
  {
    id: "petrol", label: "Petrol (MS)", shortLabel: "MS",
    icon: "🔴", emoji: "⚡",
    pricePerL: 102.46, unit: "L", unitLabel: "per litre",
    minQty: 100, maxQty: 10000, step: 50,
    tag: "Fast Delivery", tagColor: "bg-red-100 text-red-700",
    gradient: "from-red-400 to-rose-500",
    cardBg: "bg-red-50 border-red-200",
    activeBg: "border-[#155c32] bg-[#155c32]/5",
    desc: "Motor Spirit for vehicles, small engines & equipment",
    available: true,
  },
  {
    id: "cng", label: "CNG", shortLabel: "CNG",
    icon: "🌿", emoji: "💨",
    pricePerL: 76.59, unit: "Kg", unitLabel: "per kg",
    minQty: 50, maxQty: 5000, step: 25,
    tag: "Eco Friendly", tagColor: "bg-green-100 text-green-700",
    gradient: "from-green-400 to-emerald-500",
    cardBg: "bg-green-50 border-green-200",
    activeBg: "border-[#155c32] bg-[#155c32]/5",
    desc: "Compressed Natural Gas — cleaner fuel for fleets",
    available: true,
  },
  {
    id: "lubes", label: "Lubricants", shortLabel: "Lubes",
    icon: "🔵", emoji: "🛠️",
    pricePerL: 280.0, unit: "L", unitLabel: "per litre",
    minQty: 20, maxQty: 2000, step: 10,
    tag: "Industrial", tagColor: "bg-blue-100 text-blue-700",
    gradient: "from-blue-400 to-indigo-500",
    cardBg: "bg-blue-50 border-blue-200",
    activeBg: "border-[#155c32] bg-[#155c32]/5",
    desc: "Engine & industrial oils for heavy machinery",
    available: true,
  },
];

const SAVED_ADDRESSES = [
  {
    id: "addr1", tag: "Office", icon: Building2,
    name: "Vikram Singh", phone: "9876543210",
    line1: "Plot 42, Sector 62, Industrial Area",
    city: "Noida", state: "Uttar Pradesh", pincode: "201309",
  },
  {
    id: "addr2", tag: "Site", icon: MapPin,
    name: "Ramesh Yadav", phone: "9988776655",
    line1: "NH-58, Near Toll Plaza, Hapur Road",
    city: "Ghaziabad", state: "Uttar Pradesh", pincode: "201001",
  },
];

const DELIVERY_SLOTS = [
  { id: "s1", label: "Early Morning",  time: "6:00 AM – 9:00 AM",  available: true,  surge: false },
  { id: "s2", label: "Morning",        time: "9:00 AM – 12:00 PM", available: true,  surge: false, popular: true },
  { id: "s3", label: "Afternoon",      time: "12:00 PM – 3:00 PM", available: true,  surge: false },
  { id: "s4", label: "Evening",        time: "3:00 PM – 6:00 PM",  available: true,  surge: true,  surgeLabel: "+₹200" },
  { id: "s5", label: "Night",          time: "6:00 PM – 9:00 PM",  available: false, surge: false },
];

const PAYMENT_METHODS = [
  { id: "upi",     label: "UPI",           sub: "PhonePe, GPay, Paytm, BHIM", icon: Smartphone, popular: true },
  { id: "card",    label: "Credit / Debit Card", sub: "Visa, Mastercard, RuPay", icon: CreditCard, popular: false },
  { id: "netbank", label: "Net Banking",   sub: "All major Indian banks",      icon: Landmark,  popular: false },
  { id: "cod",     label: "Pay on Delivery", sub: "Cash / cheque accepted",    icon: Banknote,  popular: false },
];

const UPI_APPS = [
  { id: "phonepe", label: "PhonePe",   color: "bg-purple-500" },
  { id: "gpay",    label: "Google Pay",color: "bg-blue-500" },
  { id: "paytm",   label: "Paytm",     color: "bg-sky-500" },
  { id: "bhim",    label: "BHIM UPI",  color: "bg-orange-500" },
];

// Next 5 days for delivery date selection
function getNextDays(n: number) {
  const days = [];
  for (let i = 0; i < n; i++) {
    const d = new Date();
    d.setDate(d.getDate() + i);
    days.push({
      label: i === 0 ? "Today" : i === 1 ? "Tomorrow" : d.toLocaleDateString("en-IN", { weekday: "short" }),
      sub: d.toLocaleDateString("en-IN", { day: "numeric", month: "short" }),
      date: d.toISOString().split("T")[0],
      disabled: false,
    });
  }
  return days;
}

const DAYS = getNextDays(5);

// ─── STEP DEFINITIONS ────────────────────────────────────────────────────────
const STEPS = [
  { id: 1, label: "Fuel",     icon: Flame },
  { id: 2, label: "Quantity", icon: Package },
  { id: 3, label: "Address",  icon: MapPin },
  { id: 4, label: "Schedule", icon: Calendar },
  { id: 5, label: "Summary",  icon: Receipt },
  { id: 6, label: "Payment",  icon: CreditCard },
];

// ─── HELPERS ─────────────────────────────────────────────────────────────────
function fmt(n: number) {
  return "₹" + n.toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ─── TYPES ───────────────────────────────────────────────────────────────────
interface AddressForm {
  savedId: string;
  mode: "saved" | "new";
  name: string; phone: string; line1: string;
  line2: string; city: string; state: string; pincode: string;
}

interface FormErrors {
  [key: string]: string;
}

// ─── COMPONENT ───────────────────────────────────────────────────────────────
export default function OrderPage() {
  const [step, setStep] = useState(1);
  const [confirmed, setConfirmed] = useState(false);
  const [orderId] = useState(() => "FC" + Math.random().toString(36).slice(2, 8).toUpperCase());

  // Step 1 — Fuel
  const [fuelId, setFuelId] = useState("diesel");

  // Step 2 — Quantity
  const [qty, setQty] = useState(500);
  const [qtyInput, setQtyInput] = useState("500");
  const [qtyError, setQtyError] = useState("");
  const qtyRef = useRef<ReturnType<typeof setInterval> | null>(null);

  // Step 3 — Address
  const [address, setAddress] = useState<AddressForm>({
    savedId: "addr1", mode: "saved",
    name: "", phone: "", line1: "", line2: "", city: "", state: "", pincode: "",
  });
  const [addrErrors, setAddrErrors] = useState<FormErrors>({});

  // Step 4 — Schedule
  const [deliveryDate, setDeliveryDate] = useState(DAYS[0].date);
  const [slotId, setSlotId] = useState("");
  const [scheduleError, setScheduleError] = useState("");

  // Step 5 — Summary (just display)

  // Step 6 — Payment
  const [paymentMethod, setPaymentMethod] = useState("upi");
  const [upiId, setUpiId] = useState("");
  const [selectedUpiApp, setSelectedUpiApp] = useState("");
  const [cardNum, setCardNum] = useState("");
  const [cardExpiry, setCardExpiry] = useState("");
  const [cardCvv, setCardCvv] = useState("");
  const [cardName, setCardName] = useState("");
  const [bank, setBank] = useState("");
  const [payErrors, setPayErrors] = useState<FormErrors>({});
  const [placing, setPlacing] = useState(false);

  // Computed values
  const fuel = FUEL_TYPES.find((f) => f.id === fuelId)!;
  const subtotal = qty * fuel.pricePerL;
  const selectedSlot = DELIVERY_SLOTS.find((s) => s.id === slotId);
  const surgeFee = selectedSlot?.surge ? 200 : 0;
  const deliveryFee = subtotal >= 50000 ? 0 : 500;
  const gstRate = 0.18;
  const gstAmount = (subtotal + surgeFee + deliveryFee) * gstRate;
  const platformFee = 49;
  const total = subtotal + surgeFee + deliveryFee + gstAmount + platformFee;

  const selectedAddr =
    address.mode === "saved"
      ? SAVED_ADDRESSES.find((a) => a.id === address.savedId)
      : null;

  const selectedDay = DAYS.find((d) => d.date === deliveryDate);

  // ── Scroll to top on step change ──
  useEffect(() => {
    window.scrollTo({ top: 0, behavior: "smooth" });
  }, [step]);

  // ── Quantity controls ──
  const applyQty = useCallback(
    (val: number) => {
      const clamped = Math.max(fuel.minQty, Math.min(fuel.maxQty, val));
      setQty(clamped);
      setQtyInput(String(clamped));
      if (val < fuel.minQty) setQtyError(`Minimum order is ${fuel.minQty} ${fuel.unit}`);
      else if (val > fuel.maxQty) setQtyError(`Maximum order is ${fuel.maxQty.toLocaleString()} ${fuel.unit}`);
      else setQtyError("");
    },
    [fuel]
  );

  const qtySnapshot = useRef(qty);
  useEffect(() => { qtySnapshot.current = qty; }, [qty]);

  const startHold = (dir: 1 | -1) => {
    applyQty(qtySnapshot.current + dir * fuel.step);
    qtyRef.current = setInterval(() => {
      qtySnapshot.current = qtySnapshot.current + dir * fuel.step;
      applyQty(qtySnapshot.current);
    }, 150);
  };
  const stopHold = () => {
    if (qtyRef.current) clearInterval(qtyRef.current);
  };

  // ── Validators ──
  const validateAddress = (): boolean => {
    if (address.mode === "saved") return true;
    const errs: FormErrors = {};
    if (!address.name.trim()) errs.name = "Contact name is required";
    if (!/^[6-9]\d{9}$/.test(address.phone)) errs.phone = "Enter a valid 10-digit mobile number";
    if (!address.line1.trim()) errs.line1 = "Address line 1 is required";
    if (!address.city.trim()) errs.city = "City is required";
    if (!address.state.trim()) errs.state = "State is required";
    if (!/^\d{6}$/.test(address.pincode)) errs.pincode = "Enter a valid 6-digit PIN code";
    setAddrErrors(errs);
    return Object.keys(errs).length === 0;
  };

  const validateSchedule = (): boolean => {
    if (!slotId) { setScheduleError("Please select a delivery time slot"); return false; }
    setScheduleError("");
    return true;
  };

  const validatePayment = (): boolean => {
    const errs: FormErrors = {};
    if (paymentMethod === "upi" && !selectedUpiApp && !upiId)
      errs.upi = "Select a UPI app or enter your UPI ID";
    if (paymentMethod === "upi" && upiId && !/^[\w.\-_]{3,}@[a-z]+$/.test(upiId))
      errs.upiId = "Enter a valid UPI ID (e.g. name@upi)";
    if (paymentMethod === "card") {
      if (cardNum.replace(/\s/g, "").length !== 16) errs.cardNum = "Enter a valid 16-digit card number";
      if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) errs.cardExpiry = "Enter expiry as MM/YY";
      if (!/^\d{3,4}$/.test(cardCvv)) errs.cardCvv = "Enter 3 or 4 digit CVV";
      if (!cardName.trim()) errs.cardName = "Enter cardholder name";
    }
    if (paymentMethod === "netbank" && !bank) errs.bank = "Please select your bank";
    setPayErrors(errs);
    return Object.keys(errs).length === 0;
  };

  // ── Navigation ──
  const goNext = () => {
    if (step === 2 && (qtyError || qty < fuel.minQty)) { applyQty(qty); return; }
    if (step === 3 && !validateAddress()) return;
    if (step === 4 && !validateSchedule()) return;
    if (step === 6) { handlePlaceOrder(); return; }
    setStep((s) => s + 1);
  };

  const handlePlaceOrder = () => {
    if (!validatePayment()) return;
    setPlacing(true);
    setTimeout(() => {
      setPlacing(false);
      setConfirmed(true);
    }, 2000);
  };

  // ── Field helper ──
  const FieldError = ({ msg }: { msg?: string }) =>
    msg ? (
      <p className="mt-1.5 flex items-center gap-1 text-xs text-red-500">
        <AlertCircle className="w-3.5 h-3.5 flex-shrink-0" /> {msg}
      </p>
    ) : null;

  // ─────────────────────────── CONFIRMED SCREEN ────────────────────────────
  if (confirmed) {
    return (
      <div className="min-h-screen bg-[#fafbfa] flex flex-col">
        <Navbar />
        <main className="flex-1 flex items-center justify-center px-4 py-16">
          <div className="text-center max-w-md w-full">
            {/* Animated check */}
            <div className="relative mx-auto mb-8 w-28 h-28">
              <div className="absolute inset-0 rounded-full bg-[#155c32]/10 animate-ping" />
              <div className="relative w-28 h-28 rounded-full bg-gradient-to-br from-[#155c32] to-[#33b248] flex items-center justify-center shadow-2xl shadow-[#155c32]/30">
                <CheckCircle2 className="w-14 h-14 text-white" strokeWidth={1.5} />
              </div>
            </div>

            <h1 className="text-3xl font-extrabold text-[#1a1a1a] mb-2">Order Confirmed! 🎉</h1>
            <p className="text-[#666] text-sm mb-6 leading-relaxed">
              Your fuel delivery is booked. You&apos;ll receive an SMS &amp; WhatsApp update within 15 minutes.
            </p>

            {/* Order card */}
            <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 mb-6 text-left shadow-sm">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <p className="text-xs text-[#999] font-medium">ORDER ID</p>
                  <p className="font-bold text-[#1a1a1a] text-lg tracking-wider">{orderId}</p>
                </div>
                <span className="px-3 py-1.5 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                  Confirmed
                </span>
              </div>

              <div className="space-y-3 text-sm border-t border-[#f0f0f0] pt-4">
                <div className="flex justify-between">
                  <span className="text-[#777]">Fuel</span>
                  <span className="font-semibold">{fuel.label} · {qty.toLocaleString()} {fuel.unit}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-[#777]">Amount Paid</span>
                  <span className="font-bold text-[#155c32]">{fmt(total)}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-[#777]">Delivery</span>
                  <span className="font-semibold">{selectedDay?.label}, {selectedSlot?.time}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-[#777]">Payment</span>
                  <span className="font-semibold capitalize">{PAYMENT_METHODS.find((p) => p.id === paymentMethod)?.label}</span>
                </div>
              </div>
            </div>

            {/* Trust */}
            <div className="flex justify-center gap-6 text-xs text-[#888] mb-8">
              {["Certified Vendor", "GST Invoice", "Live Tracking"].map((t) => (
                <span key={t} className="flex items-center gap-1.5">
                  <BadgeCheck className="w-3.5 h-3.5 text-[#155c32]" /> {t}
                </span>
              ))}
            </div>

            <div className="flex gap-3">
              <Link href="/"
                className="flex-1 h-11 rounded-xl border border-[#e7ece8] text-sm font-semibold text-[#555] hover:border-[#155c32] hover:text-[#155c32] transition flex items-center justify-center">
                Back to Home
              </Link>
              <button onClick={() => { setStep(1); setConfirmed(false); setSlotId(""); }}
                className="flex-1 h-11 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] transition flex items-center justify-center gap-2">
                <RotateCcw className="w-4 h-4" /> New Order
              </button>
            </div>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  // ─────────────────────────── MAIN LAYOUT ─────────────────────────────────
  return (
    <div className="min-h-screen bg-[#f3f5f4] flex flex-col">
      <Navbar />

      {/* ── Top stepper bar ── */}
      <div className="sticky top-[80px] z-40 bg-white border-b border-[#e7ece8] shadow-sm">
        <div className="max-w-5xl mx-auto px-4 py-3">
          <div className="flex items-center justify-between">
            {STEPS.map((s, i) => {
              const done = step > s.id;
              const active = step === s.id;
              const Icon = s.icon;
              return (
                <div key={s.id} className="flex items-center flex-1">
                  <button
                    onClick={() => done && setStep(s.id)}
                    disabled={!done}
                    className="flex flex-col items-center gap-1 group disabled:cursor-default"
                  >
                    <div className={`w-8 h-8 rounded-full flex items-center justify-center transition-all duration-200 ${
                      done ? "bg-[#155c32] text-white shadow-md shadow-[#155c32]/30"
                           : active ? "bg-[#155c32] text-white ring-4 ring-[#155c32]/20"
                                    : "bg-[#f0f0f0] text-[#bbb]"
                    }`}>
                      {done ? <CheckCircle2 className="w-4 h-4" /> : <Icon className="w-4 h-4" />}
                    </div>
                    <span className={`text-[10px] font-semibold hidden sm:block ${
                      active ? "text-[#155c32]" : done ? "text-[#555]" : "text-[#bbb]"
                    }`}>{s.label}</span>
                  </button>
                  {i < STEPS.length - 1 && (
                    <div className={`flex-1 h-0.5 mx-1 sm:mx-2 rounded-full transition-all duration-300 ${done ? "bg-[#155c32]" : "bg-[#e7ece8]"}`} />
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </div>

      <main className="flex-1 py-8 px-4">
        <div className="max-w-5xl mx-auto">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {/* ═══════════════════════ LEFT: STEP CONTENT ═══════════════════════ */}
            <div className="lg:col-span-2">

              {/* ─── STEP 1: FUEL TYPE ─── */}
              {step === 1 && (
                <div className="space-y-4">
                  <StepHeader step={1} title="Choose Fuel Type" sub="Select the fuel your site requires" />
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {FUEL_TYPES.map((f) => (
                      <button
                        key={f.id}
                        type="button"
                        disabled={!f.available}
                        onClick={() => { setFuelId(f.id); applyQty(f.minQty * 5); }}
                        className={`relative text-left rounded-2xl border-2 p-5 transition-all duration-200 group focus:outline-none ${
                          fuelId === f.id
                            ? "border-[#155c32] bg-[#155c32]/5 shadow-lg shadow-[#155c32]/10"
                            : f.available
                              ? "border-[#e7ece8] bg-white hover:border-[#155c32]/40 hover:shadow-md"
                              : "border-[#e7ece8] bg-[#fafafa] opacity-50 cursor-not-allowed"
                        }`}
                      >
                        {/* Tag */}
                        <span className={`absolute top-3 right-3 px-2 py-0.5 rounded-full text-[10px] font-bold ${f.tagColor}`}>
                          {f.tag}
                        </span>

                        {/* Gradient icon blob */}
                        <div className={`w-12 h-12 rounded-xl bg-gradient-to-br ${f.gradient} flex items-center justify-center mb-4 text-2xl shadow-md`}>
                          {f.icon}
                        </div>

                        <h3 className="font-bold text-[#1a1a1a] text-base mb-1">{f.label}</h3>
                        <p className="text-xs text-[#777] leading-snug mb-3">{f.desc}</p>

                        <div className="flex items-end justify-between">
                          <div>
                            <p className="text-lg font-extrabold text-[#155c32]">₹{f.pricePerL}</p>
                            <p className="text-[10px] text-[#aaa]">{f.unitLabel}</p>
                          </div>
                          <div className="text-right">
                            <p className="text-[10px] text-[#aaa]">Min. order</p>
                            <p className="text-xs font-bold text-[#555]">{f.minQty} {f.unit}</p>
                          </div>
                        </div>

                        {fuelId === f.id && (
                          <div className="absolute top-3 left-3 w-5 h-5 rounded-full bg-[#155c32] flex items-center justify-center">
                            <CheckCircle2 className="w-3.5 h-3.5 text-white" />
                          </div>
                        )}
                      </button>
                    ))}
                  </div>
                </div>
              )}

              {/* ─── STEP 2: QUANTITY ─── */}
              {step === 2 && (
                <div className="space-y-4">
                  <StepHeader step={2} title="Set Quantity" sub={`Minimum ${fuel.minQty} ${fuel.unit} · Maximum ${fuel.maxQty.toLocaleString()} ${fuel.unit}`} />

                  <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                    {/* Fuel summary strip */}
                    <div className={`flex items-center gap-3 p-3 rounded-xl bg-gradient-to-r ${fuel.gradient} mb-6`}>
                      <span className="text-2xl">{fuel.icon}</span>
                      <div className="flex-1">
                        <p className="font-bold text-white text-sm">{fuel.label}</p>
                        <p className="text-white/80 text-xs">₹{fuel.pricePerL} {fuel.unitLabel}</p>
                      </div>
                      <button onClick={() => setStep(1)} className="text-white/70 hover:text-white text-xs font-semibold transition">Change →</button>
                    </div>

                    {/* ± Quantity control */}
                    <div className="flex items-center gap-4 mb-4">
                      <button
                        type="button"
                        onMouseDown={() => startHold(-1)}
                        onMouseUp={stopHold}
                        onMouseLeave={stopHold}
                        onTouchStart={() => startHold(-1)}
                        onTouchEnd={stopHold}
                        disabled={qty <= fuel.minQty}
                        className="w-14 h-14 rounded-2xl bg-[#f4f8f5] border-2 border-[#e7ece8] flex items-center justify-center text-[#155c32] hover:bg-[#155c32] hover:text-white hover:border-[#155c32] transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed text-2xl font-bold select-none"
                      >
                        <Minus className="w-5 h-5" />
                      </button>

                      {/* Manual input */}
                      <div className="flex-1 text-center">
                        <div className="relative">
                          <input
                            type="number"
                            value={qtyInput}
                            min={fuel.minQty}
                            max={fuel.maxQty}
                            step={fuel.step}
                            onChange={(e) => {
                              setQtyInput(e.target.value);
                              const v = parseInt(e.target.value);
                              if (!isNaN(v)) applyQty(v);
                            }}
                            onBlur={() => applyQty(parseInt(qtyInput) || fuel.minQty)}
                            className="w-full h-16 text-center text-3xl font-extrabold text-[#1a1a1a] border-2 border-[#e7ece8] rounded-2xl bg-[#fafbfa] focus:outline-none focus:border-[#155c32] focus:ring-4 focus:ring-[#155c32]/10 transition [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                          />
                          <span className="absolute right-4 top-1/2 -translate-y-1/2 text-sm font-bold text-[#aaa]">{fuel.unit}</span>
                        </div>
                        <p className="text-xs text-[#aaa] mt-1.5">Enter quantity manually or use ±</p>
                      </div>

                      <button
                        type="button"
                        onMouseDown={() => startHold(1)}
                        onMouseUp={stopHold}
                        onMouseLeave={stopHold}
                        onTouchStart={() => startHold(1)}
                        onTouchEnd={stopHold}
                        disabled={qty >= fuel.maxQty}
                        className="w-14 h-14 rounded-2xl bg-[#f4f8f5] border-2 border-[#e7ece8] flex items-center justify-center text-[#155c32] hover:bg-[#155c32] hover:text-white hover:border-[#155c32] transition-all duration-150 disabled:opacity-40 disabled:cursor-not-allowed select-none"
                      >
                        <Plus className="w-5 h-5" />
                      </button>
                    </div>

                    {/* Error / range */}
                    {qtyError ? (
                      <div className="flex items-center gap-2 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs mb-4">
                        <AlertCircle className="w-4 h-4 flex-shrink-0" /> {qtyError}
                      </div>
                    ) : (
                      <div className="flex items-center gap-2 p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] text-[#555] text-xs mb-4">
                        <Info className="w-4 h-4 flex-shrink-0 text-[#155c32]" />
                        Valid range: {fuel.minQty} – {fuel.maxQty.toLocaleString()} {fuel.unit} (step: {fuel.step} {fuel.unit})
                      </div>
                    )}

                    {/* Quick-select presets */}
                    <p className="text-xs font-semibold text-[#777] mb-2">Quick Select</p>
                    <div className="flex flex-wrap gap-2 mb-6">
                      {[fuel.minQty, fuel.minQty * 2.5, fuel.minQty * 5, fuel.minQty * 10, fuel.minQty * 25, fuel.minQty * 50]
                        .filter((v) => v <= fuel.maxQty)
                        .map((v) => (
                          <button
                            key={v} type="button"
                            onClick={() => applyQty(v)}
                            className={`px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all duration-150 ${
                              qty === v
                                ? "bg-[#155c32] border-[#155c32] text-white"
                                : "bg-white border-[#e7ece8] text-[#555] hover:border-[#155c32]/50"
                            }`}
                          >
                            {v.toLocaleString()} {fuel.unit}
                          </button>
                        ))}
                    </div>

                    {/* Live price preview */}
                    <div className="rounded-xl bg-gradient-to-r from-[#155c32] to-[#0d3a1f] p-4 text-white">
                      <div className="flex justify-between text-sm mb-2">
                        <span className="text-white/70">Subtotal (excl. taxes)</span>
                        <span className="font-bold">{fmt(subtotal)}</span>
                      </div>
                      <div className="flex justify-between text-xs text-white/60">
                        <span>{qty.toLocaleString()} {fuel.unit} × ₹{fuel.pricePerL}</span>
                        <span>GST &amp; fees added at checkout</span>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* ─── STEP 3: ADDRESS ─── */}
              {step === 3 && (
                <div className="space-y-4">
                  <StepHeader step={3} title="Delivery Address" sub="Where should we deliver your fuel?" />

                  {/* Mode toggle */}
                  <div className="flex rounded-xl overflow-hidden border border-[#e7ece8] bg-white">
                    {(["saved", "new"] as const).map((m) => (
                      <button
                        key={m} type="button"
                        onClick={() => setAddress((a) => ({ ...a, mode: m }))}
                        className={`flex-1 py-3 text-sm font-semibold transition-all ${
                          address.mode === m
                            ? "bg-[#155c32] text-white"
                            : "text-[#777] hover:bg-[#f4f8f5]"
                        }`}
                      >
                        {m === "saved" ? "Saved Addresses" : "+ Add New Address"}
                      </button>
                    ))}
                  </div>

                  {address.mode === "saved" && (
                    <div className="space-y-3">
                      {SAVED_ADDRESSES.map((a) => {
                        const Icon = a.icon;
                        return (
                          <button
                            key={a.id} type="button"
                            onClick={() => setAddress((f) => ({ ...f, savedId: a.id }))}
                            className={`w-full text-left rounded-2xl border-2 p-4 transition-all duration-150 ${
                              address.savedId === a.id
                                ? "border-[#155c32] bg-[#155c32]/5"
                                : "border-[#e7ece8] bg-white hover:border-[#155c32]/30"
                            }`}
                          >
                            <div className="flex items-start gap-3">
                              <div className="w-9 h-9 rounded-xl bg-[#155c32]/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <Icon className="w-4 h-4 text-[#155c32]" />
                              </div>
                              <div className="flex-1">
                                <div className="flex items-center gap-2 mb-0.5">
                                  <span className="font-bold text-sm text-[#1a1a1a]">{a.tag}</span>
                                  {address.savedId === a.id && (
                                    <span className="px-2 py-0.5 rounded-full bg-[#155c32] text-white text-[10px] font-bold">Selected</span>
                                  )}
                                </div>
                                <p className="text-xs text-[#555] font-medium">{a.name} · {a.phone}</p>
                                <p className="text-xs text-[#888] mt-0.5">{a.line1}, {a.city}, {a.state} – {a.pincode}</p>
                              </div>
                            </div>
                          </button>
                        );
                      })}
                    </div>
                  )}

                  {address.mode === "new" && (
                    <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 space-y-4 shadow-sm">
                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                          <Label>Contact Person *</Label>
                          <InputWrap icon={<User className="w-4 h-4" />} error={addrErrors.name}>
                            <input id="a-name" type="text" placeholder="Full name"
                              value={address.name} onChange={(e) => setAddress((f) => ({ ...f, name: e.target.value }))}
                              className={inputCls(addrErrors.name)} />
                          </InputWrap>
                          <FieldError msg={addrErrors.name} />
                        </div>
                        <div>
                          <Label>Mobile Number *</Label>
                          <InputWrap icon={<Phone className="w-4 h-4" />} error={addrErrors.phone}>
                            <input id="a-phone" type="tel" placeholder="10-digit mobile" maxLength={10}
                              value={address.phone} onChange={(e) => setAddress((f) => ({ ...f, phone: e.target.value.replace(/\D/g, "") }))}
                              className={inputCls(addrErrors.phone)} />
                          </InputWrap>
                          <FieldError msg={addrErrors.phone} />
                        </div>
                      </div>
                      <div>
                        <Label>Address Line 1 *</Label>
                        <InputWrap icon={<MapPin className="w-4 h-4" />} error={addrErrors.line1}>
                          <input id="a-line1" type="text" placeholder="Plot/Building, Street, Area"
                            value={address.line1} onChange={(e) => setAddress((f) => ({ ...f, line1: e.target.value }))}
                            className={inputCls(addrErrors.line1)} />
                        </InputWrap>
                        <FieldError msg={addrErrors.line1} />
                      </div>
                      <div>
                        <Label>Address Line 2</Label>
                        <input type="text" placeholder="Landmark, Near, etc. (optional)"
                          value={address.line2} onChange={(e) => setAddress((f) => ({ ...f, line2: e.target.value }))}
                          className="w-full h-11 px-4 rounded-xl border border-[#e7ece8] bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:border-[#155c32] transition" />
                      </div>
                      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                          <Label>City *</Label>
                          <input type="text" placeholder="City"
                            value={address.city} onChange={(e) => setAddress((f) => ({ ...f, city: e.target.value }))}
                            className={inputCls(addrErrors.city) + " w-full"} />
                          <FieldError msg={addrErrors.city} />
                        </div>
                        <div>
                          <Label>State *</Label>
                          <select value={address.state} onChange={(e) => setAddress((f) => ({ ...f, state: e.target.value }))}
                            className={inputCls(addrErrors.state) + " w-full"}>
                            <option value="">Select State</option>
                            {["Delhi", "Uttar Pradesh", "Maharashtra", "Haryana", "Rajasthan", "Punjab", "Gujarat"].map((s) => (
                              <option key={s} value={s}>{s}</option>
                            ))}
                          </select>
                          <FieldError msg={addrErrors.state} />
                        </div>
                        <div>
                          <Label>PIN Code *</Label>
                          <input type="text" placeholder="6-digit PIN" maxLength={6}
                            value={address.pincode} onChange={(e) => setAddress((f) => ({ ...f, pincode: e.target.value.replace(/\D/g, "") }))}
                            className={inputCls(addrErrors.pincode) + " w-full"} />
                          <FieldError msg={addrErrors.pincode} />
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              )}

              {/* ─── STEP 4: SCHEDULE ─── */}
              {step === 4 && (
                <div className="space-y-4">
                  <StepHeader step={4} title="Schedule Delivery" sub="Pick a convenient date and time slot" />

                  {/* Date row */}
                  <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                    <p className="text-sm font-bold text-[#1a1a1a] mb-4">Delivery Date</p>
                    <div className="flex gap-2 overflow-x-auto pb-1">
                      {DAYS.map((d) => (
                        <button
                          key={d.date} type="button"
                          disabled={d.disabled}
                          onClick={() => { setDeliveryDate(d.date); setSlotId(""); }}
                          className={`flex-shrink-0 flex flex-col items-center px-4 py-3 rounded-xl border-2 transition-all duration-150 min-w-[70px] ${
                            deliveryDate === d.date
                              ? "border-[#155c32] bg-[#155c32] text-white"
                              : d.disabled
                                ? "border-[#e7ece8] text-[#ccc] cursor-not-allowed"
                                : "border-[#e7ece8] text-[#555] hover:border-[#155c32]/40 bg-white"
                          }`}
                        >
                          <span className="text-[10px] font-bold uppercase tracking-wider">{d.label}</span>
                          <span className="text-sm font-bold mt-0.5">{d.sub}</span>
                        </button>
                      ))}
                    </div>
                  </div>

                  {/* Time slots */}
                  <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                    <p className="text-sm font-bold text-[#1a1a1a] mb-4">Time Slot</p>
                    <div className="space-y-2">
                      {DELIVERY_SLOTS.map((s) => (
                        <button
                          key={s.id} type="button"
                          disabled={!s.available}
                          onClick={() => { setSlotId(s.id); setScheduleError(""); }}
                          className={`w-full flex items-center gap-3 p-4 rounded-xl border-2 transition-all duration-150 text-left ${
                            !s.available
                              ? "border-[#e7ece8] bg-[#fafafa] opacity-50 cursor-not-allowed"
                              : slotId === s.id
                                ? "border-[#155c32] bg-[#155c32]/5"
                                : "border-[#e7ece8] bg-white hover:border-[#155c32]/40"
                          }`}
                        >
                          <div className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${
                            slotId === s.id ? "bg-[#155c32]" : "bg-[#f4f8f5]"
                          }`}>
                            <Clock className={`w-4 h-4 ${slotId === s.id ? "text-white" : "text-[#155c32]"}`} />
                          </div>
                          <div className="flex-1">
                            <div className="flex items-center gap-2">
                              <span className="font-semibold text-sm text-[#1a1a1a]">{s.label}</span>
                              {s.popular && <span className="px-2 py-0.5 rounded-full bg-orange-100 text-orange-600 text-[10px] font-bold">Popular</span>}
                              {!s.available && <span className="px-2 py-0.5 rounded-full bg-[#f0f0f0] text-[#aaa] text-[10px] font-bold">Unavailable</span>}
                            </div>
                            <p className="text-xs text-[#777] mt-0.5">{s.time}</p>
                          </div>
                          {s.surge && s.available && (
                            <span className="text-xs font-bold text-orange-500 flex-shrink-0">{s.surgeLabel} surge</span>
                          )}
                          {slotId === s.id && (
                            <CircleCheck className="w-5 h-5 text-[#155c32] flex-shrink-0" />
                          )}
                        </button>
                      ))}
                    </div>
                    {scheduleError && (
                      <div className="flex items-center gap-2 mt-3 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">
                        <AlertCircle className="w-4 h-4 flex-shrink-0" /> {scheduleError}
                      </div>
                    )}
                  </div>
                </div>
              )}

              {/* ─── STEP 5: ORDER SUMMARY ─── */}
              {step === 5 && (
                <div className="space-y-4">
                  <StepHeader step={5} title="Review Your Order" sub="Verify all details before payment" />

                  {/* Fuel + Address + Slot mini-cards */}
                  {[
                    {
                      icon: <span className="text-xl">{fuel.icon}</span>,
                      title: "Fuel",
                      lines: [`${fuel.label}`, `${qty.toLocaleString()} ${fuel.unit} · ₹${fuel.pricePerL} ${fuel.unitLabel}`],
                      onEdit: () => setStep(1),
                    },
                    {
                      icon: <MapPin className="w-5 h-5 text-[#155c32]" />,
                      title: "Delivery Address",
                      lines: selectedAddr
                        ? [`${selectedAddr.name} · ${selectedAddr.phone}`, `${selectedAddr.line1}, ${selectedAddr.city} – ${selectedAddr.pincode}`]
                        : [`${address.name} · ${address.phone}`, `${address.line1}, ${address.city}, ${address.state} – ${address.pincode}`],
                      onEdit: () => setStep(3),
                    },
                    {
                      icon: <Calendar className="w-5 h-5 text-[#155c32]" />,
                      title: "Delivery Schedule",
                      lines: [`${selectedDay?.label} · ${selectedDay?.sub}`, selectedSlot?.time ?? ""],
                      onEdit: () => setStep(4),
                    },
                  ].map((card) => (
                    <div key={card.title} className="bg-white rounded-2xl border border-[#e7ece8] p-4 flex items-start gap-3 shadow-sm">
                      <div className="w-10 h-10 rounded-xl bg-[#155c32]/8 flex items-center justify-center flex-shrink-0">{card.icon}</div>
                      <div className="flex-1">
                        <p className="text-xs text-[#aaa] font-semibold mb-0.5">{card.title}</p>
                        {card.lines.map((l, i) => <p key={i} className={`${i === 0 ? "font-semibold text-sm text-[#1a1a1a]" : "text-xs text-[#777]"}`}>{l}</p>)}
                      </div>
                      <button onClick={card.onEdit} className="text-xs font-semibold text-[#155c32] hover:underline flex-shrink-0">Edit</button>
                    </div>
                  ))}

                  {/* Bill breakdown */}
                  <div className="bg-white rounded-2xl border border-[#e7ece8] p-6 shadow-sm">
                    <p className="font-bold text-[#1a1a1a] mb-4 flex items-center gap-2">
                      <Receipt className="w-4 h-4 text-[#155c32]" /> Bill Details
                    </p>
                    <div className="space-y-3 text-sm">
                      {[
                        { label: `Fuel Cost (${qty.toLocaleString()} ${fuel.unit} × ₹${fuel.pricePerL})`, val: fmt(subtotal) },
                        { label: "Delivery Charge",   val: deliveryFee === 0 ? "Free" : fmt(deliveryFee), green: deliveryFee === 0 },
                        ...(surgeFee > 0 ? [{ label: "Surge Fee (Evening Slot)", val: fmt(surgeFee), orange: true }] : []),
                        { label: `GST (18%)`,         val: fmt(gstAmount) },
                        { label: "Platform Fee",      val: fmt(platformFee) },
                      ].map((r) => (
                        <div key={r.label} className="flex justify-between text-[#555]">
                          <span>{r.label}</span>
                          <span className={`font-semibold ${"green" in r && r.green ? "text-green-600" : "orange" in r && r.orange ? "text-orange-500" : "text-[#1a1a1a]"}`}>{r.val}</span>
                        </div>
                      ))}
                      <div className="border-t-2 border-dashed border-[#e7ece8] pt-3 flex justify-between font-extrabold text-base">
                        <span className="text-[#1a1a1a]">Grand Total</span>
                        <span className="text-[#155c32]">{fmt(total)}</span>
                      </div>
                    </div>

                    {deliveryFee === 0 && (
                      <div className="mt-4 flex items-center gap-2 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs font-semibold">
                        <Zap className="w-3.5 h-3.5" /> Free delivery applied on orders above ₹50,000!
                      </div>
                    )}
                  </div>

                  {/* Savings callout */}
                  <div className="flex items-center gap-3 p-4 rounded-2xl bg-[#155c32]/8 border border-[#155c32]/20">
                    <ShieldCheck className="w-5 h-5 text-[#155c32] flex-shrink-0" />
                    <p className="text-xs text-[#155c32] font-medium">
                      Certified vendor · GST invoice emailed · Quantity verified at delivery
                    </p>
                  </div>
                </div>
              )}

              {/* ─── STEP 6: PAYMENT ─── */}
              {step === 6 && (
                <div className="space-y-4">
                  <StepHeader step={6} title="Payment" sub="Choose how you'd like to pay" />

                  {/* Method tabs */}
                  <div className="bg-white rounded-2xl border border-[#e7ece8] overflow-hidden shadow-sm">
                    {PAYMENT_METHODS.map((pm) => {
                      const Icon = pm.icon;
                      return (
                        <div key={pm.id}>
                          <button
                            type="button"
                            onClick={() => { setPaymentMethod(pm.id); setPayErrors({}); }}
                            className={`w-full flex items-center gap-4 px-5 py-4 text-left border-b border-[#f0f0f0] last:border-b-0 transition-colors ${
                              paymentMethod === pm.id ? "bg-[#155c32]/5" : "hover:bg-[#fafbfa]"
                            }`}
                          >
                            <div className={`w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 ${
                              paymentMethod === pm.id ? "bg-[#155c32]" : "bg-[#f4f4f4]"
                            }`}>
                              <Icon className={`w-5 h-5 ${paymentMethod === pm.id ? "text-white" : "text-[#555]"}`} />
                            </div>
                            <div className="flex-1">
                              <div className="flex items-center gap-2">
                                <span className={`font-semibold text-sm ${paymentMethod === pm.id ? "text-[#155c32]" : "text-[#1a1a1a]"}`}>{pm.label}</span>
                                {pm.popular && <span className="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">Recommended</span>}
                              </div>
                              <p className="text-xs text-[#aaa]">{pm.sub}</p>
                            </div>
                            <div className={`w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${
                              paymentMethod === pm.id ? "border-[#155c32] bg-[#155c32]" : "border-[#ccc]"
                            }`}>
                              {paymentMethod === pm.id && <div className="w-2 h-2 rounded-full bg-white" />}
                            </div>
                          </button>

                          {/* Expanded sub-form */}
                          {paymentMethod === pm.id && (
                            <div className="px-5 pb-5 pt-2 bg-[#fafbfa] border-b border-[#f0f0f0] last:border-b-0">
                              {pm.id === "upi" && (
                                <div className="space-y-3">
                                  <p className="text-xs font-semibold text-[#777] mb-2">Pay using UPI App</p>
                                  <div className="grid grid-cols-2 gap-2">
                                    {UPI_APPS.map((app) => (
                                      <button
                                        key={app.id} type="button"
                                        onClick={() => { setSelectedUpiApp(app.id); setUpiId(""); setPayErrors({}); }}
                                        className={`flex items-center gap-2.5 p-3 rounded-xl border-2 transition-all ${
                                          selectedUpiApp === app.id ? "border-[#155c32] bg-[#155c32]/5" : "border-[#e7ece8] bg-white hover:border-[#155c32]/30"
                                        }`}
                                      >
                                        <div className={`w-7 h-7 rounded-lg ${app.color} flex items-center justify-center text-white text-xs font-bold`}>
                                          {app.label[0]}
                                        </div>
                                        <span className="text-xs font-semibold text-[#1a1a1a]">{app.label}</span>
                                      </button>
                                    ))}
                                  </div>
                                  <div className="relative flex items-center gap-2">
                                    <div className="flex-1 h-px bg-[#e7ece8]" />
                                    <span className="text-xs text-[#aaa] font-medium">or enter UPI ID</span>
                                    <div className="flex-1 h-px bg-[#e7ece8]" />
                                  </div>
                                  <input type="text" placeholder="yourname@upi"
                                    value={upiId}
                                    onChange={(e) => { setUpiId(e.target.value); setSelectedUpiApp(""); setPayErrors({}); }}
                                    className={inputCls(payErrors.upiId)} />
                                  <FieldError msg={payErrors.upi || payErrors.upiId} />
                                </div>
                              )}

                              {pm.id === "card" && (
                                <div className="space-y-3">
                                  <div>
                                    <Label>Card Number *</Label>
                                    <input type="text" placeholder="1234 5678 9012 3456" maxLength={19}
                                      value={cardNum}
                                      onChange={(e) => {
                                        const v = e.target.value.replace(/\D/g, "").slice(0, 16);
                                        setCardNum(v.replace(/(.{4})/g, "$1 ").trim());
                                        setPayErrors({});
                                      }}
                                      className={inputCls(payErrors.cardNum)} />
                                    <FieldError msg={payErrors.cardNum} />
                                  </div>
                                  <div>
                                    <Label>Cardholder Name *</Label>
                                    <input type="text" placeholder="Name on card"
                                      value={cardName} onChange={(e) => { setCardName(e.target.value); setPayErrors({}); }}
                                      className={inputCls(payErrors.cardName)} />
                                    <FieldError msg={payErrors.cardName} />
                                  </div>
                                  <div className="grid grid-cols-2 gap-3">
                                    <div>
                                      <Label>Expiry (MM/YY) *</Label>
                                      <input type="text" placeholder="MM/YY" maxLength={5}
                                        value={cardExpiry}
                                        onChange={(e) => {
                                          let v = e.target.value.replace(/\D/g, "").slice(0, 4);
                                          if (v.length > 2) v = v.slice(0, 2) + "/" + v.slice(2);
                                          setCardExpiry(v); setPayErrors({});
                                        }}
                                        className={inputCls(payErrors.cardExpiry)} />
                                      <FieldError msg={payErrors.cardExpiry} />
                                    </div>
                                    <div>
                                      <Label>CVV *</Label>
                                      <input type="password" placeholder="•••" maxLength={4}
                                        value={cardCvv} onChange={(e) => { setCardCvv(e.target.value.replace(/\D/g, "")); setPayErrors({}); }}
                                        className={inputCls(payErrors.cardCvv)} />
                                      <FieldError msg={payErrors.cardCvv} />
                                    </div>
                                  </div>
                                </div>
                              )}

                              {pm.id === "netbank" && (
                                <div>
                                  <Label>Select Bank *</Label>
                                  <select value={bank} onChange={(e) => { setBank(e.target.value); setPayErrors({}); }}
                                    className={inputCls(payErrors.bank)}>
                                    <option value="">Select your bank</option>
                                    {["SBI", "HDFC Bank", "ICICI Bank", "Axis Bank", "Kotak Mahindra", "Punjab National Bank", "Bank of Baroda", "Canara Bank"].map((b) => (
                                      <option key={b} value={b}>{b}</option>
                                    ))}
                                  </select>
                                  <FieldError msg={payErrors.bank} />
                                </div>
                              )}

                              {pm.id === "cod" && (
                                <div className="flex items-start gap-2 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs">
                                  <Info className="w-4 h-4 flex-shrink-0 mt-0.5" />
                                  Cash or cheque accepted at the time of delivery. Please keep the exact amount ready.
                                </div>
                              )}
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </div>

                  {/* Security badge */}
                  <div className="flex items-center justify-center gap-2 text-xs text-[#888]">
                    <ShieldCheck className="w-4 h-4 text-[#155c32]" />
                    256-bit SSL encrypted · PCI-DSS compliant · Your data is safe
                  </div>
                </div>
              )}

              {/* ─── NAVIGATION BUTTONS ─── */}
              <div className="flex gap-3 mt-6">
                {step > 1 && (
                  <button
                    type="button"
                    onClick={() => setStep((s) => s - 1)}
                    className="flex items-center gap-2 h-12 px-6 rounded-xl border-2 border-[#e7ece8] text-sm font-semibold text-[#555] hover:border-[#155c32] hover:text-[#155c32] transition-all duration-200"
                  >
                    <ArrowLeft className="w-4 h-4" /> Back
                  </button>
                )}
                <button
                  type="button"
                  onClick={goNext}
                  disabled={placing}
                  className="flex-1 flex items-center justify-center gap-2 h-12 rounded-xl bg-[#155c32] text-white font-bold text-sm hover:bg-[#0d3a1f] hover:shadow-xl hover:shadow-[#155c32]/25 transition-all duration-200 hover:-translate-y-px disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  {placing ? (
                    <>
                      <div className="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                      Processing…
                    </>
                  ) : step === 6 ? (
                    <><ShieldCheck className="w-4 h-4" /> Pay {fmt(total)} Securely</>
                  ) : (
                    <>Continue <ArrowRight className="w-4 h-4" /></>
                  )}
                </button>
              </div>
            </div>

            {/* ═══════════════════════ RIGHT: ORDER SUMMARY CARD ═══════════════════════ */}
            <div className="lg:col-span-1">
              <div className="sticky top-[140px] space-y-4">
                {/* Mini summary card */}
                <div className="bg-white rounded-2xl border border-[#e7ece8] p-5 shadow-sm">
                  <h3 className="font-bold text-[#1a1a1a] text-sm mb-4 flex items-center gap-2">
                    <Truck className="w-4 h-4 text-[#155c32]" /> Order Summary
                  </h3>

                  <div className={`flex items-center gap-2.5 p-3 rounded-xl bg-gradient-to-r ${fuel.gradient} mb-4`}>
                    <span className="text-xl">{fuel.icon}</span>
                    <div>
                      <p className="font-bold text-white text-xs">{fuel.label}</p>
                      <p className="text-white/80 text-[11px]">{qty.toLocaleString()} {fuel.unit}</p>
                    </div>
                  </div>

                  <div className="space-y-2.5 text-xs">
                    <SummaryRow label="Subtotal" val={fmt(subtotal)} />
                    <SummaryRow label="Delivery" val={deliveryFee === 0 ? "Free" : fmt(deliveryFee)} green={deliveryFee === 0} />
                    {surgeFee > 0 && <SummaryRow label="Surge" val={fmt(surgeFee)} orange />}
                    <SummaryRow label="GST (18%)" val={fmt(gstAmount)} />
                    <SummaryRow label="Platform Fee" val={fmt(platformFee)} />
                    <div className="border-t border-[#e7ece8] pt-2.5 flex justify-between font-extrabold text-sm">
                      <span className="text-[#1a1a1a]">Total</span>
                      <span className="text-[#155c32]">{fmt(total)}</span>
                    </div>
                  </div>
                </div>

                {/* Schedule preview */}
                {slotId && (
                  <div className="bg-white rounded-2xl border border-[#e7ece8] p-4 shadow-sm">
                    <p className="text-xs text-[#aaa] font-semibold mb-2">DELIVERY WINDOW</p>
                    <div className="flex items-center gap-2">
                      <Clock className="w-4 h-4 text-[#155c32]" />
                      <div>
                        <p className="text-sm font-bold text-[#1a1a1a]">{selectedDay?.label}, {selectedDay?.sub}</p>
                        <p className="text-xs text-[#777]">{selectedSlot?.time}</p>
                      </div>
                    </div>
                  </div>
                )}

                {/* Trust badges */}
                <div className="bg-white rounded-2xl border border-[#e7ece8] p-4 shadow-sm space-y-2.5">
                  {[
                    { icon: BadgeCheck, label: "Quality-Certified Vendors" },
                    { icon: ShieldCheck, label: "GST Invoice on Delivery" },
                    { icon: Star,        label: "ISO 9001:2015 Compliant" },
                    { icon: Zap,         label: "Same-Day Delivery Available" },
                  ].map(({ icon: Icon, label }) => (
                    <div key={label} className="flex items-center gap-2 text-xs text-[#666]">
                      <Icon className="w-3.5 h-3.5 text-[#155c32] flex-shrink-0" /> {label}
                    </div>
                  ))}
                </div>

                {/* Need help */}
                <div className="bg-[#155c32]/5 border border-[#155c32]/20 rounded-2xl p-4">
                  <p className="text-xs font-bold text-[#155c32] mb-1">Need help with your order?</p>
                  <p className="text-xs text-[#555]">Call us: <a href="tel:18001002003" className="font-semibold">1800-100-200</a></p>
                  <p className="text-xs text-[#555]">Mon–Sat · 9AM–9PM</p>
                </div>
              </div>
            </div>

          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
}

// ─── MICRO COMPONENTS ────────────────────────────────────────────────────────

function StepHeader({ step, title, sub }: { step: number; title: string; sub: string }) {
  return (
    <div className="mb-2">
      <div className="flex items-center gap-2 mb-1">
        <span className="w-6 h-6 rounded-full bg-[#155c32] text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
          {step}
        </span>
        <h2 className="text-lg font-extrabold text-[#1a1a1a]">{title}</h2>
      </div>
      <p className="text-sm text-[#777] ml-8">{sub}</p>
    </div>
  );
}

function SummaryRow({ label, val, green, orange }: { label: string; val: string; green?: boolean; orange?: boolean }) {
  return (
    <div className="flex justify-between text-[#666]">
      <span>{label}</span>
      <span className={`font-semibold ${green ? "text-green-600" : orange ? "text-orange-500" : "text-[#1a1a1a]"}`}>{val}</span>
    </div>
  );
}

function Label({ children }: { children: React.ReactNode }) {
  return <p className="text-sm font-semibold text-[#1a1a1a] mb-1.5">{children}</p>;
}

function InputWrap({ icon, children, error }: { icon: React.ReactNode; children: React.ReactNode; error?: string }) {
  return (
    <div className={`relative flex items-center rounded-xl border ${error ? "border-red-300" : "border-[#e7ece8]"} bg-[#fafbfa] focus-within:border-[#155c32] focus-within:ring-2 focus-within:ring-[#155c32]/10 transition`}>
      <span className="pl-3.5 text-[#aaa]">{icon}</span>
      {children}
    </div>
  );
}

const inputCls = (err?: string) =>
  `w-full h-11 px-4 rounded-xl border ${err ? "border-red-300 focus:border-red-400 focus:ring-red-100" : "border-[#e7ece8] focus:border-[#155c32] focus:ring-[#155c32]/10"} bg-[#fafbfa] text-sm text-[#1a1a1a] placeholder-[#bbb] focus:outline-none focus:ring-2 transition`;
