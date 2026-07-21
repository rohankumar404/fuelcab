"use client";

import React, { useState, useEffect, useTransition, Suspense } from "react";
import Link from "next/link";
import { useRouter, useSearchParams, usePathname } from "next/navigation";
import { motion, AnimatePresence } from "framer-motion";
import {
  Search,
  MapPin,
  Flame,
  Droplet,
  Wind,
  Zap,
  CheckCircle2,
  Clock,
  ArrowRight,
  Building2,
  X,
  FileText,
  AlertCircle,
  Filter,
  ShieldCheck,
  FileCheck,
  TrendingUp,
  Truck,
  ChevronDown,
  Layers,
  Sparkles,
  HelpCircle,
  Package,
  SlidersHorizontal,
  RefreshCw,
  ExternalLink,
  ChevronRight,
  Award,
} from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

// ── Types ────────────────────────────────────────────────────────────────────
interface Vendor {
  id: string;
  brand_name: string;
  city: string;
  state: string;
  is_verified: boolean;
  rating?: number;
}

interface ProductMaster {
  id: string;
  name: string;
  category_name: string;
  code: string;
  standard_unit: string;
  gcv_range: string;
  description: string;
  active_listings_count: number;
}

interface Listing {
  id: string;
  listing_title: string;
  slug: string;
  sku: string;
  category_name: string;
  marketplace_product_id: string;
  marketplace_product: string;
  short_description: string;
  full_description: string;
  base_price: number;
  unit: string;
  available_quantity: number;
  min_order_quantity: number;
  max_order_quantity?: number;
  tax_rate: number;
  tax_inclusive: boolean;
  dispatch_location: string;
  serviceable_locations: string[];
  estimated_dispatch_hours: number;
  quality_specifications: Record<string, string>;
  certificate_documents?: string[];
  vendor: Vendor;
  is_featured: boolean;
  approval_status: "APPROVED";
  created_at: string;
}

// ── Master Products Data ────────────────────────────────────────────────────
const MARKETPLACE_PRODUCT_MASTERS: ProductMaster[] = [
  {
    id: "mp-1",
    name: "Refuse Derived Fuel (RDF)",
    category_name: "Solid Fuels",
    code: "RDF-IND-3500",
    standard_unit: "metric_tonnes",
    gcv_range: "3500 – 4200 kcal/kg",
    description: "High-calorific processed non-hazardous industrial waste fuel ideal for cement kilns and power boilers.",
    active_listings_count: 14,
  },
  {
    id: "mp-2",
    name: "Biomass Briquettes / Bio Coal",
    category_name: "Solid Fuels",
    code: "BIO-BRIQ-90",
    standard_unit: "metric_tonnes",
    gcv_range: "3800 – 4300 kcal/kg",
    description: "Eco-friendly dense cylindrical biofuel made from agricultural residue and sawdust with sub-8% moisture.",
    active_listings_count: 22,
  },
  {
    id: "mp-3",
    name: "Bio Diesel (B-100)",
    category_name: "Liquid Fuels",
    code: "BIO-DSL-B100",
    standard_unit: "litres",
    gcv_range: "9000 – 9500 kcal/kg",
    description: "100% pure fatty acid methyl ester (FAME) bio-diesel conforming to IS 15607 standards for commercial fleets & DGs.",
    active_listings_count: 18,
  },
  {
    id: "mp-4",
    name: "Compressed Natural Gas (CNG)",
    category_name: "Gas Fuels",
    code: "CNG-IND-PURE",
    standard_unit: "kilograms",
    gcv_range: "11500 – 12000 kcal/kg",
    description: "High-pressure clean natural gas with >90% methane purity for industrial furnace and captive power usage.",
    active_listings_count: 9,
  },
  {
    id: "mp-5",
    name: "Bio-Furnace Oil",
    category_name: "Liquid Fuels",
    code: "BIO-FO-HV",
    standard_unit: "litres",
    gcv_range: "9600 – 9900 kcal/kg",
    description: "Low-sulfur eco-replacement for traditional heavy furnace oil in textile, chemical, and metal processing plants.",
    active_listings_count: 11,
  },
  {
    id: "mp-6",
    name: "Raw Rice Husk / Paddy Husk",
    category_name: "Solid Fuels",
    code: "AGRO-RH-RAW",
    standard_unit: "metric_tonnes",
    gcv_range: "3000 – 3300 kcal/kg",
    description: "Bulk agricultural byproduct biomass fuel for fluidized bed combustion (FBC) boilers.",
    active_listings_count: 16,
  },
];

