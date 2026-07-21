"use client";

import React, { useState, useEffect } from "react";
import Link from "next/link";
import {
  Search,
  MapPin,
  Flame,
  Droplet,
  Wind,
  CheckCircle2,
  Clock,
  ArrowRight,
  Building2,
  X,
  FileText,
  AlertCircle,
} from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

// ── Types ────────────────────────────────────────────────────────────────────
interface Vendor {
  brand_name: string;
  city: string;
  state: string;
}

interface Listing {
  id: string;
  listing_title: string;
  slug: string;
  category_name: string;
  marketplace_product: string;
  base_price: number;
  unit: string;
  available_quantity: number;
  min_order_quantity: number;
  tax_rate: number;
  tax_inclusive: boolean;
  dispatch_location: string;
  serviceable_locations: string[];
  estimated_dispatch_hours: number;
  quality_specifications: Record<string, string>;
  vendor: Vendor;
  is_featured: boolean;
}

// ────────────────────────────────────────────────────────────────────────────
// High-fidelity fallback catalog — shown when the Laravel API is unreachable.
// ────────────────────────────────────────────────────────────────────────────
const MOCK_LISTINGS: Listing[] = [
  {
    id: "l-1",
    listing_title: "Premium Biomass Briquettes — Gujarat Supply",
    slug: "premium-biomass-briquettes-gujarat",
    category_name: "Solid Fuels",
    marketplace_product: "Biomass Briquettes / Bio Coal",
    base_price: 6800,
    unit: "metric_tonnes",
    available_quantity: 450,
    min_order_quantity: 15,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Surat, Gujarat",
    serviceable_locations: ["Gujarat", "Maharashtra", "Rajasthan", "Madhya Pradesh"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      GCV: "3800 – 4200 kcal/kg",
      Moisture: "Max 8%",
      "Ash Content": "Max 7%",
      Sulphur: "Less than 0.1%",
      Density: "1.2 g/cm³",
    },
    vendor: { brand_name: "Gujarat Bio-Energy Ltd", city: "Surat", state: "Gujarat" },
    is_featured: true,
  },
  {
    id: "l-2",
    listing_title: "Industrial Bio-Diesel (B-100) — Mumbai Supply",
    slug: "industrial-bio-diesel-b100-mumbai",
    category_name: "Liquid Fuels",
    marketplace_product: "Bio Diesel (B-100)",
    base_price: 84,
    unit: "litres",
    available_quantity: 25000,
    min_order_quantity: 1000,
    tax_rate: 18,
    tax_inclusive: false,
    dispatch_location: "Navi Mumbai, Maharashtra",
    serviceable_locations: ["Maharashtra", "Goa", "Karnataka", "Gujarat"],
    estimated_dispatch_hours: 24,
    quality_specifications: {
      "Flash Point": "Min 130 °C",
      "Density at 15 °C": "875 – 900 kg/m³",
      "Viscosity at 40 °C": "3.5 – 5.0 cSt",
      "Water Content": "Max 500 mg/kg",
      "Cetane Number": "Min 51",
    },
    vendor: { brand_name: "Apex Biofuels Logistics", city: "Navi Mumbai", state: "Maharashtra" },
    is_featured: true,
  },
  {
    id: "l-3",
    listing_title: "Compressed Natural Gas (CNG) — NCR Distribution",
    slug: "cng-ncr-distribution",
    category_name: "Gas Fuels",
    marketplace_product: "CNG",
    base_price: 78,
    unit: "kilograms",
    available_quantity: 12000,
    min_order_quantity: 500,
    tax_rate: 12,
    tax_inclusive: true,
    dispatch_location: "Gurugram, Haryana",
    serviceable_locations: ["Delhi NCR", "Haryana", "Uttar Pradesh"],
    estimated_dispatch_hours: 12,
    quality_specifications: {
      "Methane (C1)": "Min 90%",
      "Ethane (C2)": "Max 5%",
      "Sulphur Content": "Max 10 mg/m³",
      "Gross Calorific Value": "11500 kcal/kg",
    },
    vendor: { brand_name: "NCR Gas Suppliers", city: "Gurugram", state: "Haryana" },
    is_featured: false,
  },
  {
    id: "l-4",
    listing_title: "High Calorific Bio-Furnace Oil — Vadodara",
    slug: "bio-furnace-oil-industrial",
    category_name: "Liquid Fuels",
    marketplace_product: "Bio-Furnace Oil",
    base_price: 52,
    unit: "litres",
    available_quantity: 40000,
    min_order_quantity: 2000,
    tax_rate: 18,
    tax_inclusive: false,
    dispatch_location: "Vadodara, Gujarat",
    serviceable_locations: ["Gujarat", "Maharashtra", "Madhya Pradesh"],
    estimated_dispatch_hours: 72,
    quality_specifications: {
      "Calorific Value": "9600 kcal/kg",
      "Viscosity at 50 °C": "125 cSt",
      "Flash Point": "Min 66 °C",
      "Ash Content": "Max 0.1%",
    },
    vendor: { brand_name: "Western India Eco-Fuels", city: "Vadodara", state: "Gujarat" },
    is_featured: false,
  },
  {
    id: "l-5",
    listing_title: "Premium Rice Husk / Paddy Husk — Punjab Supply",
    slug: "rice-husk-paddy-husk-punjab",
    category_name: "Solid Fuels",
    marketplace_product: "Rice Husk",
    base_price: 3800,
    unit: "metric_tonnes",
    available_quantity: 800,
    min_order_quantity: 20,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Ludhiana, Punjab",
    serviceable_locations: ["Punjab", "Haryana", "Himachal Pradesh", "Delhi NCR"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      GCV: "3200 kcal/kg",
      Moisture: "Max 10%",
      "Ash Content": "Max 18%",
      "Bulk Density": "100 kg/m³",
    },
    vendor: { brand_name: "Punjab Agro Products", city: "Ludhiana", state: "Punjab" },
    is_featured: false,
  },
];

