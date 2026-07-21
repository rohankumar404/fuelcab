"use client";

import React, { useState } from "react";
import Link from "next/link";
import {
  Building2,
  CheckCircle2,
  MapPin,
  Clock,
  ArrowRight,
  ShieldCheck,
  Package,
  Layers,
  ChevronRight,
  Sparkles,
  FileCheck,
  Award,
  ArrowLeft,
  FileText,
  ShoppingCart,
  PhoneCall,
  Mail,
  Download,
  Share2,
  Info,
  Check,
  SlidersHorizontal,
  X,
  Truck,
  Plus,
  Minus,
} from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { VendorListing } from "@/lib/marketplace-data";

interface Props {
  listing: VendorListing;
}

export default function ListingDetailClient({ listing }: Props) {
  // Gallery active image state
  const [activeImgIndex, setActiveImgIndex] = useState(0);

  // Commerce Mode State: Direct Order Quantity
  const [quantity, setQuantity] = useState(listing.min_order_quantity);
  const [addedToCart, setAddedToCart] = useState(false);
  const [cartLoading, setCartLoading] = useState(false);

  // RFQ Drawer Modal state for REQUEST_QUOTE mode
  const [rfqModalOpen, setRfqModalOpen] = useState(false);
  const [rfqSuccess, setRfqSuccess] = useState(false);
  const [rfqLoading, setRfqLoading] = useState(false);

  // RFQ Form inputs
  const [rfqForm, setRfqForm] = useState({
    name: "",
    company: "",
    phone: "",
    email: "",
    required_qty: listing.min_order_quantity,
    target_price: listing.base_price,
    delivery_pincode: "",
    notes: "",
  });

  // Calculate live pricing
  const subtotal = quantity * listing.base_price;
  const taxAmount = listing.tax_inclusive ? 0 : subtotal * (listing.tax_rate / 100);
  const grandTotal = subtotal + taxAmount;

  // Handle Add to Cart in DIRECT_ORDER mode
  const handleAddToCart = () => {
    setCartLoading(true);
    setTimeout(() => {
      setCartLoading(false);
      setAddedToCart(true);
      setTimeout(() => setAddedToCart(false), 4000);
    }, 800);
  };

  // Handle RFQ Form Submission in REQUEST_QUOTE mode
  const handleRfqSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setRfqLoading(true);
    setTimeout(() => {
      setRfqLoading(false);
      setRfqSuccess(true);
      setTimeout(() => {
        setRfqSuccess(false);
        setRfqModalOpen(false);
      }, 3500);
    }, 1200);
  };

  return (
    <div className="min-h-screen bg-[#fafbfa] text-[#1a1a1a] pb-20">

      {/* ── Top Breadcrumb Nav ──────────────────────────────────────────────── */}
      <div className="bg-white border-b border-[#e7ece8]">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-3">
          <nav className="flex items-center gap-2 text-xs font-semibold text-[#555555]">
            <Link href="/" className="hover:text-[#155c32] transition-colors">Home</Link>
            <ChevronRight className="w-3.5 h-3.5 text-gray-400" />
            <Link href="/marketplace" className="hover:text-[#155c32] transition-colors">Marketplace</Link>
            <ChevronRight className="w-3.5 h-3.5 text-gray-400" />
            <Link
              href={`/marketplace/${listing.category_slug}/${listing.marketplace_product_slug}`}
              className="hover:text-[#155c32] transition-colors capitalize"
            >
              {listing.marketplace_product_name}
            </Link>
            <ChevronRight className="w-3.5 h-3.5 text-gray-400" />
            <span className="text-[#155c32] font-bold line-clamp-1">{listing.listing_title}</span>
          </nav>
        </div>
      </div>

      {/* ── Main Detail Container ────────────────────────────────────────────── */}
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 pt-8 space-y-10">

        <div className="grid lg:grid-cols-12 gap-10">
          
          {/* Left Column: Image Gallery & Detailed Content ──────────────────── */}
          <div className="lg:col-span-7 space-y-8">
            
            {/* Image Gallery */}
            <div className="bg-white rounded-3xl p-4 border border-[#e7ece8] shadow-sm space-y-4">
              <div className="relative rounded-2xl overflow-hidden bg-gray-100 h-80 sm:h-96 border border-[#e7ece8]">
                <img
                  src={listing.product_images[activeImgIndex] || listing.product_images[0]}
                  alt={listing.listing_title}
                  className="w-full h-full object-cover transition-all duration-300"
                />

                <span className="absolute top-4 left-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#155c32] text-white text-xs font-bold shadow-md">
                  <ShieldCheck className="w-3.5 h-3.5" />
                  Verified Listing
                </span>

                <span className="absolute top-4 right-4 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase bg-white/90 text-[#1a1a1a] border border-[#e7ece8] shadow-sm">
                  SKU: {listing.sku}
                </span>
              </div>

              {/* Thumbnails */}
              {listing.product_images.length > 1 && (
                <div className="flex gap-3 overflow-x-auto pb-1">
                  {listing.product_images.map((img, idx) => (
                    <button
                      key={idx}
                      onClick={() => setActiveImgIndex(idx)}
                      className={cn(
                        "w-20 h-20 rounded-xl overflow-hidden border-2 transition-all shrink-0",
                        activeImgIndex === idx
                          ? "border-[#155c32] ring-2 ring-[#155c32]/20"
                          : "border-transparent opacity-75 hover:opacity-100"
                      )}
                    >
                      <img src={img} alt="" className="w-full h-full object-cover" />
                    </button>
                  ))}
                </div>
              )}
            </div>

            {/* Product Overview & Description */}
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-[#e7ece8] shadow-sm space-y-6">
              <div>
                <h2 className="text-lg font-bold text-[#1a1a1a] mb-2 border-b border-[#e7ece8] pb-3">
                  Listing Description & Operational Overview
                </h2>
                <p className="text-xs sm:text-sm text-[#555555] leading-relaxed">
                  {listing.full_description || listing.short_description}
                </p>
              </div>

              {/* Audited Quality Specifications (JSONB Schema) */}
              <div>
                <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-3">
                  Audited Quality Specifications (Lab Verified)
                </h3>

                <div className="grid grid-cols-2 gap-px bg-[#e7ece8] rounded-2xl overflow-hidden border border-[#e7ece8]">
                  {Object.entries(listing.quality_specifications).map(([key, val]) => (
                    <div key={key} className="bg-white p-3.5 flex flex-col gap-0.5">
                      <span className="text-[11px] text-[#555555] font-semibold">{key}</span>
                      <strong className="text-xs sm:text-sm font-extrabold text-[#1a1a1a]">{val}</strong>
                    </div>
                  ))}
                </div>
              </div>

              {/* Certificate Documents */}
              {listing.certificate_documents && listing.certificate_documents.length > 0 && (
                <div>
                  <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-3">
                    Quality Certificates & Compliance Documents
                  </h3>
                  <div className="space-y-2">
                    {listing.certificate_documents.map((doc, idx) => (
                      <div
                        key={idx}
                        className="p-3.5 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8] flex items-center justify-between gap-4"
                      >
                        <div className="flex items-center gap-3">
                          <div className="w-9 h-9 rounded-xl bg-[#155c32]/10 text-[#155c32] flex items-center justify-center">
                            <FileCheck className="w-5 h-5" />
                          </div>
                          <div>
                            <p className="text-xs font-bold text-[#1a1a1a]">{doc.name}</p>
                            <span className="text-[10px] text-[#555555]">{doc.size} • Verified PDF Document</span>
                          </div>
                        </div>

                        <a
                          href={doc.url}
                          download
                          className="text-xs font-bold text-[#155c32] hover:text-[#33b248] flex items-center gap-1 bg-white px-3 py-1.5 rounded-xl border border-[#e7ece8] shadow-sm"
                        >
                          <Download className="w-3.5 h-3.5" />
                          Download
                        </a>
                      </div>
                    ))}
                  </div>
                </div>
              )}

            </div>

            {/* Vendor Profile Panel */}
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-[#e7ece8] shadow-sm space-y-6">
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#e7ece8] pb-4">
                <div className="flex items-center gap-3">
                  <div className="w-12 h-12 rounded-2xl bg-[#155c32] text-white flex items-center justify-center font-bold text-lg">
                    <Building2 className="w-6 h-6" />
                  </div>
                  <div>
                    <span className="text-[10px] font-bold uppercase tracking-widest text-[#33b248]">
                      Super Admin Audited Seller
                    </span>
                    <h3 className="text-lg font-extrabold text-[#1a1a1a]">
                      {listing.vendor.brand_name}
                    </h3>
                  </div>
                </div>

                {listing.vendor.is_verified && (
                  <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-[#33b248]/15 text-[#155c32] border border-[#33b248]/30">
                    <CheckCircle2 className="w-4 h-4 text-[#33b248]" />
                    Verified Partner
                  </span>
                )}
              </div>

              <div className="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div className="p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] space-y-1">
                  <span className="text-[#555555] font-semibold block">Headquarters:</span>
                  <strong className="text-[#1a1a1a] font-bold">{listing.vendor.city}, {listing.vendor.state}</strong>
                </div>
                <div className="p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] space-y-1">
                  <span className="text-[#555555] font-semibold block">Established:</span>
                  <strong className="text-[#1a1a1a] font-bold">{listing.vendor.established_year || 2015}</strong>
                </div>
                <div className="p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] space-y-1">
                  <span className="text-[#555555] font-semibold block">Seller Score:</span>
                  <strong className="text-[#155c32] font-bold">★ {listing.vendor.rating || 4.9} / 5.0</strong>
                </div>
              </div>
            </div>

          </div>

          {/* Right Column: B2B Commerce Action Card ──────────────────────── */}
          <aside className="lg:col-span-5 space-y-6">
            
            <div className="bg-white rounded-3xl p-6 sm:p-8 border border-[#e7ece8] shadow-xl shadow-[#155c32]/5 space-y-6 sticky top-24">
              
              {/* Commerce Mode Badge Header */}
              <div className="flex justify-between items-center border-b border-[#e7ece8] pb-4">
                <div className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-[#33b248] animate-ping" />
                  <span className="text-xs font-extrabold uppercase tracking-wider text-[#155c32]">
                    Mode: {listing.commerce_mode === "DIRECT_ORDER" ? "Direct Bulk Order" : "B2B RFQ Quote Mode"}
                  </span>
                </div>

                <span className="text-[10px] font-bold px-2.5 py-1 rounded-full bg-[#f4f8f5] text-[#555555] border border-[#e7ece8]">
                  {listing.category_name}
                </span>
              </div>

              {/* Title & Master Link */}
              <div>
                <h1 className="text-xl sm:text-2xl font-extrabold text-[#1a1a1a] leading-tight mb-2">
                  {listing.listing_title}
                </h1>

                <Link
                  href={`/marketplace/${listing.category_slug}/${listing.marketplace_product_slug}`}
                  className="text-xs font-bold text-[#155c32] hover:text-[#33b248] flex items-center gap-1"
                >
                  <Package className="w-3.5 h-3.5" />
                  Master: {listing.marketplace_product_name}
                  <ChevronRight className="w-3 h-3" />
                </Link>
              </div>

              {/* Price & Unit Display */}
              <div className="p-4 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8] flex justify-between items-end">
                <div>
                  <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider block">
                    Public Unit Price
                  </span>
                  <div className="flex items-baseline gap-1 text-[#1a1a1a]">
                    <span className="text-2xl sm:text-3xl font-extrabold">₹{listing.base_price.toLocaleString()}</span>
                    <span className="text-xs text-[#555555] font-semibold">/ {listing.unit}</span>
                  </div>
                </div>

                <div className="text-right text-[11px] text-[#555555] font-semibold">
                  <span>{listing.tax_inclusive ? "Tax Inclusive" : `+${listing.tax_rate}% GST`}</span>
                  <span className="block text-[#155c32] font-bold">In Stock: {listing.available_quantity.toLocaleString()} {listing.unit}</span>
                </div>
              </div>

              {/* Key Logistics Summary */}
              <div className="space-y-2 text-xs text-[#555555]">
                <div className="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-[#e7ece8]">
                  <span className="flex items-center gap-2">
                    <MapPin className="w-4 h-4 text-[#33b248]" />
                    Dispatch Warehouse
                  </span>
                  <strong className="text-[#1a1a1a]">{listing.dispatch_location}</strong>
                </div>

                <div className="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-[#e7ece8]">
                  <span className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-[#33b248]" />
                    Estimated Dispatch Time
                  </span>
                  <strong className="text-[#1a1a1a]">{listing.estimated_dispatch_hours} Hours</strong>
                </div>

                <div className="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 border border-[#e7ece8]">
                  <span className="flex items-center gap-2">
                    <Package className="w-4 h-4 text-[#33b248]" />
                    Minimum Order Quantity (MOQ)
                  </span>
                  <strong className="text-[#1a1a1a]">{listing.min_order_quantity} {listing.unit}</strong>
                </div>
              </div>

              {/* DUAL COMMERCE MODE ARCHITECTURE BRANCH ───────────────────────── */}
              {listing.commerce_mode === "DIRECT_ORDER" ? (
                /* DIRECT ORDER MODE: Instant Add to Cart & Checkout Calculator */
                <div className="space-y-4 pt-2 border-t border-[#e7ece8]">
                  <div className="space-y-2">
                    <div className="flex justify-between items-center text-xs font-bold text-[#1a1a1a]">
                      <span>Select Order Volume ({listing.unit})</span>
                      <span>Min: {listing.min_order_quantity}</span>
                    </div>

                    <div className="flex items-center gap-3">
                      <button
                        onClick={() => setQuantity((q) => Math.max(listing.min_order_quantity, q - 5))}
                        className="w-10 h-10 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] font-bold text-[#1a1a1a] hover:bg-[#e7ece8] flex items-center justify-center"
                        aria-label="Decrease quantity"
                      >
                        <Minus className="w-4 h-4" />
                      </button>

                      <input
                        type="number"
                        min={listing.min_order_quantity}
                        max={listing.available_quantity}
                        value={quantity}
                        onChange={(e) => setQuantity(Math.max(listing.min_order_quantity, parseFloat(e.target.value) || listing.min_order_quantity))}
                        className="h-10 text-center font-bold text-base bg-[#f4f8f5] rounded-xl border border-[#e7ece8] flex-1 focus:border-[#33b248] focus:outline-none"
                      />

                      <button
                        onClick={() => setQuantity((q) => Math.min(listing.available_quantity, q + 5))}
                        className="w-10 h-10 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] font-bold text-[#1a1a1a] hover:bg-[#e7ece8] flex items-center justify-center"
                        aria-label="Increase quantity"
                      >
                        <Plus className="w-4 h-4" />
                      </button>
                    </div>
                  </div>

                  {/* Calculated Price Breakdown */}
                  <div className="p-3.5 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8] space-y-1.5 text-xs">
                    <div className="flex justify-between text-[#555555]">
                      <span>Subtotal ({quantity} {listing.unit}):</span>
                      <span>₹{subtotal.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between text-[#555555]">
                      <span>Estimated GST ({listing.tax_rate}%):</span>
                      <span>₹{taxAmount.toLocaleString()}</span>
                    </div>
                    <div className="flex justify-between font-bold text-sm text-[#1a1a1a] border-t border-[#e7ece8] pt-1.5">
                      <span>Total Price:</span>
                      <span className="text-[#155c32]">₹{grandTotal.toLocaleString()}</span>
                    </div>
                  </div>

                  {/* Direct Order Actions */}
                  <div className="space-y-2.5">
                    {addedToCart ? (
                      <div className="p-3.5 bg-[#f4f8f5] border border-[#33b248] rounded-xl flex items-center justify-center gap-2 text-xs font-bold text-[#155c32]">
                        <CheckCircle2 className="w-4 h-4 text-[#33b248]" />
                        Added to Procurement Cart!
                      </div>
                    ) : (
                      <button
                        onClick={handleAddToCart}
                        disabled={cartLoading}
                        className={cn(
                          buttonVariants({ variant: "default", size: "lg" }),
                          "w-full h-12 rounded-xl bg-[#155c32] hover:bg-[#0d3a1f] text-white font-bold text-sm shadow-xl shadow-[#155c32]/20 transition-all flex items-center justify-center gap-2"
                        )}
                      >
                        {cartLoading ? (
                          <span className="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin" />
                        ) : (
                          <>
                            <ShoppingCart className="w-4 h-4" />
                            Add to Cart / Place Bulk Order
                          </>
                        )}
                      </button>
                    )}

                    <Link
                      href="/order"
                      className={cn(
                        buttonVariants({ variant: "outline", size: "lg" }),
                        "w-full h-11 rounded-xl border-[#155c32] text-[#155c32] font-bold text-xs hover:bg-[#155c32]/5 flex items-center justify-center gap-2"
                      )}
                    >
                      Instant Checkout
                      <ArrowRight className="w-3.5 h-3.5" />
                    </Link>
                  </div>
                </div>
              ) : (
                /* REQUEST QUOTE MODE: Enterprise RFQ Action */
                <div className="space-y-4 pt-2 border-t border-[#e7ece8]">
                  <div className="p-4 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8] space-y-2">
                    <span className="text-xs font-bold text-[#155c32] flex items-center gap-1.5">
                      <Info className="w-4 h-4 text-[#33b248]" />
                      B2B RFQ / Custom Terms Mode
                    </span>
                    <p className="text-xs text-[#555555] leading-relaxed">
                      This product requires a custom quote proposal based on your plant delivery pincode, contract length, and lab specification tolerances.
                    </p>
                  </div>

                  <button
                    onClick={() => setRfqModalOpen(true)}
                    className={cn(
                      buttonVariants({ variant: "default", size: "lg" }),
                      "w-full h-12 rounded-xl bg-[#33b248] hover:bg-[#28923a] text-white font-bold text-sm shadow-xl shadow-[#33b248]/25 transition-all flex items-center justify-center gap-2"
                    )}
                  >
                    <FileText className="w-4 h-4" />
                    Request Custom B2B Quote (RFQ)
                  </button>
                </div>
              )}

              {/* Serviceable Locations List */}
              <div className="pt-2 border-t border-[#e7ece8] space-y-2">
                <span className="text-[10px] font-bold uppercase tracking-wider text-[#555555] block">
                  Serviceable Delivery States
                </span>
                <div className="flex flex-wrap gap-1.5">
                  {listing.serviceable_locations.map((loc) => (
                    <span key={loc} className="px-2.5 py-1 bg-[#f4f8f5] rounded-lg text-[10px] text-[#155c32] font-bold border border-[#e7ece8]">
                      {loc}
                    </span>
                  ))}
                </div>
              </div>

            </div>

          </aside>

        </div>

      </div>

      {/* ── B2B RFQ DRAWER MODAL (For REQUEST_QUOTE mode or direct inquiry) ───── */}
      {rfqModalOpen && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto"
          role="dialog"
          aria-modal="true"
        >
          <div className="bg-white rounded-3xl max-w-xl w-full border border-[#e7ece8] shadow-2xl overflow-hidden animate-fade-up my-8">
            <div className="p-6 border-b border-[#e7ece8] flex justify-between items-center bg-[#f4f8f5]">
              <div>
                <span className="text-[10px] font-bold uppercase tracking-widest text-[#33b248]">
                  Submit B2B RFQ Proposal
                </span>
                <h3 className="text-lg font-bold text-[#1a1a1a]">
                  Request Quote from {listing.vendor.brand_name}
                </h3>
              </div>

              <button
                onClick={() => setRfqModalOpen(false)}
                className="p-1.5 rounded-xl hover:bg-white text-[#555555]"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            <div className="p-6 space-y-4">
              {rfqSuccess ? (
                <div className="p-6 bg-[#f4f8f5] border border-[#33b248] rounded-2xl text-center space-y-3">
                  <CheckCircle2 className="w-12 h-12 text-[#33b248] mx-auto" />
                  <h4 className="text-base font-bold text-[#155c32]">RFQ Proposal Submitted Successfully!</h4>
                  <p className="text-xs text-[#555555] max-w-sm mx-auto">
                    A representative from {listing.vendor.brand_name} and FuelCab procurement will review your requirements and send a customized quote.
                  </p>
                </div>
              ) : (
                <form onSubmit={handleRfqSubmit} className="space-y-4">
                  <div className="grid grid-cols-2 gap-3">
                    <div className="space-y-1">
                      <label className="text-[11px] font-bold text-[#555555]">Your Name</label>
                      <input
                        type="text"
                        required
                        placeholder="John Doe"
                        value={rfqForm.name}
                        onChange={(e) => setRfqForm({ ...rfqForm, name: e.target.value })}
                        className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>

                    <div className="space-y-1">
                      <label className="text-[11px] font-bold text-[#555555]">Company Name</label>
                      <input
                        type="text"
                        required
                        placeholder="Acme Industries"
                        value={rfqForm.company}
                        onChange={(e) => setRfqForm({ ...rfqForm, company: e.target.value })}
                        className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div className="space-y-1">
                      <label className="text-[11px] font-bold text-[#555555]">Contact Phone</label>
                      <input
                        type="tel"
                        required
                        placeholder="+91 98765 43210"
                        value={rfqForm.phone}
                        onChange={(e) => setRfqForm({ ...rfqForm, phone: e.target.value })}
                        className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>

                    <div className="space-y-1">
                      <label className="text-[11px] font-bold text-[#555555]">Corporate Email</label>
                      <input
                        type="email"
                        required
                        placeholder="procurement@acme.com"
                        value={rfqForm.email}
                        onChange={(e) => setRfqForm({ ...rfqForm, email: e.target.value })}
                        className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-2 gap-3">
                    <div className="space-y-1">
                      <label className="text-[11px] font-bold text-[#555555]">Required Volume ({listing.unit})</label>
                      <input
                        type="number"
                        min={listing.min_order_quantity}
                        required
                        value={rfqForm.required_qty}
                        onChange={(e) => setRfqForm({ ...rfqForm, required_qty: parseFloat(e.target.value) || listing.min_order_quantity })}
                        className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>

                    <div className="space-y-1">
                      <label className="text-[11px] font-bold text-[#555555]">Delivery Pincode / City</label>
                      <input
                        type="text"
                        required
                        placeholder="395007 Surat"
                        value={rfqForm.delivery_pincode}
                        onChange={(e) => setRfqForm({ ...rfqForm, delivery_pincode: e.target.value })}
                        className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>
                  </div>

                  <div className="space-y-1">
                    <label className="text-[11px] font-bold text-[#555555]">Special Quality Notes / Contract Duration</label>
                    <textarea
                      rows={3}
                      placeholder="e.g. Need monthly recurring supply of 100 MT with GCV > 3800..."
                      value={rfqForm.notes}
                      onChange={(e) => setRfqForm({ ...rfqForm, notes: e.target.value })}
                      className="w-full p-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                    />
                  </div>

                  <button
                    type="submit"
                    disabled={rfqLoading}
                    className={cn(
                      buttonVariants({ variant: "default", size: "lg" }),
                      "w-full h-11 rounded-xl bg-[#155c32] hover:bg-[#0d3a1f] text-white font-bold text-xs flex items-center justify-center gap-2"
                    )}
                  >
                    {rfqLoading ? (
                      <span className="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin" />
                    ) : (
                      <>
                        Submit Official RFQ Proposal
                        <ArrowRight className="w-3.5 h-3.5" />
                      </>
                    )}
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>
      )}

    </div>
  );
}