// ── Master Approved Vendor Listings Mock Data ──────────────────────────────
const MOCK_LISTINGS: Listing[] = [
  {
    id: "l-1",
    listing_title: "Premium Industrial RDF — 3500+ GCV Bulk Supply",
    slug: "premium-industrial-rdf-3500-gcv",
    sku: "RDF-GUJ-3500",
    category_name: "Solid Fuels",
    marketplace_product_id: "mp-1",
    marketplace_product: "Refuse Derived Fuel (RDF)",
    short_description: "Refuse Derived Fuel (RDF) with guaranteed GCV above 3500 kcal/kg, screened for low ash & zero moisture spikes.",
    full_description: "Processed from segregated dry combustible fraction. Shredded to 30-50mm flake size, tested for low chlorine (<0.5%) and high calorific consistency. Ideal for cement kilns, captive power plants, and heavy steam generation boilers across Western & Central India.",
    base_price: 4200,
    unit: "metric_tonnes",
    available_quantity: 1200,
    min_order_quantity: 25,
    max_order_quantity: 5000,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Surat, Gujarat",
    serviceable_locations: ["Gujarat", "Maharashtra", "Rajasthan", "Madhya Pradesh"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      "Calorific Value (GCV)": "3500 – 3800 kcal/kg",
      "Moisture Content": "Max 12%",
      "Ash Content": "Max 14%",
      "Flake Size": "30 – 50 mm",
      "Chlorine (Cl)": "Max 0.4%",
      "Sulphur (S)": "Max 0.2%",
    },
    certificate_documents: ["Lab_Test_Report_RDF_2026.pdf", "ISO_9001_Quality_Cert.pdf"],
    vendor: { id: "v-101", brand_name: "Gujarat Eco-Energy Solutions", city: "Surat", state: "Gujarat", is_verified: true, rating: 4.9 },
    is_featured: true,
    approval_status: "APPROVED",
    created_at: "2026-06-15T10:00:00Z",
  },
  {
    id: "l-2",
    listing_title: "High-Density Biomass Briquettes (90mm Sawdust & Husk)",
    slug: "high-density-biomass-briquettes-90mm",
    sku: "BRIQ-MH-90",
    category_name: "Solid Fuels",
    marketplace_product_id: "mp-2",
    marketplace_product: "Biomass Briquettes / Bio Coal",
    short_description: "90mm diameter bio-briquettes manufactured from saw dust and crop husk with low ash residue.",
    full_description: "Extruded biomass briquettes under high pressure without chemical binders. Delivers steady heat output and minimal smoke emissions. Perfect substitute for Indonesian coal in industrial boilers.",
    base_price: 6800,
    unit: "metric_tonnes",
    available_quantity: 650,
    min_order_quantity: 15,
    max_order_quantity: 2000,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Nagpur, Maharashtra",
    serviceable_locations: ["Maharashtra", "Chhattisgarh", "Madhya Pradesh", "Telangana"],
    estimated_dispatch_hours: 36,
    quality_specifications: {
      "Calorific Value (GCV)": "3900 – 4200 kcal/kg",
      "Moisture Content": "Max 7%",
      "Ash Content": "Max 6.5%",
      "Density": "1.25 g/cm³",
      "Diameter": "90 mm",
    },
    certificate_documents: ["SGS_Calorific_Analysis_Briquette.pdf"],
    vendor: { id: "v-102", brand_name: "Vidarbha Bio-Coal Energy", city: "Nagpur", state: "Maharashtra", is_verified: true, rating: 4.8 },
    is_featured: true,
    approval_status: "APPROVED",
    created_at: "2026-06-18T12:30:00Z",
  },
  {
    id: "l-3",
    listing_title: "Industrial Bio-Diesel (B-100) — IS 15607 Certified",
    slug: "industrial-bio-diesel-b100-is15607",
    sku: "BD100-MUM-01",
    category_name: "Liquid Fuels",
    marketplace_product_id: "mp-3",
    marketplace_product: "Bio Diesel (B-100)",
    short_description: "Pure B-100 Bio-Diesel derived from non-edible oilseeds for commercial DG sets & fleet engines.",
    full_description: "IS 15607 compliant ultra-clean liquid biofuel. Zero sulfur content, high cetane value (53+), superior lubrication property extending engine injector lifespan while reducing carbon footprint.",
    base_price: 84,
    unit: "litres",
    available_quantity: 35000,
    min_order_quantity: 1000,
    max_order_quantity: 100000,
    tax_rate: 18,
    tax_inclusive: false,
    dispatch_location: "Navi Mumbai, Maharashtra",
    serviceable_locations: ["Maharashtra", "Goa", "Gujarat", "Karnataka"],
    estimated_dispatch_hours: 24,
    quality_specifications: {
      "Flash Point": "Min 135 °C",
      "Density at 15°C": "880 kg/m³",
      "Kinematic Viscosity at 40°C": "4.2 cSt",
      "Water Content": "Max 300 mg/kg",
      "Cetane Number": "Min 53",
      "Sulfur Content": "Below 10 ppm",
    },
    certificate_documents: ["IS15607_Certificate_Apex.pdf", "MSDS_BioDiesel_B100.pdf"],
    vendor: { id: "v-103", brand_name: "Apex Biofuels Logistics", city: "Navi Mumbai", state: "Maharashtra", is_verified: true, rating: 5.0 },
    is_featured: true,
    approval_status: "APPROVED",
    created_at: "2026-06-20T09:15:00Z",
  },
  {
    id: "l-4",
    listing_title: "High-Pressure Compressed Natural Gas (CNG) — Industrial Tankers",
    slug: "high-pressure-cng-industrial-tankers",
    sku: "CNG-NCR-IND",
    category_name: "Gas Fuels",
    marketplace_product_id: "mp-4",
    marketplace_product: "Compressed Natural Gas (CNG)",
    short_description: "Cascade CNG delivery for factories without pipeline connectivity in Delhi NCR & Haryana.",
    full_description: "Delivered in mobile cascade storage units. Methane content > 92%. Ideal for continuous annealing furnaces, ceramic kilns, and captive power generators seeking clean energy transition.",
    base_price: 78,
    unit: "kilograms",
    available_quantity: 15000,
    min_order_quantity: 500,
    max_order_quantity: 20000,
    tax_rate: 12,
    tax_inclusive: true,
    dispatch_location: "Gurugram, Haryana",
    serviceable_locations: ["Delhi NCR", "Haryana", "Uttar Pradesh", "Punjab"],
    estimated_dispatch_hours: 12,
    quality_specifications: {
      "Methane (C1)": "Min 92.5%",
      "Ethane (C2)": "Max 3.8%",
      "Gross Calorific Value": "11800 kcal/kg",
      "Delivery Pressure": "200 Bar",
      "Hydrogen Sulfide": "Nil",
    },
    certificate_documents: ["PESO_Tanker_Safety_Approval.pdf"],
    vendor: { id: "v-104", brand_name: "NCR Clean Gas Infra", city: "Gurugram", state: "Haryana", is_verified: true, rating: 4.7 },
    is_featured: false,
    approval_status: "APPROVED",
    created_at: "2026-06-22T14:20:00Z",
  },
  {
    id: "l-5",
    listing_title: "Low Sulfur Bio-Furnace Oil — Commercial Boiler Grade",
    slug: "low-sulfur-bio-furnace-oil-boiler-grade",
    sku: "BFO-VAD-9600",
    category_name: "Liquid Fuels",
    marketplace_product_id: "mp-5",
    marketplace_product: "Bio-Furnace Oil",
    short_description: "9600 GCV alternative furnace oil designed to lower soot and boiler maintenance.",
    full_description: "Viscosity-optimized liquid fuel blend. Compatible with standard FO burners without pre-heating modifications. Viscosity at 50°C is 110 cSt, ensuring smooth atomization and clean combustion.",
    base_price: 54,
    unit: "litres",
    available_quantity: 50000,
    min_order_quantity: 2000,
    max_order_quantity: 150000,
    tax_rate: 18,
    tax_inclusive: false,
    dispatch_location: "Vadodara, Gujarat",
    serviceable_locations: ["Gujarat", "Maharashtra", "Madhya Pradesh"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      "Calorific Value (GCV)": "9600 kcal/kg",
      "Viscosity at 50°C": "110 cSt",
      "Flash Point": "Min 68 °C",
      "Water & Sediment": "Max 0.2%",
      "Ash Content": "Max 0.08%",
    },
    certificate_documents: ["Vadodara_Lab_Analysis_FO.pdf"],
    vendor: { id: "v-105", brand_name: "Western India Eco-Fuels", city: "Vadodara", state: "Gujarat", is_verified: true, rating: 4.9 },
    is_featured: false,
    approval_status: "APPROVED",
    created_at: "2026-06-25T11:45:00Z",
  },
  {
    id: "l-6",
    listing_title: "Screened Agro Rice Husk — High Calorific Boiler Fuel",
    slug: "screened-agro-rice-husk-boiler-fuel",
    sku: "RH-PB-RAW",
    category_name: "Solid Fuels",
    marketplace_product_id: "mp-6",
    marketplace_product: "Raw Rice Husk / Paddy Husk",
    short_description: "Clean dry paddy husk with uniform moisture content under 10% for agricultural & textile boilers.",
    full_description: "Sourced directly from automated Punjab rice mills. Screened to eliminate mud and stones. Consistently high silicon oxide ash suitable for silica extraction post combustion.",
    base_price: 3600,
    unit: "metric_tonnes",
    available_quantity: 900,
    min_order_quantity: 20,
    max_order_quantity: 3000,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Ludhiana, Punjab",
    serviceable_locations: ["Punjab", "Haryana", "Himachal Pradesh", "Delhi NCR", "Uttarakhand"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      "Calorific Value (GCV)": "3200 kcal/kg",
      "Moisture Content": "Max 9.5%",
      "Ash Content": "Max 17.5%",
      "Bulk Density": "105 kg/m³",
    },
    certificate_documents: ["Punjab_Agro_Quality_Cert.pdf"],
    vendor: { id: "v-106", brand_name: "Punjab Agro Products Ltd", city: "Ludhiana", state: "Punjab", is_verified: true, rating: 4.6 },
    is_featured: false,
    approval_status: "APPROVED",
    created_at: "2026-06-28T16:10:00Z",
  },
];

// ── Category Icon Map ────────────────────────────────────────────────────────
const CATEGORY_ICON_MAP: Record<string, React.ComponentType<{ className?: string }>> = {
  "Solid Fuels": Flame,
  "Liquid Fuels": Droplet,
  "Gas Fuels": Wind,
  "EV": Zap,
};