// ── Category icon map ────────────────────────────────────────────────────────
const CATEGORY_ICON: Record<string, React.ComponentType<{ className?: string }>> = {
  "Solid Fuels": Flame,
  "Liquid Fuels": Droplet,
  "Gas Fuels": Wind,
};

const CATEGORY_TABS = ["All", "Solid Fuels", "Liquid Fuels", "Gas Fuels", "EV Charging"] as const;

// (Types moved above MOCK_LISTINGS — see top of file)

// ────────────────────────────────────────────────────────────────────────────
// Page component
// ────────────────────────────────────────────────────────────────────────────
export default function MarketplacePage() {
  const [listings, setListings] = useState<Listing[]>(MOCK_LISTINGS);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [locationSearch, setLocationSearch] = useState("");
  const [categoryFilter, setCategoryFilter] = useState<string>("All");
  const [sortBy, setSortBy] = useState("featured");
  const [selectedListing, setSelectedListing] = useState<Listing | null>(null);
  const [inquirySuccess, setInquirySuccess] = useState(false);
  const [inquiryLoading, setInquiryLoading] = useState(false);

  // ── Fetch live listings ──────────────────────────────────────────────────
  useEffect(() => {
    (async () => {
      try {
        const res = await fetch(
          `${process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000"}/api/v1/marketplace/listings`,
          { headers: { Accept: "application/json" } }
        );
        if (res.ok) {
          const json = await res.json();
          if (Array.isArray(json?.data) && json.data.length > 0) {
            setListings(json.data);
          }
        }
      } catch {
        // Backend not reachable — keep mock catalog visible
      } finally {
        setLoading(false);
      }
    })();
  }, []);

  // ── Filter + sort ────────────────────────────────────────────────────────
  const filtered = listings.filter((item) => {
    const q = search.toLowerCase();
    const matchesSearch =
      item.listing_title.toLowerCase().includes(q) ||
      item.marketplace_product.toLowerCase().includes(q);

    const loc = locationSearch.toLowerCase();
    const matchesLocation =
      !loc ||
      item.dispatch_location.toLowerCase().includes(loc) ||
      item.serviceable_locations.some((l) => l.toLowerCase().includes(loc));

    const matchesCategory =
      categoryFilter === "All" || item.category_name === categoryFilter;

    return matchesSearch && matchesLocation && matchesCategory;
  });

  const sorted = [...filtered].sort((a, b) => {
    if (sortBy === "price-asc") return a.base_price - b.base_price;
    if (sortBy === "price-desc") return b.base_price - a.base_price;
    if (sortBy === "stock-desc") return b.available_quantity - a.available_quantity;
    if (a.is_featured && !b.is_featured) return -1;
    if (!a.is_featured && b.is_featured) return 1;
    return 0;
  });

  // ── Inquiry form ─────────────────────────────────────────────────────────
  const handleInquiry = (e: React.FormEvent) => {
    e.preventDefault();
    setInquiryLoading(true);
    setTimeout(() => {
      setInquiryLoading(false);
      setInquirySuccess(true);
      setTimeout(() => {
        setInquirySuccess(false);
        setSelectedListing(null);
      }, 3500);
    }, 1500);
  };

  // ────────────────────────────────────────────────────────────────────────
  // Render
  // ────────────────────────────────────────────────────────────────────────
  return (
    <>
      {/* ── Dark B2B hero ──────────────────────────────────────────────── */}
      <section
        className="bg-[#0d3a1f] text-white py-20 relative overflow-hidden"
        aria-labelledby="marketplace-heading"
      >
        {/* Ambient glows */}
        <div className="absolute inset-0 pointer-events-none" aria-hidden="true">
          <div className="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-[#33b248]/12 blur-3xl" />
          <div className="absolute bottom-0 left-0 w-[400px] h-[400px] rounded-full bg-white/3 blur-3xl" />
        </div>

        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
          <div className="max-w-3xl space-y-5">
            {/* Live badge */}
            <span className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#33b248]/15 border border-[#33b248]/30">
              <span className="w-1.5 h-1.5 rounded-full bg-[#33b248] animate-pulse-dot" />
              <span className="text-[10px] font-extrabold uppercase tracking-widest text-[#33b248]">
                Verified B2B Supplier Network
              </span>
            </span>

            <h1
              id="marketplace-heading"
              className="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.05]"
            >
              FuelCab{" "}
              <span className="text-[#33b248]">Marketplace</span>
            </h1>

            <p className="text-gray-300 text-base sm:text-lg max-w-2xl leading-relaxed">
              Source solid biomass, alternative liquid biofuels, industrial gases, and EV charging
              solutions directly from compliance-verified bulk producers across India.
            </p>

            {/* Stat pills */}
            <div className="flex flex-wrap gap-3 pt-1">
              {[
                { label: "Verified Suppliers", value: "120+" },
                { label: "Product Categories", value: "4" },
                { label: "States Covered", value: "22" },
              ].map(({ label, value }) => (
                <div
                  key={label}
                  className="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm"
                >
                  <span className="text-base font-extrabold text-[#33b248]">{value}</span>
                  <span className="text-[11px] text-gray-400 font-medium">{label}</span>
                </div>
              ))}
            </div>
          </div>

          {/* ── Search Panel ──────────────────────────────────────────────── */}
          <div className="mt-10 bg-white rounded-2xl shadow-2xl shadow-[#0d3a1f]/40 p-4 sm:p-6 text-[#1a1a1a]">
            <div className="grid gap-3 sm:grid-cols-12">
              {/* Keyword search */}
              <div className="sm:col-span-5 relative">
                <Search
                  className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#555555]/50 pointer-events-none"
                  aria-hidden="true"
                />
                <input
                  id="marketplace-search"
                  type="search"
                  placeholder="Search: Biomass, Bio-Diesel, Briquettes..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="w-full h-12 pl-11 pr-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 focus:outline-none placeholder:text-[#555555]/50"
                  aria-label="Search fuel products"
                />
              </div>

              {/* Location search */}
              <div className="sm:col-span-4 relative">
                <MapPin
                  className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#555555]/50 pointer-events-none"
                  aria-hidden="true"
                />
                <input
                  id="marketplace-location"
                  type="search"
                  placeholder="State / city (e.g. Gujarat, Mumbai)"
                  value={locationSearch}
                  onChange={(e) => setLocationSearch(e.target.value)}
                  className="w-full h-12 pl-11 pr-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 focus:outline-none placeholder:text-[#555555]/50"
                  aria-label="Filter by delivery location"
                />
              </div>

              {/* Sort */}
              <div className="sm:col-span-3">
                <select
                  value={sortBy}
                  onChange={(e) => setSortBy(e.target.value)}
                  className="w-full h-12 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:outline-none cursor-pointer text-[#1a1a1a] font-medium"
                  aria-label="Sort listings"
                >
                  <option value="featured">Sort: Featured</option>
                  <option value="price-asc">Price: Low → High</option>
                  <option value="price-desc">Price: High → Low</option>
                  <option value="stock-desc">Max Stock</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* ── Listings section ───────────────────────────────────────────── */}
      <section className="py-14 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12" aria-label="Fuel listings">

        {/* Category tabs + result count */}
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#e7ece8] pb-6 mb-10">
          <div
            className="flex flex-wrap gap-2"
            role="tablist"
            aria-label="Filter by fuel category"
          >
            {CATEGORY_TABS.map((cat) => {
              const isSelected = categoryFilter === cat;
              const isEV = cat === "EV Charging";
              return (
                <button
                  key={cat}
                  role="tab"
                  aria-selected={isSelected}
                  disabled={isEV}
                  onClick={() => !isEV && setCategoryFilter(cat)}
                  className={cn(
                    "px-4 py-2 rounded-xl text-xs font-bold transition-all duration-150 border select-none",
                    isSelected
                      ? "bg-[#155c32] text-white border-transparent shadow-md shadow-[#155c32]/20"
                      : "bg-white text-[#555555] border-[#e7ece8] hover:border-[#33b248] hover:text-[#155c32]",
                    isEV && "opacity-50 cursor-not-allowed"
                  )}
                >
                  {cat}
                  {isEV && (
                    <span className="ml-1.5 text-[8px] font-extrabold uppercase tracking-wider bg-[#e7ece8] text-[#555555] px-1.5 py-0.5 rounded-full">
                      Soon
                    </span>
                  )}
                </button>
              );
            })}
          </div>

          <p className="text-xs font-semibold text-[#555555] bg-[#f4f8f5] px-4 py-2 rounded-xl border border-[#e7ece8] shrink-0">
            <span className="text-[#155c32] font-extrabold">{sorted.length}</span>{" "}
            {sorted.length === 1 ? "solution" : "solutions"} available
          </p>
        </div>

        {/* ── State: loading ───────────────────────────────────────────── */}
        {loading && (
          <div className="py-24 text-center space-y-4" aria-live="polite" aria-busy="true">
            <div className="w-9 h-9 rounded-full border-4 border-[#33b248]/20 border-t-[#155c32] animate-spin mx-auto" />
            <p className="text-sm text-[#555555]">Loading energy solutions catalog…</p>
          </div>
        )}

        {/* ── State: empty ─────────────────────────────────────────────── */}
        {!loading && sorted.length === 0 && (
          <div
            className="py-20 text-center space-y-5 bg-white rounded-2xl border border-[#e7ece8] p-8"
            aria-live="polite"
          >
            <AlertCircle className="w-12 h-12 text-[#ffb400] mx-auto" aria-hidden="true" />
            <div className="space-y-1">
              <h3 className="text-base font-bold text-[#1a1a1a]">No matching solutions found</h3>
              <p className="text-xs text-[#555555] max-w-sm mx-auto">
                Broaden your keywords, clear the location filter, or switch category.
              </p>
            </div>
            <button
              onClick={() => {
                setSearch("");
                setLocationSearch("");
                setCategoryFilter("All");
              }}
              className={cn(
                buttonVariants({ variant: "outline" }),
                "rounded-xl border-[#e7ece8] h-10 text-sm"
              )}
            >
              Clear all filters
            </button>
          </div>
        )}

        {/* ── State: results ───────────────────────────────────────────── */}
        {!loading && sorted.length > 0 && (
          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {sorted.map((item) => {
              const Icon = CATEGORY_ICON[item.category_name] ?? Wind;
              return (
                <article
                  key={item.id}
                  className="bg-white rounded-2xl border border-[#e7ece8] hover:border-[#33b248] p-6 transition-all duration-300 hover:shadow-xl hover:shadow-[#155c32]/5 flex flex-col justify-between group"
                  aria-label={item.listing_title}
                >
                  <div>
                    {/* Category badge + featured tag */}
                    <div className="flex justify-between items-start mb-4">
                      <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#f4f8f5] text-[#555555] text-[10px] font-bold border border-[#e7ece8]">
                        <Icon className="w-3 h-3 text-[#33b248]" aria-hidden="true" />
                        {item.category_name}
                      </span>
                      {item.is_featured && (
                        <span className="text-[9px] font-extrabold uppercase tracking-wider text-[#0d3a1f] bg-[#ffb400] px-2.5 py-0.5 rounded-full shadow-sm shadow-[#ffb400]/30">
                          Featured
                        </span>
                      )}
                    </div>

                    {/* Title */}
                    <h2 className="text-[15px] font-bold text-[#1a1a1a] mb-1.5 line-clamp-2 min-h-[2.8rem] group-hover:text-[#155c32] transition-colors duration-150 leading-snug">
                      {item.listing_title}
                    </h2>

                    {/* Vendor */}
                    <p className="text-[11px] text-[#555555] mb-4 flex items-center gap-1.5 font-medium">
                      <Building2 className="w-3.5 h-3.5 text-[#555555]/50 flex-shrink-0" aria-hidden="true" />
                      <span className="truncate">{item.vendor.brand_name}</span>
                    </p>

                    {/* Logistics */}
                    <dl className="space-y-1.5 mb-5 text-xs text-[#555555]">
                      <div className="flex items-center gap-2">
                        <MapPin className="w-3.5 h-3.5 text-[#555555]/50 flex-shrink-0" aria-hidden="true" />
                        <dd>
                          Dispatch:{" "}
                          <strong className="text-[#1a1a1a] font-semibold">{item.dispatch_location}</strong>
                        </dd>
                      </div>
                      <div className="flex items-center gap-2">
                        <Clock className="w-3.5 h-3.5 text-[#555555]/50 flex-shrink-0" aria-hidden="true" />
                        <dd>
                          Lead time:{" "}
                          <strong className="text-[#1a1a1a] font-semibold">
                            {item.estimated_dispatch_hours}h
                          </strong>
                        </dd>
                      </div>
                    </dl>
                  </div>

                  {/* Pricing + CTA */}
                  <div className="border-t border-[#e7ece8] pt-4">
                    <div className="flex justify-between items-end mb-3.5">
                      <div>
                        <p className="text-[9px] uppercase font-bold text-[#555555]/60 tracking-wider mb-0.5">
                          Base Price
                        </p>
                        <p className="flex items-baseline gap-1">
                          <span className="text-xl font-extrabold text-[#1a1a1a]">₹{item.base_price.toLocaleString()}</span>
                          <span className="text-[11px] text-[#555555] font-semibold">/{item.unit}</span>
                        </p>
                      </div>
                      <p className="text-[10px] text-[#555555] font-semibold text-right">
                        Min. {item.min_order_quantity} {item.unit}
                      </p>
                    </div>

                    <button
                      onClick={() => setSelectedListing(item)}
                      className={cn(
                        buttonVariants({ variant: "default" }),
                        "w-full h-10 rounded-xl bg-[#155c32] text-white hover:bg-[#0d3a1f] font-semibold text-xs transition-all duration-200 group-hover:shadow-lg group-hover:shadow-[#155c32]/15"
                      )}
                      aria-label={`View details and specs for ${item.listing_title}`}
                    >
                      View Details & Specifications
                    </button>
                  </div>
                </article>
              );
            })}
          </div>
        )}

        {/* ── Vendor CTA banner ─────────────────────────────────────────── */}
        <div
          className="mt-20 p-8 sm:p-12 rounded-3xl bg-[#0d3a1f] text-white border border-[#155c32]/60 relative overflow-hidden"
          aria-label="Become a Marketplace vendor"
        >
          <div className="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div className="absolute -top-10 right-0 w-[400px] h-[400px] bg-[#33b248]/10 rounded-full blur-3xl" />
          </div>
          <div className="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
            <div className="space-y-3 max-w-2xl">
              <span className="text-[10px] font-extrabold uppercase tracking-widest text-[#33b248]">
                Partner Registration Open
              </span>
              <h2 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                List Your Energy Products on FuelCab
              </h2>
              <p className="text-gray-300 text-sm leading-relaxed max-w-xl">
                Join India's leading industrial energy marketplace. Connect directly with manufacturing
                plants, logistics hubs, and commercial buyers purchasing bulk fuels.
              </p>
            </div>
            <div className="flex flex-wrap gap-3 shrink-0">
              <Link
                href="/vendor/register"
                className={cn(
                  buttonVariants({ variant: "default" }),
                  "h-12 px-6 rounded-xl bg-[#33b248] hover:bg-[#28923a] text-white font-bold text-sm shadow-lg shadow-[#33b248]/25 transition-all duration-200 flex items-center gap-2"
                )}
              >
                Register as Vendor
                <ArrowRight className="w-4 h-4" aria-hidden="true" />
              </Link>
              <Link
                href="/#faqs"
                className={cn(
                  buttonVariants({ variant: "outline" }),
                  "h-12 px-6 rounded-xl border-white/20 text-white hover:bg-white/8 transition-all duration-200"
                )}
              >
                Vendor FAQs
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* ── Listing detail modal ──────────────────────────────────────── */}
      {selectedListing && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center p-4"
          role="dialog"
          aria-modal="true"
          aria-labelledby="modal-listing-title"
          onClick={(e) => e.target === e.currentTarget && setSelectedListing(null)}
        >
          {/* Backdrop */}
          <div className="absolute inset-0 bg-[#1a1a1a]/65 backdrop-blur-sm" aria-hidden="true" />

          {/* Panel */}
          <div className="relative bg-white rounded-3xl max-w-2xl w-full shadow-2xl animate-fade-up max-h-[90vh] flex flex-col overflow-hidden border border-[#e7ece8]">
            {/* Header */}
            <div className="p-6 border-b border-[#e7ece8] flex justify-between items-start gap-4">
              <div className="min-w-0">
                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#f4f8f5] text-[10px] font-bold text-[#555555] border border-[#e7ece8] mb-2">
                  {selectedListing.category_name}
                </span>
                <h2
                  id="modal-listing-title"
                  className="text-lg font-bold text-[#1a1a1a] leading-snug"
                >
                  {selectedListing.listing_title}
                </h2>
              </div>
              <button
                onClick={() => setSelectedListing(null)}
                className="p-2 rounded-xl hover:bg-[#f4f8f5] text-[#555555] hover:text-[#1a1a1a] transition-colors duration-150 shrink-0"
                aria-label="Close listing details"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Body */}
            <div className="p-6 overflow-y-auto flex-1 space-y-6">
              {/* Vendor banner */}
              <div className="flex items-center gap-4 p-4 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8]">
                <div
                  className="w-10 h-10 rounded-xl bg-[#155c32] flex items-center justify-center shrink-0"
                  aria-hidden="true"
                >
                  <Building2 className="w-5 h-5 text-white" />
                </div>
                <div>
                  <p className="text-[10px] font-bold uppercase tracking-wider text-[#555555]">Verified Seller</p>
                  <p className="text-sm font-bold text-[#155c32]">{selectedListing.vendor.brand_name}</p>
                  <p className="text-[11px] text-[#555555]">
                    {selectedListing.vendor.city}, {selectedListing.vendor.state}
                  </p>
                </div>
              </div>

              {/* Price / Stock summary */}
              <div className="grid grid-cols-2 gap-3">
                <div className="p-4 rounded-2xl border border-[#e7ece8] space-y-0.5">
                  <p className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">Base Price</p>
                  <p className="text-lg font-extrabold text-[#1a1a1a]">
                    ₹{selectedListing.base_price.toLocaleString()}{" "}
                    <span className="text-xs font-semibold text-[#555555]">/ {selectedListing.unit}</span>
                  </p>
                  <p className="text-[10px] text-[#555555]">
                    {selectedListing.tax_inclusive
                      ? "GST Inclusive"
                      : `+ ${selectedListing.tax_rate}% GST`}
                  </p>
                </div>
                <div className="p-4 rounded-2xl border border-[#e7ece8] space-y-0.5">
                  <p className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">Available Stock</p>
                  <p className="text-lg font-extrabold text-[#1a1a1a]">
                    {selectedListing.available_quantity.toLocaleString()}{" "}
                    <span className="text-xs font-semibold text-[#555555]">{selectedListing.unit}</span>
                  </p>
                  <p className="text-[10px] text-[#555555]">
                    Min. {selectedListing.min_order_quantity} {selectedListing.unit}
                  </p>
                </div>
              </div>

              {/* Quality Specifications */}
              <div>
                <h3 className="text-[10px] font-extrabold uppercase tracking-widest text-[#555555] mb-3">
                  Quality Specifications
                </h3>
                <div className="grid grid-cols-2 gap-px rounded-xl overflow-hidden border border-[#e7ece8] bg-[#e7ece8]">
                  {Object.entries(selectedListing.quality_specifications).map(([key, val]) => (
                    <div key={key} className="bg-white p-3">
                      <p className="text-[10px] text-[#555555] font-semibold mb-0.5">{key}</p>
                      <p className="text-xs font-bold text-[#1a1a1a]">{val}</p>
                    </div>
                  ))}
                </div>
              </div>

              {/* Logistics */}
              <div>
                <h3 className="text-[10px] font-extrabold uppercase tracking-widest text-[#555555] mb-3">
                  Logistics & Coverage
                </h3>
                <dl className="space-y-2 text-xs text-[#555555] mb-3">
                  <div className="flex items-center gap-2">
                    <MapPin className="w-4 h-4 text-[#33b248] shrink-0" aria-hidden="true" />
                    <dd>
                      Dispatch:{" "}
                      <strong className="text-[#1a1a1a]">{selectedListing.dispatch_location}</strong>
                    </dd>
                  </div>
                  <div className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-[#33b248] shrink-0" aria-hidden="true" />
                    <dd>
                      Lead time:{" "}
                      <strong className="text-[#1a1a1a]">{selectedListing.estimated_dispatch_hours} hours</strong>
                    </dd>
                  </div>
                </dl>
                <div className="flex flex-wrap gap-1.5">
                  {selectedListing.serviceable_locations.map((loc) => (
                    <span
                      key={loc}
                      className="px-2.5 py-1 bg-[#f4f8f5] rounded-lg text-[10px] text-[#155c32] font-bold border border-[#e7ece8]"
                    >
                      {loc}
                    </span>
                  ))}
                </div>
              </div>

              {/* Inquiry form */}
              <div className="border-t border-[#e7ece8] pt-5">
                <h3 className="text-[10px] font-extrabold uppercase tracking-widest text-[#555555] mb-4">
                  B2B Quote / Callback Request
                </h3>

                {inquirySuccess ? (
                  <div className="flex items-center gap-3 p-4 bg-[#f4f8f5] border border-[#33b248] rounded-2xl text-sm text-[#155c32] font-bold">
                    <CheckCircle2 className="w-5 h-5 text-[#33b248] shrink-0" aria-hidden="true" />
                    Inquiry sent! A vendor representative will contact you shortly.
                  </div>
                ) : (
                  <form onSubmit={handleInquiry} className="space-y-3">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <input
                        type="text"
                        placeholder="Your full name"
                        required
                        className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                        aria-label="Your full name"
                      />
                      <input
                        type="tel"
                        placeholder="Company mobile number"
                        required
                        className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                        aria-label="Company mobile number"
                      />
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                      <input
                        type="text"
                        placeholder="Required quantity (e.g. 50 MT)"
                        required
                        className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                        aria-label="Required quantity"
                      />
                      <button
                        type="submit"
                        disabled={inquiryLoading}
                        className={cn(
                          buttonVariants({ variant: "default" }),
                          "h-11 rounded-xl bg-[#155c32] text-white hover:bg-[#0d3a1f] font-bold text-xs flex items-center justify-center gap-1.5 transition-all"
                        )}
                      >
                        {inquiryLoading ? (
                          <span className="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin" />
                        ) : (
                          <>
                            Request Quote
                            <FileText className="w-3.5 h-3.5" aria-hidden="true" />
                          </>
                        )}
                      </button>
                    </div>
                  </form>
                )}
              </div>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