// ── Main Page Content Component ─────────────────────────────────────────────
function MarketplaceContent() {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [isPending, startTransition] = useTransition();

  // Read URL query parameters
  const currentCategory = searchParams.get("category") || "All";
  const currentProduct = searchParams.get("product") || "";
  const currentSearch = searchParams.get("search") || "";
  const currentLocation = searchParams.get("location") || "";
  const currentVendor = searchParams.get("vendor") || "";
  const currentSort = searchParams.get("sort") || "featured";
  const currentPage = parseInt(searchParams.get("page") || "1", 10);
  const currentMinPrice = searchParams.get("min_price") || "";
  const currentMaxPrice = searchParams.get("max_price") || "";
  const currentMoq = searchParams.get("moq") || "";

  // Local state for interactive controls
  const [listings, setListings] = useState<Listing[]>(MOCK_LISTINGS);
  const [products, setProducts] = useState<ProductMaster[]>(MARKETPLACE_PRODUCT_MASTERS);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  // Selected listing modal state
  const [selectedListing, setSelectedListing] = useState<Listing | null>(null);
  const [inquirySuccess, setInquirySuccess] = useState(false);
  const [inquiryLoading, setInquiryLoading] = useState(false);

  // Helper to update URL params cleanly
  const updateQueryParams = (newParams: Record<string, string | number | null>) => {
    const params = new URLSearchParams(searchParams.toString());
    Object.entries(newParams).forEach(([key, value]) => {
      if (value === null || value === "" || value === "All") {
        params.delete(key);
      } else {
        params.set(key, String(value));
      }
    });
    // Reset to page 1 on filter changes unless explicitly changing page
    if (!newParams.hasOwnProperty("page")) {
      params.delete("page");
    }

    startTransition(() => {
      router.push(`${pathname}?${params.toString()}`, { scroll: false });
    });
  };

  // Fetch listings from backend API or use mock fallback
  useEffect(() => {
    async function fetchMarketplaceData() {
      setLoading(true);
      setError(null);
      try {
        const query = new URLSearchParams();
        if (currentSearch) query.append("search", currentSearch);
        if (currentLocation) query.append("dispatch_location", currentLocation);
        if (currentProduct) query.append("marketplace_product_id", currentProduct);

        const apiHost = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";
        const res = await fetch(`${apiHost}/api/v1/marketplace/listings?${query.toString()}`, {
          headers: { Accept: "application/json" },
        });

        if (res.ok) {
          const json = await res.json();
          if (Array.isArray(json?.data) && json.data.length > 0) {
            setListings(json.data);
          } else {
            // Keep local fallback if API returns empty during initial dev seed
            setListings(MOCK_LISTINGS);
          }
        } else {
          setListings(MOCK_LISTINGS);
        }
      } catch (err) {
        // Fallback gracefully to high-fidelity mock B2B catalog
        setListings(MOCK_LISTINGS);
      } finally {
        setLoading(false);
      }
    }

    fetchMarketplaceData();
  }, [currentSearch, currentLocation, currentProduct]);

  // Client-side Filter logic
  const filteredListings = listings.filter((item) => {
    // Category Filter
    if (currentCategory !== "All") {
      const catSlug = currentCategory.toLowerCase().replace(/[^a-z0-9]/g, "");
      const itemCatSlug = item.category_name.toLowerCase().replace(/[^a-z0-9]/g, "");
      if (catSlug !== itemCatSlug && !itemCatSlug.includes(catSlug)) {
        return false;
      }
    }

    // Product Master Filter
    if (currentProduct) {
      const matchId = item.marketplace_product_id === currentProduct;
      const matchName = item.marketplace_product.toLowerCase().includes(currentProduct.toLowerCase());
      if (!matchId && !matchName) return false;
    }

    // Keyword Search
    if (currentSearch) {
      const q = currentSearch.toLowerCase();
      const titleMatch = item.listing_title.toLowerCase().includes(q);
      const prodMatch = item.marketplace_product.toLowerCase().includes(q);
      const vendorMatch = item.vendor.brand_name.toLowerCase().includes(q);
      const skuMatch = item.sku.toLowerCase().includes(q);
      if (!titleMatch && !prodMatch && !vendorMatch && !skuMatch) return false;
    }

    // Location Filter
    if (currentLocation) {
      const loc = currentLocation.toLowerCase();
      const dispatchMatch = item.dispatch_location.toLowerCase().includes(loc);
      const serviceMatch = item.serviceable_locations.some((s) => s.toLowerCase().includes(loc));
      if (!dispatchMatch && !serviceMatch) return false;
    }

    // Vendor Filter
    if (currentVendor) {
      if (!item.vendor.brand_name.toLowerCase().includes(currentVendor.toLowerCase())) {
        return false;
      }
    }

    // Min & Max Price
    if (currentMinPrice && item.base_price < parseFloat(currentMinPrice)) return false;
    if (currentMaxPrice && item.base_price > parseFloat(currentMaxPrice)) return false;

    // MOQ Filter
    if (currentMoq && item.min_order_quantity > parseFloat(currentMoq)) return false;

    return true;
  });

  // Client-side Sorting logic
  const sortedListings = [...filteredListings].sort((a, b) => {
    if (currentSort === "price-asc") return a.base_price - b.base_price;
    if (currentSort === "price-desc") return b.base_price - a.base_price;
    if (currentSort === "newest") {
      return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    }
    // Default: Featured first
    if (a.is_featured && !b.is_featured) return -1;
    if (!a.is_featured && b.is_featured) return 1;
    return b.id.localeCompare(a.id);
  });

  // Pagination calculation
  const ITEMS_PER_PAGE = 6;
  const totalPages = Math.ceil(sortedListings.length / ITEMS_PER_PAGE) || 1;
  const validPage = Math.min(Math.max(1, currentPage), totalPages);
  const paginatedListings = sortedListings.slice(
    (validPage - 1) * ITEMS_PER_PAGE,
    validPage * ITEMS_PER_PAGE
  );

  // Form Submission Handler for B2B Lead Inquiry
  const handleInquirySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setInquiryLoading(true);
    setTimeout(() => {
      setInquiryLoading(false);
      setInquirySuccess(true);
      setTimeout(() => {
        setInquirySuccess(false);
        setSelectedListing(null);
      }, 3500);
    }, 1200);
  };

  return (
    <div className="min-h-screen bg-[#fafbfa] text-[#1a1a1a]">

      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 1. MARKETPLACE HERO                                                   */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section
        id="marketplace-hero"
        className="relative bg-[#0d3a1f] text-white py-20 lg:py-24 overflow-hidden border-b border-[#155c32]"
        aria-labelledby="hero-title"
      >
        {/* Modern Ambient Mesh Glows */}
        <div className="absolute inset-0 pointer-events-none opacity-30" aria-hidden="true">
          <div className="absolute top-0 right-1/4 w-[600px] h-[600px] rounded-full bg-[#33b248]/20 blur-3xl" />
          <div className="absolute bottom-0 left-10 w-[500px] h-[500px] rounded-full bg-white/5 blur-3xl" />
        </div>

        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
          <div className="max-w-4xl space-y-6">
            
            {/* Enterprise Eyebrow */}
            <motion.div
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.4 }}
              className="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-[#33b248]/15 border border-[#33b248]/35 text-[#33b248] text-xs font-bold uppercase tracking-wider"
            >
              <ShieldCheck className="w-4 h-4 text-[#33b248]" />
              <span>Verified B2B Industrial Energy Platform</span>
            </motion.div>

            {/* Main Heading */}
            <motion.h1
              id="hero-title"
              initial={{ opacity: 0, y: 15 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.1 }}
              className="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.08]"
            >
              Fuel & Energy Marketplace <br className="hidden sm:inline" />
              for <span className="text-[#33b248]">Modern Businesses</span>
            </motion.h1>

            {/* Description */}
            <motion.p
              initial={{ opacity: 0, y: 15 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.2 }}
              className="text-gray-300 text-base sm:text-lg lg:text-xl max-w-3xl leading-relaxed"
            >
              Source industrial fuels, alternative fuels and energy materials from verified suppliers across India.
            </motion.p>

            {/* Hero CTAs */}
            <motion.div
              initial={{ opacity: 0, y: 15 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.5, delay: 0.3 }}
              className="flex flex-wrap items-center gap-4 pt-2"
            >
              <button
                onClick={() => {
                  const catalogElem = document.getElementById("marketplace-catalog");
                  catalogElem?.scrollIntoView({ behavior: "smooth" });
                }}
                className={cn(
                  buttonVariants({ variant: "default", size: "lg" }),
                  "h-12 px-7 rounded-xl bg-[#33b248] hover:bg-[#28923a] text-white font-bold text-sm shadow-xl shadow-[#33b248]/25 transition-all duration-200 flex items-center gap-2 group cursor-pointer"
                )}
                aria-label="Explore Products in Marketplace"
              >
                Explore Products
                <ArrowRight className="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" />
              </button>

              <Link
                href="/vendor/register"
                className={cn(
                  buttonVariants({ variant: "outline", size: "lg" }),
                  "h-12 px-7 rounded-xl border-white/30 text-white hover:bg-white/10 hover:border-white font-bold text-sm transition-all duration-200 flex items-center gap-2"
                )}
                aria-label="Register to Become a Vendor"
              >
                <Building2 className="w-4 h-4 text-[#33b248]" />
                Become a Vendor
              </Link>
            </motion.div>
          </div>

          {/* Search Bar Panel in Hero */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.4 }}
            className="mt-12 bg-white rounded-2xl p-4 sm:p-6 shadow-2xl shadow-[#0d3a1f]/50 border border-[#e7ece8] text-[#1a1a1a]"
          >
            <div className="grid gap-3 sm:grid-cols-12">
              {/* Product / Title Keyword Search */}
              <div className="sm:col-span-6 relative">
                <label htmlFor="hero-search-input" className="sr-only">Search Products or Listings</label>
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#555555]/50 pointer-events-none" />
                <input
                  id="hero-search-input"
                  type="text"
                  placeholder="Search fuel e.g. RDF, Biomass Briquettes, Bio-Diesel, CNG..."
                  value={currentSearch}
                  onChange={(e) => updateQueryParams({ search: e.target.value })}
                  className="w-full h-12 pl-11 pr-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 focus:outline-none transition-all placeholder:text-[#555555]/50"
                />
              </div>

              {/* Location Input */}
              <div className="sm:col-span-4 relative">
                <label htmlFor="hero-location-input" className="sr-only">Dispatch Location</label>
                <MapPin className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-[#555555]/50 pointer-events-none" />
                <input
                  id="hero-location-input"
                  type="text"
                  placeholder="Location e.g. Gujarat, Mumbai, NCR..."
                  value={currentLocation}
                  onChange={(e) => updateQueryParams({ location: e.target.value })}
                  className="w-full h-12 pl-11 pr-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 focus:outline-none transition-all placeholder:text-[#555555]/50"
                />
              </div>

              {/* Action Button */}
              <div className="sm:col-span-2">
                <button
                  onClick={() => {
                    const catalogElem = document.getElementById("marketplace-catalog");
                    catalogElem?.scrollIntoView({ behavior: "smooth" });
                  }}
                  className={cn(
                    buttonVariants({ variant: "default" }),
                    "w-full h-12 rounded-xl bg-[#155c32] hover:bg-[#0d3a1f] text-white font-bold text-sm transition-all duration-200 flex items-center justify-center gap-1.5"
                  )}
                >
                  Search
                  <Search className="w-4 h-4" />
                </button>
              </div>
            </div>

            {/* Quick Stats Banner */}
            <div className="mt-5 pt-4 border-t border-[#e7ece8] flex flex-wrap items-center justify-between gap-4 text-xs text-[#555555] font-semibold">
              <div className="flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 text-[#33b248]" />
                <span>100% Super Admin Approved Listings</span>
              </div>
              <div className="flex items-center gap-2">
                <Award className="w-4 h-4 text-[#33b248]" />
                <span>Verified Supplier Credentials & Lab Specs</span>
              </div>
              <div className="flex items-center gap-2">
                <Truck className="w-4 h-4 text-[#33b248]" />
                <span>Multi-State Serviceable Delivery</span>
              </div>
            </div>
          </motion.div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 2. CATEGORY NAVIGATION                                                */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section className="py-12 bg-white border-b border-[#e7ece8]" aria-label="Category Navigation">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
              <span className="text-[10px] font-extrabold uppercase tracking-widest text-[#33b248] block mb-1">
                Browse Energy Domains
              </span>
              <h2 className="text-xl sm:text-2xl font-bold text-[#1a1a1a]">
                Product Categories
              </h2>
            </div>

            {/* Active category reset */}
            {currentCategory !== "All" && (
              <button
                onClick={() => updateQueryParams({ category: "All" })}
                className="text-xs font-bold text-[#155c32] hover:text-[#33b248] transition-colors flex items-center gap-1"
              >
                <RefreshCw className="w-3.5 h-3.5" />
                Reset Category Filter
              </button>
            )}
          </div>

          <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
            {[
              { id: "solid-fuels", label: "Solid Fuels", icon: Flame, desc: "Briquettes, Bio Coal, RDF, Husk", isSoon: false },
              { id: "liquid-fuels", label: "Liquid Fuels", icon: Droplet, desc: "Bio-Diesel B100, Furnace Oil, LDO", isSoon: false },
              { id: "gas-fuels", label: "Gas Fuels", icon: Wind, desc: "CNG, Bio-CNG / CBG, LPG", isSoon: false },
              { id: "ev", label: "EV", icon: Zap, desc: "Fleet Charging Infrastructure", isSoon: true },
            ].map((cat) => {
              const Icon = cat.icon;
              const isSelected = currentCategory.toLowerCase().includes(cat.id.replace("-fuels", ""));

              return (
                <button
                  key={cat.id}
                  disabled={cat.isSoon}
                  onClick={() => !cat.isSoon && updateQueryParams({ category: cat.label })}
                  className={cn(
                    "p-5 rounded-2xl border transition-all duration-200 text-left relative flex flex-col justify-between h-36 group",
                    cat.isSoon
                      ? "bg-[#f4f8f5]/60 border-[#e7ece8] opacity-75 cursor-not-allowed"
                      : isSelected
                      ? "bg-[#155c32] text-white border-[#155c32] shadow-xl shadow-[#155c32]/20"
                      : "bg-white border-[#e7ece8] hover:border-[#33b248] hover:shadow-lg hover:shadow-[#155c32]/5 text-[#1a1a1a]"
                  )}
                >
                  <div className="flex justify-between items-start">
                    <div
                      className={cn(
                        "w-10 h-10 rounded-xl flex items-center justify-center transition-colors",
                        isSelected ? "bg-white/15 text-white" : "bg-[#f4f8f5] text-[#155c32] group-hover:bg-[#155c32] group-hover:text-white"
                      )}
                    >
                      <Icon className="w-5 h-5 stroke-2" />
                    </div>

                    {cat.isSoon && (
                      <span className="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-full tracking-wider bg-[#e7ece8] text-[#555555]">
                        Coming Soon
                      </span>
                    )}
                  </div>

                  <div>
                    <h3 className={cn("text-base font-bold mb-0.5", isSelected ? "text-white" : "text-[#1a1a1a]")}>
                      {cat.label}
                    </h3>
                    <p className={cn("text-xs line-clamp-1", isSelected ? "text-gray-200" : "text-[#555555]")}>
                      {cat.desc}
                    </p>
                  </div>
                </button>
              );
            })}
          </div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 3. FEATURED PRODUCTS (Product Master Data)                            */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section className="py-16 bg-[#f4f8f5] border-b border-[#e7ece8]" aria-label="Featured Product Masters">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          
          <div className="mb-10 text-center max-w-2xl mx-auto space-y-2">
            <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] block">
              Super Admin Controlled Master Specifications
            </span>
            <h2 className="text-2xl sm:text-3xl font-extrabold text-[#1a1a1a]">
              Featured Marketplace Product Masters
            </h2>
            <p className="text-xs sm:text-sm text-[#555555]">
              Standardized fuel specifications approved for industrial procurement across India.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {products.map((pm) => (
              <div
                key={pm.id}
                className="bg-white rounded-2xl p-6 border border-[#e7ece8] hover:border-[#33b248] transition-all duration-200 shadow-sm hover:shadow-md flex flex-col justify-between group"
              >
                <div>
                  <div className="flex justify-between items-start mb-3">
                    <span className="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-[#f4f8f5] text-[#155c32] border border-[#e7ece8]">
                      {pm.category_name}
                    </span>
                    <span className="text-[10px] font-mono text-[#555555] bg-gray-100 px-2 py-0.5 rounded">
                      {pm.code}
                    </span>
                  </div>

                  <h3 className="text-lg font-bold text-[#1a1a1a] mb-2 group-hover:text-[#155c32] transition-colors">
                    {pm.name}
                  </h3>

                  <p className="text-xs text-[#555555] leading-relaxed mb-4">
                    {pm.description}
                  </p>

                  <div className="p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] space-y-1 mb-4 text-xs">
                    <div className="flex justify-between">
                      <span className="text-[#555555]">Standard GCV:</span>
                      <strong className="text-[#1a1a1a] font-semibold">{pm.gcv_range}</strong>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-[#555555]">Trading Unit:</span>
                      <strong className="text-[#1a1a1a] font-semibold capitalize">{pm.standard_unit.replace("_", " ")}</strong>
                    </div>
                  </div>
                </div>

                <div className="flex items-center justify-between border-t border-[#e7ece8] pt-4 mt-auto">
                  <span className="text-xs font-semibold text-[#155c32]">
                    {pm.active_listings_count} Active Listings
                  </span>

                  <button
                    onClick={() => updateQueryParams({ product: pm.name })}
                    className="text-xs font-bold text-[#155c32] hover:text-[#33b248] flex items-center gap-1 group/btn"
                  >
                    View Listings
                    <ChevronRight className="w-3.5 h-3.5 transition-transform group-hover/btn:translate-x-0.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 4. FEATURED VENDOR LISTINGS (Catalog, Filters, Sort & Pagination)     */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section
        id="marketplace-catalog"
        className="py-16 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12"
        aria-label="Vendor Listings Catalog"
      >
        <div className="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-[#e7ece8] pb-6">
          <div>
            <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] block mb-1">
              Active Vendor Listings
            </span>
            <h2 className="text-2xl sm:text-3xl font-extrabold text-[#1a1a1a]">
              Approved & Active Bulk Supplies
            </h2>
          </div>

          {/* Active Filter Summary Badges & Reset */}
          <div className="flex flex-wrap items-center gap-2">
            {currentCategory !== "All" && (
              <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#155c32] text-white">
                Category: {currentCategory}
                <button onClick={() => updateQueryParams({ category: "All" })} aria-label="Remove category filter">
                  <X className="w-3.5 h-3.5 hover:text-red-300" />
                </button>
              </span>
            )}
            {currentProduct && (
              <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#155c32] text-white">
                Product: {currentProduct}
                <button onClick={() => updateQueryParams({ product: null })} aria-label="Remove product filter">
                  <X className="w-3.5 h-3.5 hover:text-red-300" />
                </button>
              </span>
            )}
            {currentSearch && (
              <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#155c32] text-white">
                Keyword: "{currentSearch}"
                <button onClick={() => updateQueryParams({ search: null })} aria-label="Remove search filter">
                  <X className="w-3.5 h-3.5 hover:text-red-300" />
                </button>
              </span>
            )}
            {(currentCategory !== "All" || currentProduct || currentSearch || currentLocation || currentVendor || currentMinPrice || currentMaxPrice || currentMoq) && (
              <button
                onClick={() => updateQueryParams({ category: "All", product: null, search: null, location: null, vendor: null, min_price: null, max_price: null, moq: null })}
                className="text-xs font-bold text-red-600 hover:text-red-800 underline ml-2"
              >
                Clear All Filters
              </button>
            )}
          </div>
        </div>

        <div className="grid lg:grid-cols-12 gap-8">
          
          {/* Filters Sidebar */}
          <aside className="lg:col-span-3 space-y-6">
            <div className="bg-white rounded-2xl p-5 border border-[#e7ece8] shadow-sm space-y-5">
              
              <div className="flex items-center justify-between border-b border-[#e7ece8] pb-3">
                <h3 className="text-sm font-bold text-[#1a1a1a] flex items-center gap-2">
                  <SlidersHorizontal className="w-4 h-4 text-[#155c32]" />
                  Filter Listings
                </h3>
                {isPending && <span className="text-[10px] text-[#33b248] font-bold animate-pulse">Updating...</span>}
              </div>

              {/* Sort By Select */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[#555555]">Sort Order</label>
                <select
                  value={currentSort}
                  onChange={(e) => updateQueryParams({ sort: e.target.value })}
                  className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs font-semibold focus:border-[#33b248] focus:outline-none cursor-pointer"
                >
                  <option value="featured">Featured First</option>
                  <option value="price-asc">Price: Low to High</option>
                  <option value="price-desc">Price: High to Low</option>
                  <option value="newest">Newest Listings</option>
                </select>
              </div>

              {/* Product Master Dropdown Filter */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[#555555]">Master Product</label>
                <select
                  value={currentProduct}
                  onChange={(e) => updateQueryParams({ product: e.target.value })}
                  className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs font-semibold focus:border-[#33b248] focus:outline-none cursor-pointer"
                >
                  <option value="">All Master Products</option>
                  {products.map((p) => (
                    <option key={p.id} value={p.name}>
                      {p.name}
                    </option>
                  ))}
                </select>
              </div>

              {/* Vendor Search */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[#555555]">Vendor Name</label>
                <input
                  type="text"
                  placeholder="e.g. Gujarat Eco, Apex"
                  value={currentVendor}
                  onChange={(e) => updateQueryParams({ vendor: e.target.value })}
                  className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                />
              </div>

              {/* Location Search */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[#555555]">Dispatch / Serviceable Location</label>
                <input
                  type="text"
                  placeholder="e.g. Gujarat, Maharashtra"
                  value={currentLocation}
                  onChange={(e) => updateQueryParams({ location: e.target.value })}
                  className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                />
              </div>

              {/* Max Acceptable MOQ */}
              <div className="space-y-1.5">
                <label className="text-xs font-bold text-[#555555]">Max Minimum Order (MOQ)</label>
                <input
                  type="number"
                  placeholder="e.g. 50 (MT/Litres)"
                  value={currentMoq}
                  onChange={(e) => updateQueryParams({ moq: e.target.value })}
                  className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                />
              </div>

              {/* Price Range Filter */}
              <div className="space-y-1.5 pt-2 border-t border-[#e7ece8]">
                <label className="text-xs font-bold text-[#555555]">Price Range (Base Price)</label>
                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="number"
                    placeholder="Min ₹"
                    value={currentMinPrice}
                    onChange={(e) => updateQueryParams({ min_price: e.target.value })}
                    className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                  />
                  <input
                    type="number"
                    placeholder="Max ₹"
                    value={currentMaxPrice}
                    onChange={(e) => updateQueryParams({ max_price: e.target.value })}
                    className="w-full h-10 px-3 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                  />
                </div>
              </div>

            </div>
          </aside>

          {/* Main Listings Grid */}
          <main className="lg:col-span-9 space-y-6">

            {/* Results Count & Meta */}
            <div className="flex justify-between items-center bg-white p-4 rounded-xl border border-[#e7ece8] text-xs text-[#555555] font-semibold">
              <span>
                Showing <strong className="text-[#155c32]">{paginatedListings.length}</strong> of{" "}
                <strong className="text-[#155c32]">{sortedListings.length}</strong> active listings
              </span>

              <span>Page {validPage} of {totalPages}</span>
            </div>

            {/* Loading Skeletons */}
            {loading ? (
              <div className="grid sm:grid-cols-2 gap-6">
                {[1, 2, 3, 4].map((i) => (
                  <div key={i} className="bg-white rounded-2xl p-6 border border-[#e7ece8] space-y-4 animate-pulse">
                    <div className="h-4 bg-gray-200 rounded w-1/3" />
                    <div className="h-6 bg-gray-200 rounded w-3/4" />
                    <div className="h-4 bg-gray-200 rounded w-1/2" />
                    <div className="h-16 bg-gray-100 rounded" />
                    <div className="h-10 bg-gray-200 rounded" />
                  </div>
                ))}
              </div>
            ) : sortedListings.length === 0 ? (
              /* Empty State */
              <div className="bg-white rounded-2xl border border-[#e7ece8] p-12 text-center space-y-4">
                <AlertCircle className="w-12 h-12 text-[#ffb400] mx-auto" />
                <div className="space-y-1">
                  <h3 className="text-lg font-bold text-[#1a1a1a]">No Approved Listings Match Your Query</h3>
                  <p className="text-xs text-[#555555] max-w-md mx-auto">
                    Try broadening your filters, clearing location constraints, or searching by general fuel category.
                  </p>
                </div>
                <button
                  onClick={() => updateQueryParams({ category: "All", product: null, search: null, location: null, vendor: null, min_price: null, max_price: null, moq: null })}
                  className={cn(buttonVariants({ variant: "outline" }), "rounded-xl border-[#e7ece8]")}
                >
                  Reset All Filters
                </button>
              </div>
            ) : (
              /* Listing Cards Grid */
              <div className="grid sm:grid-cols-2 gap-6">
                {paginatedListings.map((item) => {
                  const Icon = CATEGORY_ICON_MAP[item.category_name] || Flame;

                  return (
                    <div
                      key={item.id}
                      className="bg-white rounded-2xl border border-[#e7ece8] hover:border-[#33b248] p-6 transition-all duration-300 hover:shadow-xl hover:shadow-[#155c32]/5 flex flex-col justify-between group relative"
                    >
                      <div>
                        {/* Header Badges */}
                        <div className="flex justify-between items-start mb-3">
                          <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#f4f8f5] text-[#555555] text-[10px] font-bold border border-[#e7ece8]">
                            <Icon className="w-3 h-3 text-[#33b248]" />
                            {item.category_name}
                          </span>

                          {item.is_featured && (
                            <span className="text-[9px] font-extrabold uppercase tracking-wider text-[#0d3a1f] bg-[#ffb400] px-2.5 py-0.5 rounded-full shadow-sm">
                              Featured
                            </span>
                          )}
                        </div>

                        {/* Title */}
                        <h3 className="text-base font-bold text-[#1a1a1a] mb-2 line-clamp-2 min-h-[3rem] group-hover:text-[#155c32] transition-colors leading-snug">
                          {item.listing_title}
                        </h3>

                        {/* Master Product & Vendor Info */}
                        <div className="space-y-1 mb-4 text-xs">
                          <p className="text-[#555555] font-medium flex items-center gap-1.5">
                            <Package className="w-3.5 h-3.5 text-[#33b248]" />
                            Master: <strong className="text-[#1a1a1a]">{item.marketplace_product}</strong>
                          </p>
                          <p className="text-[#555555] font-medium flex items-center gap-1.5">
                            <Building2 className="w-3.5 h-3.5 text-[#33b248]" />
                            Vendor: <strong className="text-[#155c32]">{item.vendor.brand_name}</strong>
                            {item.vendor.is_verified && (
                              <CheckCircle2 className="w-3.5 h-3.5 text-[#33b248] fill-[#33b248]/15" />
                            )}
                          </p>
                        </div>

                        {/* Quick Quality Specs Snippet */}
                        <div className="p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] mb-4 grid grid-cols-2 gap-2 text-[11px]">
                          {Object.entries(item.quality_specifications).slice(0, 2).map(([key, val]) => (
                            <div key={key} className="space-y-0.5">
                              <span className="text-[#555555] font-medium block truncate">{key}:</span>
                              <strong className="text-[#1a1a1a] font-bold block truncate">{val}</strong>
                            </div>
                          ))}
                        </div>

                        {/* Logistics Details */}
                        <div className="space-y-1.5 mb-5 text-xs text-[#555555]">
                          <div className="flex items-center gap-2">
                            <MapPin className="w-3.5 h-3.5 text-[#555555]/60" />
                            <span>Dispatch: <strong className="text-[#1a1a1a]">{item.dispatch_location}</strong></span>
                          </div>
                          <div className="flex items-center gap-2">
                            <Clock className="w-3.5 h-3.5 text-[#555555]/60" />
                            <span>Lead Time: <strong className="text-[#1a1a1a]">{item.estimated_dispatch_hours} Hours</strong></span>
                          </div>
                        </div>
                      </div>

                      {/* Price & Action Footer */}
                      <div className="border-t border-[#e7ece8] pt-4 mt-auto">
                        <div className="flex justify-between items-end mb-4">
                          <div>
                            <span className="text-[9px] uppercase font-bold text-[#555555]/70 tracking-wider block">
                              Base Price
                            </span>
                            <div className="flex items-baseline gap-1 text-[#1a1a1a]">
                              <span className="text-xl font-extrabold">₹{item.base_price.toLocaleString()}</span>
                              <span className="text-xs text-[#555555] font-semibold">/ {item.unit}</span>
                            </div>
                          </div>

                          <div className="text-right text-[10px] text-[#555555] font-semibold">
                            <span>Stock: {item.available_quantity.toLocaleString()} {item.unit}</span>
                            <span className="block text-gray-400">Min Order: {item.min_order_quantity} {item.unit}</span>
                          </div>
                        </div>

                        <button
                          onClick={() => setSelectedListing(item)}
                          className={cn(
                            buttonVariants({ variant: "default" }),
                            "w-full h-10 rounded-xl bg-[#155c32] hover:bg-[#0d3a1f] text-white font-semibold text-xs transition-all duration-200"
                          )}
                        >
                          View Specifications & Request Quote
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}

            {/* Pagination Controls */}
            {totalPages > 1 && (
              <div className="flex justify-center items-center gap-2 pt-6">
                <button
                  disabled={validPage <= 1}
                  onClick={() => updateQueryParams({ page: validPage - 1 })}
                  className="px-4 py-2 rounded-xl border border-[#e7ece8] text-xs font-bold text-[#555555] hover:bg-[#f4f8f5] disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Previous
                </button>

                {Array.from({ length: totalPages }, (_, i) => i + 1).map((pg) => (
                  <button
                    key={pg}
                    onClick={() => updateQueryParams({ page: pg })}
                    className={cn(
                      "w-9 h-9 rounded-xl text-xs font-bold transition-all",
                      pg === validPage
                        ? "bg-[#155c32] text-white"
                        : "bg-white border border-[#e7ece8] text-[#555555] hover:bg-[#f4f8f5]"
                    )}
                  >
                    {pg}
                  </button>
                ))}

                <button
                  disabled={validPage >= totalPages}
                  onClick={() => updateQueryParams({ page: validPage + 1 })}
                  className="px-4 py-2 rounded-xl border border-[#e7ece8] text-xs font-bold text-[#555555] hover:bg-[#f4f8f5] disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  Next
                </button>
              </div>
            )}

          </main>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 5. WHY FUELCAB MARKETPLACE                                            */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section className="py-20 bg-white border-t border-b border-[#e7ece8]" aria-label="Why FuelCab Marketplace">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          
          <div className="text-center max-w-2xl mx-auto mb-16 space-y-2">
            <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] block">
              Built for High-Volume B2B Procurement
            </span>
            <h2 className="text-3xl font-extrabold text-[#1a1a1a]">
              Why Businesses Trust FuelCab Marketplace
            </h2>
            <p className="text-sm text-[#555555]">
              Eliminating multi-broker markups with audited quality compliance and verified logistics.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-5 gap-6">
            {[
              {
                title: "Verified Suppliers",
                desc: "Strict company audit & document verification before any vendor listing goes live.",
                icon: ShieldCheck,
              },
              {
                title: "Transparent Procurement",
                desc: "No hidden fees. Full breakdown of base pricing, GST tax tiers, and transportation.",
                icon: SlidersHorizontal,
              },
              {
                title: "Business-focused Ordering",
                desc: "Flexible Minimum Order Quantities (MOQ), volume tier quotes, & contract agreements.",
                icon: Package,
              },
              {
                title: "Quality Documentation",
                desc: "Lab test reports, GCV certifications, & MSDS sheets attached to every listing.",
                icon: FileCheck,
              },
              {
                title: "Multi-location Support",
                desc: "Serviceable state mapping and nationwide logistics coverage for industrial plants.",
                icon: Truck,
              },
            ].map((item, idx) => {
              const Icon = item.icon;
              return (
                <div
                  key={idx}
                  className="bg-[#f4f8f5] rounded-2xl p-6 border border-[#e7ece8] hover:border-[#33b248] transition-all space-y-3"
                >
                  <div className="w-10 h-10 rounded-xl bg-[#155c32] text-white flex items-center justify-center">
                    <Icon className="w-5 h-5" />
                  </div>
                  <h3 className="text-base font-bold text-[#1a1a1a]">{item.title}</h3>
                  <p className="text-xs text-[#555555] leading-relaxed">{item.desc}</p>
                </div>
              );
            })}
          </div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 6. HOW MARKETPLACE WORKS                                              */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section className="py-20 bg-[#f4f8f5] border-b border-[#e7ece8]" aria-label="How Marketplace Works">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          
          <div className="text-center max-w-2xl mx-auto mb-16 space-y-2">
            <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] block">
              Streamlined Procurement Workflow
            </span>
            <h2 className="text-3xl font-extrabold text-[#1a1a1a]">
              How FuelCab Marketplace Works
            </h2>
            <p className="text-sm text-[#555555]">
              From product discovery to plant unloading in 6 simple steps.
            </p>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-6 gap-6 relative">
            {[
              { step: "01", title: "Find Product", desc: "Search master fuel catalog or filter solid, liquid, gas categories." },
              { step: "02", title: "Compare Suppliers", desc: "Evaluate GCV specs, lab reports, dispatch locations & pricing." },
              { step: "03", title: "Select Quantity", desc: "Specify required metric tonnage or liter volume for your plant." },
              { step: "04", title: "Place Request/Order", desc: "Submit direct lead request or place digital purchase agreement." },
              { step: "05", title: "Vendor Fulfillment", desc: "Verified seller prepares stock and schedules tanker/truck dispatch." },
              { step: "06", title: "Track Delivery", desc: "Monitor real-time transit status until plant unloading & verification." },
            ].map((st, i) => (
              <div key={i} className="bg-white rounded-2xl p-5 border border-[#e7ece8] shadow-sm relative flex flex-col justify-between h-48">
                <div>
                  <span className="text-2xl font-extrabold text-[#33b248] block mb-2">{st.step}</span>
                  <h3 className="text-sm font-bold text-[#1a1a1a] mb-1.5">{st.title}</h3>
                  <p className="text-xs text-[#555555] leading-relaxed">{st.desc}</p>
                </div>
              </div>
            ))}
          </div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 7. BECOME A VENDOR CTA                                                */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section className="py-20 bg-white" aria-label="Become a Vendor Call to Action">
        <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          
          <div className="p-8 sm:p-12 lg:p-16 rounded-3xl bg-[#0d3a1f] text-white border border-[#155c32] relative overflow-hidden shadow-2xl shadow-[#0d3a1f]/30">
            <div className="absolute inset-0 pointer-events-none opacity-20">
              <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-[#33b248] rounded-full blur-3xl" />
            </div>

            <div className="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
              <div className="space-y-4 max-w-2xl">
                <span className="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-widest text-[#33b248] bg-[#33b248]/15 px-3 py-1 rounded-full border border-[#33b248]/30">
                  <Building2 className="w-3.5 h-3.5" />
                  For Energy Producers & Distributors
                </span>
                <h2 className="text-3xl sm:text-4xl font-extrabold tracking-tight">
                  List Your Energy Products on FuelCab
                </h2>
                <p className="text-gray-300 text-sm sm:text-base leading-relaxed">
                  Connect directly with verified manufacturing plants, captive power units, commercial fleets, and logistics hubs purchasing bulk energy across India.
                </p>
              </div>

              <div className="flex flex-wrap gap-4 w-full sm:w-auto shrink-0">
                <Link
                  href="/vendor/register"
                  className={cn(
                    buttonVariants({ variant: "default", size: "lg" }),
                    "h-12 px-7 rounded-xl bg-[#33b248] hover:bg-[#28923a] text-white font-bold text-sm shadow-lg shadow-[#33b248]/25 transition-all flex items-center justify-center gap-2"
                  )}
                >
                  Register as Vendor
                  <ArrowRight className="w-4 h-4" />
                </Link>
                <Link
                  href="/#faqs"
                  className={cn(
                    buttonVariants({ variant: "outline", size: "lg" }),
                    "h-12 px-7 rounded-xl border-white/20 text-white hover:bg-white/10 transition-all flex items-center justify-center"
                  )}
                >
                  Vendor FAQs
                </Link>
              </div>
            </div>
          </div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 8. FAQ SECTION                                                        */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <section className="py-20 bg-[#f4f8f5] border-t border-[#e7ece8]" aria-label="Frequently Asked Questions">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          
          <div className="text-center mb-14 space-y-2">
            <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] block">
              Got Questions?
            </span>
            <h2 className="text-3xl font-extrabold text-[#1a1a1a]">
              Marketplace Frequently Asked Questions
            </h2>
            <p className="text-xs sm:text-sm text-[#555555]">
              Everything you need to know about procurement, vendor vetting, and logistics.
            </p>
          </div>

          <div className="space-y-4">
            {[
              {
                q: "How are vendor listings vetted and approved?",
                a: "Every vendor must submit business registration (GST, PAN, Company CIN), factory license, quality test reports, and PESO safety approvals (for gas/fuel storage). Our Super Admin team audits these documents before approving any listing to the public marketplace.",
              },
              {
                q: "Can I request customized bulk pricing for high-volume orders?",
                a: "Yes! For orders exceeding standard Minimum Order Quantities (MOQ), you can submit a B2B Lead Inquiry directly on any listing. The verified vendor will provide tailored volume discount tier pricing.",
              },
              {
                q: "What quality assurance documents are provided with purchases?",
                a: "All active listings include downloadable lab test reports (specifying GCV, moisture, ash, sulfur), ISO certifications, and Material Safety Data Sheets (MSDS). Lab reports are re-verified per batch shipment.",
              },
              {
                q: "How does logistics and dispatch time calculation work?",
                a: "Dispatch lead times (e.g. 24-48 hours) are specified by the seller based on stock location. Once an order is accepted, FuelCab logistics or vendor transport fleets coordinate door-to-door delivery with live GPS status.",
              },
              {
                q: "How do I become a seller on FuelCab Marketplace?",
                a: "Click on 'Become a Vendor' in the main navigation or hero section, complete the vendor registration form with company details, and submit your product catalog for Super Admin review.",
              },
            ].map((faq, idx) => (
              <details
                key={idx}
                className="bg-white rounded-2xl border border-[#e7ece8] p-5 [&_svg]:open:rotate-180 group transition-all"
              >
                <summary className="font-bold text-sm sm:text-base text-[#1a1a1a] cursor-pointer flex justify-between items-center gap-4 list-none">
                  <span>{faq.q}</span>
                  <ChevronDown className="w-4 h-4 text-[#155c32] shrink-0 transition-transform duration-200" />
                </summary>
                <p className="mt-3 text-xs sm:text-sm text-[#555555] leading-relaxed border-t border-[#e7ece8] pt-3">
                  {faq.a}
                </p>
              </details>
            ))}
          </div>

        </div>
      </section>


      {/* ────────────────────────────────────────────────────────────────────── */}
      {/* 9. LISTING SPECIFICATION & QUOTE REQUEST MODAL                        */}
      {/* ────────────────────────────────────────────────────────────────────── */}
      <AnimatePresence>
        {selectedListing && (
          <div
            className="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
          >
            {/* Backdrop */}
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              onClick={() => setSelectedListing(null)}
              className="fixed inset-0 bg-black/60 backdrop-blur-sm"
            />

            {/* Modal Dialog */}
            <motion.div
              initial={{ opacity: 0, scale: 0.95, y: 10 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.95, y: 10 }}
              className="relative bg-white rounded-3xl max-w-3xl w-full border border-[#e7ece8] shadow-2xl overflow-hidden z-10 max-h-[90vh] flex flex-col text-[#1a1a1a]"
            >
              {/* Modal Header */}
              <div className="p-6 border-b border-[#e7ece8] flex justify-between items-start gap-4 bg-[#f4f8f5]">
                <div>
                  <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white text-[#155c32] text-[10px] font-bold border border-[#e7ece8] mb-2">
                    {selectedListing.category_name}
                  </span>
                  <h2 id="modal-title" className="text-xl font-bold text-[#1a1a1a]">
                    {selectedListing.listing_title}
                  </h2>
                </div>
                <button
                  onClick={() => setSelectedListing(null)}
                  className="p-1.5 rounded-xl hover:bg-white text-[#555555] transition-colors"
                  aria-label="Close modal"
                >
                  <X className="w-5 h-5" />
                </button>
              </div>

              {/* Modal Content */}
              <div className="p-6 overflow-y-auto flex-1 space-y-6">
                
                {/* Vendor Information Banner */}
                <div className="p-4 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8] flex items-center justify-between gap-4">
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-xl bg-[#155c32] text-white flex items-center justify-center shrink-0">
                      <Building2 className="w-5 h-5" />
                    </div>
                    <div>
                      <span className="text-[10px] font-bold uppercase tracking-wider text-[#555555]">Verified Seller</span>
                      <h4 className="text-sm font-bold text-[#155c32]">{selectedListing.vendor.brand_name}</h4>
                      <p className="text-[10px] text-[#555555]">{selectedListing.vendor.city}, {selectedListing.vendor.state}</p>
                    </div>
                  </div>

                  <span className="inline-flex items-center gap-1 text-xs font-bold text-[#33b248] bg-[#33b248]/10 px-3 py-1.5 rounded-full border border-[#33b248]/20">
                    <CheckCircle2 className="w-3.5 h-3.5" />
                    Verified Vendor
                  </span>
                </div>

                {/* Description */}
                <div>
                  <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-2">
                    Product Description
                  </h3>
                  <p className="text-xs text-[#555555] leading-relaxed">
                    {selectedListing.full_description || selectedListing.short_description}
                  </p>
                </div>

                {/* Price and Stock Grid */}
                <div className="grid grid-cols-2 gap-4">
                  <div className="p-4 rounded-2xl border border-[#e7ece8] space-y-1">
                    <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">Base Price</span>
                    <div className="text-lg font-bold text-[#1a1a1a]">
                      ₹{selectedListing.base_price.toLocaleString()} <span className="text-xs text-[#555555]">/ {selectedListing.unit}</span>
                    </div>
                    <span className="text-[10px] text-[#555555] block">
                      {selectedListing.tax_inclusive ? "GST Inclusive" : `+${selectedListing.tax_rate}% GST Extra`}
                    </span>
                  </div>

                  <div className="p-4 rounded-2xl border border-[#e7ece8] space-y-1">
                    <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">Available Stock</span>
                    <div className="text-lg font-bold text-[#1a1a1a]">
                      {selectedListing.available_quantity.toLocaleString()} <span className="text-xs text-[#555555]">{selectedListing.unit}</span>
                    </div>
                    <span className="text-[10px] text-[#555555] block">
                      MOQ: {selectedListing.min_order_quantity} {selectedListing.unit}
                    </span>
                  </div>
                </div>

                {/* Quality Specifications (JSONB Schema) */}
                <div>
                  <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-3">
                    Quality Specifications (Audited Lab Schema)
                  </h3>
                  <div className="grid grid-cols-2 gap-px bg-[#e7ece8] rounded-xl overflow-hidden border border-[#e7ece8]">
                    {Object.entries(selectedListing.quality_specifications).map(([key, val]) => (
                      <div key={key} className="bg-white p-3 flex flex-col gap-0.5">
                        <span className="text-[10px] text-[#555555] font-semibold">{key}</span>
                        <span className="text-xs font-bold text-[#1a1a1a]">{val}</span>
                      </div>
                    ))}
                  </div>
                </div>

                {/* B2B Quote Request Form */}
                <div className="border-t border-[#e7ece8] pt-6">
                  <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-4">
                    Request Custom B2B Quote / Callback
                  </h3>

                  {inquirySuccess ? (
                    <div className="p-4 bg-[#f4f8f5] border border-[#33b248] rounded-2xl flex items-center gap-3 text-sm text-[#155c32] font-bold">
                      <CheckCircle2 className="w-5 h-5 text-[#33b248]" />
                      <span>Quote request submitted! Vendor representative will contact you shortly.</span>
                    </div>
                  ) : (
                    <form onSubmit={handleInquirySubmit} className="space-y-4">
                      <div className="grid grid-cols-2 gap-4">
                        <input
                          type="text"
                          placeholder="Your Name"
                          required
                          className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                        />
                        <input
                          type="tel"
                          placeholder="Company Mobile Number"
                          required
                          className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                        />
                      </div>

                      <div className="grid grid-cols-2 gap-4">
                        <input
                          type="text"
                          placeholder="Required Quantity (e.g. 100 MT)"
                          required
                          className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                        />
                        <button
                          type="submit"
                          disabled={inquiryLoading}
                          className={cn(
                            buttonVariants({ variant: "default" }),
                            "h-11 rounded-xl bg-[#155c32] hover:bg-[#0d3a1f] text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all"
                          )}
                        >
                          {inquiryLoading ? (
                            <span className="w-4 h-4 rounded-full border-2 border-white/20 border-t-white animate-spin" />
                          ) : (
                            <>
                              Submit Quote Request
                              <FileText className="w-3.5 h-3.5" />
                            </>
                          )}
                        </button>
                      </div>
                    </form>
                  )}
                </div>

              </div>
            </motion.div>
          </div>
        )}
      </AnimatePresence>

    </div>
  );
}

// Wrap inside Suspense for Next.js App Router query params compatibility
export default function MarketplacePage() {
  return (
    <Suspense
      fallback={
        <div className="py-24 text-center space-y-3 bg-[#fafbfa]">
          <div className="w-8 h-8 rounded-full border-4 border-[#33b248]/20 border-t-[#155c32] animate-spin mx-auto" />
          <p className="text-sm text-[#555555]">Loading FuelCab Marketplace...</p>
        </div>
      }
    >
      <MarketplaceContent />
    </Suspense>
  );
}
