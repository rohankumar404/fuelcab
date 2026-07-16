"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { 
  Search, 
  MapPin, 
  Flame, 
  Droplet, 
  Wind, 
  Zap, 
  CheckCircle2, 
  Clock, 
  TrendingUp, 
  ArrowRight, 
  Building2, 
  Filter, 
  X,
  FileText,
  AlertCircle
} from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Footer from "@/components/layout/Footer";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

// Mock Listings to fall back on if API fails or is offline
const MOCK_LISTINGS = [
  {
    id: "l-1",
    listing_title: "Premium Biomass Briquettes - Gujarat Supply",
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
      "GCV": "3800 - 4200 kcal/kg",
      "Moisture": "Max 8%",
      "Ash Content": "Max 7%",
      "Sulphur": "Less than 0.1%",
      "Density": "1.2 g/cm³"
    },
    vendor: {
      brand_name: "Gujarat Bio-Energy Ltd",
      city: "Surat",
      state: "Gujarat"
    },
    is_featured: true
  },
  {
    id: "l-2",
    listing_title: "Industrial Bio-Diesel (B-100) - Mumbai Supply",
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
      "Flash Point": "Min 130°C",
      "Density at 15°C": "875 - 900 kg/m³",
      "Viscosity at 40°C": "3.5 - 5.0 cSt",
      "Water Content": "Max 500 mg/kg",
      "Cetane Number": "Min 51"
    },
    vendor: {
      brand_name: "Apex Biofuels Logistics",
      city: "Navi Mumbai",
      state: "Maharashtra"
    },
    is_featured: true
  },
  {
    id: "l-3",
    listing_title: "Compressed Natural Gas (CNG) - NCR Distribution",
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
      "Gross Calorific Value": "11500 kcal/kg"
    },
    vendor: {
      brand_name: "NCR Gas Suppliers",
      city: "Gurugram",
      state: "Haryana"
    },
    is_featured: false
  },
  {
    id: "l-4",
    listing_title: "High Calorific Bio-Furnace Oil",
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
      "Viscosity at 50°C": "125 cSt",
      "Flash Point": "Min 66°C",
      "Ash Content": "Max 0.1%"
    },
    vendor: {
      brand_name: "Western India Eco-Fuels",
      city: "Vadodara",
      state: "Gujarat"
    },
    is_featured: false
  },
  {
    id: "l-5",
    listing_title: "Premium Rice Husk / Paddy Husk - Punjab Supply",
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
      "GCV": "3200 kcal/kg",
      "Moisture": "Max 10%",
      "Ash Content": "Max 18%",
      "Bulk Density": "100 kg/m³"
    },
    vendor: {
      brand_name: "Punjab Agro Products",
      city: "Ludhiana",
      state: "Punjab"
    },
    is_featured: false
  }
];

export default function MarketplacePage() {
  const [listings, setListings] = useState<any[]>(MOCK_LISTINGS);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState("");
  const [locationSearch, setLocationSearch] = useState("");
  const [categoryFilter, setCategoryFilter] = useState("All");
  const [sortBy, setSortBy] = useState("featured");
  
  // Modal details view
  const [selectedListing, setSelectedListing] = useState<any>(null);
  const [inquirySuccess, setInquirySuccess] = useState(false);
  const [inquiryLoading, setInquiryLoading] = useState(false);

  useEffect(() => {
    async function fetchListings() {
      try {
        const res = await fetch("http://localhost:8000/api/v1/marketplace/listings", {
          method: "GET",
          headers: {
            "Accept": "application/json"
          }
        });
        if (res.ok) {
          const data = await res.json();
          if (data?.data && data.data.length > 0) {
            setListings(data.data);
          }
        }
      } catch (err) {
        console.warn("Backend API not reachable. Falling back to high-fidelity mock B2B catalog.", err);
      } finally {
        setLoading(false);
      }
    }
    fetchListings();
  }, []);

  // Filter listings
  const filteredListings = listings.filter((item) => {
    const matchesSearch = item.listing_title.toLowerCase().includes(search.toLowerCase()) ||
                          item.marketplace_product.toLowerCase().includes(search.toLowerCase());
    
    const matchesLocation = !locationSearch || 
                            item.dispatch_location.toLowerCase().includes(locationSearch.toLowerCase()) ||
                            item.serviceable_locations.some((loc: string) => loc.toLowerCase().includes(locationSearch.toLowerCase()));
    
    const matchesCategory = categoryFilter === "All" || 
                            item.category_name === categoryFilter;
    
    return matchesSearch && matchesLocation && matchesCategory;
  });

  // Sort listings
  const sortedListings = [...filteredListings].sort((a, b) => {
    if (sortBy === "price-asc") {
      return a.base_price - b.base_price;
    }
    if (sortBy === "price-desc") {
      return b.base_price - a.base_price;
    }
    if (sortBy === "stock-desc") {
      return b.available_quantity - a.available_quantity;
    }
    // Default: featured first, then newest
    if (a.is_featured && !b.is_featured) return -1;
    if (!a.is_featured && b.is_featured) return 1;
    return b.id.localeCompare(a.id);
  });

  const handleInquirySubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setInquiryLoading(true);
    setTimeout(() => {
      setInquiryLoading(false);
      setInquirySuccess(true);
      setTimeout(() => {
        setInquirySuccess(false);
        setSelectedListing(null);
      }, 3000);
    }, 1500);
  };

  return (
    <>
      <AnnouncementBar />
      <Navbar />

      <main className="flex-1 bg-[#fafbfa]">
        {/* B2B Industrial Hero Section */}
        <section className="bg-[#0d3a1f] text-white py-20 relative overflow-hidden">
          <div className="absolute inset-0 z-0 pointer-events-none opacity-20">
            <div className="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-[#33b248]/20 blur-3xl" />
            <div className="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full bg-white/5 blur-3xl" />
          </div>

          <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
            <div className="max-w-3xl space-y-4">
              <span className="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-[#33b248]/15 border border-[#33b248]/30">
                <span className="w-2 h-2 rounded-full bg-[#33b248] animate-pulse-dot" />
                <span className="text-[10px] font-extrabold uppercase tracking-widest text-[#33b248]">
                  Verified B2B Supplier Network
                </span>
              </span>
              <h1 className="text-4xl sm:text-5xl font-extrabold tracking-tight">
                FuelCab Marketplace
              </h1>
              <p className="text-gray-300 text-base sm:text-lg max-w-2xl leading-relaxed">
                Source solid biomass, alternative liquid biofuels, industrial gases, and EV charging solutions directly from compliance-verified bulk producers across India.
              </p>
            </div>

            {/* Premium Interactive Search Panel */}
            <div className="mt-10 bg-white rounded-2xl shadow-xl shadow-[#0d3a1f]/30 border border-[#e7ece8]/10 p-4 sm:p-6 text-[#1a1a1a]">
              <div className="grid sm:grid-cols-12 gap-4">
                <div className="sm:col-span-5 relative">
                  <Search className="absolute left-4 top-3.5 w-5 h-5 text-[#555555]/60" />
                  <input
                    type="text"
                    placeholder="Search fuels e.g. Biomass, Bio-Diesel, Briquettes..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="w-full h-12 pl-12 pr-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 focus:outline-none"
                    aria-label="Search fuels input"
                  />
                </div>
                <div className="sm:col-span-4 relative">
                  <MapPin className="absolute left-4 top-3.5 w-5 h-5 text-[#555555]/60" />
                  <input
                    type="text"
                    placeholder="Dispatch or serviceable location..."
                    value={locationSearch}
                    onChange={(e) => setLocationSearch(e.target.value)}
                    className="w-full h-12 pl-12 pr-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:ring-2 focus:ring-[#33b248]/20 focus:outline-none"
                    aria-label="Location search input"
                  />
                </div>
                <div className="sm:col-span-3 flex gap-2">
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value)}
                    className="w-full h-12 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-sm focus:border-[#33b248] focus:outline-none cursor-pointer"
                    aria-label="Sort listings select"
                  >
                    <option value="featured">Sort: Featured</option>
                    <option value="price-asc">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="stock-desc">Available Stock</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </section>

        {/* Directory Listings Section */}
        <section className="py-16 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          {/* Category Tabs & Stats */}
          <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 border-b border-[#e7ece8] pb-6 mb-10">
            <div className="flex flex-wrap gap-2">
              {["All", "Solid Fuels", "Liquid Fuels", "Gas Fuels", "EV Charging"].map((cat) => {
                const isSelected = categoryFilter === cat;
                const isEV = cat === "EV Charging";

                return (
                  <button
                    key={cat}
                    onClick={() => !isEV && setCategoryFilter(cat)}
                    className={cn(
                      "px-5 py-2.5 rounded-xl text-xs font-bold transition-all relative border",
                      isSelected
                        ? "bg-[#155c32] text-white border-transparent shadow-md"
                        : "bg-white text-[#555555] border-[#e7ece8] hover:border-[#33b248] hover:text-[#155c32]",
                      isEV && "opacity-60 cursor-not-allowed"
                    )}
                    disabled={isEV}
                  >
                    <span>{cat}</span>
                    {isEV && (
                      <span className="ml-1.5 text-[8px] font-extrabold uppercase bg-[#e7ece8] text-[#555555] px-1.5 py-0.5 rounded-full">
                        Soon
                      </span>
                    )}
                  </button>
                );
              })}
            </div>
            
            <div className="text-xs text-[#555555] font-semibold bg-[#f4f8f5] px-4 py-2 rounded-xl border border-[#e7ece8]">
              Showing <span className="text-[#155c32]">{sortedListings.length}</span> energy solutions
            </div>
          </div>

          {/* Directory Listings Grid */}
          {loading ? (
            <div className="py-20 text-center space-y-3">
              <div className="w-8 h-8 rounded-full border-4 border-[#33b248]/20 border-t-[#155c32] animate-spin mx-auto" />
              <p className="text-sm text-[#555555]">Loading energy solution catalog...</p>
            </div>
          ) : sortedListings.length === 0 ? (
            <div className="py-20 text-center space-y-4 bg-white rounded-2xl border border-[#e7ece8] p-8">
              <AlertCircle className="w-12 h-12 text-[#ffb400] mx-auto" />
              <div className="space-y-1">
                <h3 className="text-base font-bold text-[#1a1a1a]">No matching solutions found</h3>
                <p className="text-xs text-[#555555] max-w-md mx-auto">Try broadening your search keywords, clearing location filters, or exploring other category tabs.</p>
              </div>
              <button
                onClick={() => { setSearch(""); setLocationSearch(""); setCategoryFilter("All"); }}
                className={cn(buttonVariants({ variant: "outline" }), "rounded-xl border-[#e7ece8]")}
              >
                Clear All Filters
              </button>
            </div>
          ) : (
            <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {sortedListings.map((item) => {
                const isSolid = item.category_name === "Solid Fuels";
                const isLiquid = item.category_name === "Liquid Fuels";
                const Icon = isSolid ? Flame : isLiquid ? Droplet : Wind;

                return (
                  <div
                    key={item.id}
                    className="bg-white rounded-2xl border border-[#e7ece8] hover:border-[#33b248] p-6 transition-all duration-300 hover:shadow-xl hover:shadow-[#155c32]/4 flex flex-col justify-between group relative"
                  >
                    <div>
                      {/* Badge / Category */}
                      <div className="flex justify-between items-start mb-4">
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#f4f8f5] text-[#555555] text-[10px] font-bold border border-[#e7ece8]">
                          <Icon className="w-3 h-3 text-[#33b248]" />
                          {item.category_name}
                        </span>
                        
                        {item.is_featured && (
                          <span className="text-[9px] font-extrabold uppercase tracking-wider text-[#0d3a1f] bg-[#ffb400] px-2.5 py-0.5 rounded-full shadow-sm shadow-[#ffb400]/30">
                            Featured
                          </span>
                        )}
                      </div>

                      {/* Title */}
                      <h3 className="text-lg font-bold text-[#1a1a1a] mb-2 line-clamp-2 min-h-[3.5rem] group-hover:text-[#155c32] transition-colors duration-150">
                        {item.listing_title}
                      </h3>

                      {/* Product Master Tag */}
                      <p className="text-xs text-[#555555] mb-4 flex items-center gap-1.5 font-medium">
                        <Building2 className="w-3.5 h-3.5 text-[#555555]/60" />
                        Master: {item.marketplace_product}
                      </p>

                      {/* Location Details */}
                      <div className="space-y-1.5 mb-6 text-xs text-[#555555]">
                        <div className="flex items-center gap-2">
                          <MapPin className="w-3.5 h-3.5 text-[#555555]/60" />
                          <span>Dispatch: <strong className="text-[#1a1a1a] font-semibold">{item.dispatch_location}</strong></span>
                        </div>
                        <div className="flex items-center gap-2">
                          <Clock className="w-3.5 h-3.5 text-[#555555]/60" />
                          <span>Est. Dispatch: <strong className="text-[#1a1a1a] font-semibold">{item.estimated_dispatch_hours} hours</strong></span>
                        </div>
                      </div>
                    </div>

                    {/* Pricing & CTA */}
                    <div className="border-t border-[#e7ece8] pt-4 mt-auto">
                      <div className="flex justify-between items-end mb-4">
                        <div className="space-y-0.5">
                          <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">
                            Base Price
                          </span>
                          <div className="flex items-baseline gap-1 text-[#1a1a1a]">
                            <span className="text-xl font-extrabold">₹{item.base_price}</span>
                            <span className="text-xs text-[#555555] font-semibold">/ {item.unit}</span>
                          </div>
                        </div>
                        
                        <div className="text-right text-[10px] text-[#555555] font-semibold">
                          Min. Order: {item.min_order_quantity} {item.unit}
                        </div>
                      </div>

                      <button
                        onClick={() => setSelectedListing(item)}
                        className={cn(
                          buttonVariants({ variant: "default" }),
                          "w-full h-10 rounded-xl bg-[#155c32] text-white hover:bg-[#0d3a1f] font-semibold text-xs transition-all duration-200"
                        )}
                        aria-label={`View details and specs for ${item.listing_title}`}
                      >
                        View Details & Specifications
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          )}

          {/* Become a Vendor Bottom CTA */}
          <div className="mt-20 p-8 sm:p-12 rounded-3xl bg-[#0d3a1f] text-white border border-[#155c32] relative overflow-hidden">
            <div className="absolute inset-0 pointer-events-none opacity-20">
              <div className="absolute top-0 right-0 w-[400px] h-[400px] bg-[#33b248] rounded-full blur-3xl" />
            </div>
            
            <div className="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
              <div className="space-y-3 max-w-2xl">
                <span className="inline-block text-[10px] font-extrabold uppercase tracking-widest text-[#33b248]">
                  Partner Registration Open
                </span>
                <h2 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                  List Your Energy Products on FuelCab
                </h2>
                <p className="text-gray-300 text-sm leading-relaxed">
                  Join India's leading industrial energy marketplace. Connect with heavy manufacturing units, logistics hubs, and commercial operations purchasing bulk fuels.
                </p>
              </div>

              <div className="flex flex-wrap gap-4 w-full sm:w-auto">
                <Link
                  href="/vendor/register"
                  className={cn(
                    buttonVariants({ variant: "default" }),
                    "h-12 px-6 rounded-xl bg-[#33b248] hover:bg-[#28923a] text-white font-bold text-sm shadow-lg shadow-[#33b248]/25 transition-all duration-200 flex items-center justify-center"
                  )}
                  aria-label="Become a seller on our marketplace platform"
                >
                  Register as Vendor
                </Link>
                <Link
                  href="/#faqs"
                  className={cn(
                    buttonVariants({ variant: "outline" }),
                    "h-12 px-6 rounded-xl border-white/20 text-white hover:bg-white/10 transition-all duration-200 flex items-center justify-center"
                  )}
                  aria-label="Read faq details for marketplace vendors"
                >
                  View Vendor FAQs
                </Link>
              </div>
            </div>
          </div>
        </section>
      </main>

      {/* Listing Detail Modal Backdrop */}
      {selectedListing && (
        <div 
          className="fixed inset-0 z-50 bg-[#1a1a1a]/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
          role="dialog"
          aria-modal="true"
          aria-label="Listing detail modal overlay"
        >
          <div className="bg-white rounded-3xl max-w-2xl w-full border border-[#e7ece8] shadow-2xl overflow-hidden relative animate-fade-up max-h-[90vh] flex flex-col text-[#1a1a1a]">
            {/* Modal Header */}
            <div className="p-6 border-b border-[#e7ece8] flex justify-between items-start gap-4">
              <div>
                <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-[#f4f8f5] text-[#555555] text-[10px] font-bold border border-[#e7ece8] mb-2">
                  {selectedListing.category_name}
                </span>
                <h2 className="text-xl font-bold text-[#1a1a1a]">
                  {selectedListing.listing_title}
                </h2>
              </div>
              <button 
                onClick={() => setSelectedListing(null)}
                className="p-1.5 rounded-xl hover:bg-[#f4f8f5] text-[#555555] transition-colors duration-150"
                aria-label="Close details popup modal"
              >
                <X className="w-5 h-5" />
              </button>
            </div>

            {/* Modal Body */}
            <div className="p-6 overflow-y-auto flex-1 space-y-6">
              
              {/* Supplier Info Banner */}
              <div className="p-4 rounded-2xl bg-[#f4f8f5] border border-[#e7ece8] flex items-center gap-4">
                <div className="w-10 h-10 rounded-xl bg-[#155c32] flex items-center justify-center flex-shrink-0">
                  <Building2 className="w-5 h-5 text-white" />
                </div>
                <div>
                  <h4 className="text-xs font-bold text-[#1a1a1a]">Verified Seller</h4>
                  <p className="text-sm font-semibold text-[#155c32]">{selectedListing.vendor.brand_name}</p>
                  <p className="text-[10px] text-[#555555]">{selectedListing.vendor.city}, {selectedListing.vendor.state}</p>
                </div>
              </div>

              {/* Pricing and Stock Summary */}
              <div className="grid grid-cols-2 gap-4">
                <div className="p-4 rounded-2xl border border-[#e7ece8] space-y-1">
                  <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">Base Price</span>
                  <div className="text-lg font-bold text-[#1a1a1a]">
                    ₹{selectedListing.base_price} <span className="text-xs font-semibold text-[#555555]">/ {selectedListing.unit}</span>
                  </div>
                  <span className="text-[9px] text-[#555555] block">
                    {selectedListing.tax_inclusive ? "Tax Inclusive" : `+${selectedListing.tax_rate}% GST extra`}
                  </span>
                </div>
                
                <div className="p-4 rounded-2xl border border-[#e7ece8] space-y-1">
                  <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider">Available Stock</span>
                  <div className="text-lg font-bold text-[#1a1a1a]">
                    {selectedListing.available_quantity.toLocaleString()} <span className="text-xs font-semibold text-[#555555]">{selectedListing.unit}</span>
                  </div>
                  <span className="text-[9px] text-[#555555] block">
                    Min Order: {selectedListing.min_order_quantity} {selectedListing.unit}
                  </span>
                </div>
              </div>

              {/* Quality Specifications */}
              <div>
                <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-3">
                  Quality Specifications (JSONB Schema)
                </h3>
                <div className="grid grid-cols-2 gap-px bg-[#e7ece8] rounded-xl overflow-hidden border border-[#e7ece8]">
                  {Object.entries(selectedListing.quality_specifications).map(([key, val]: any) => (
                    <div key={key} className="bg-white p-3 flex flex-col gap-1">
                      <span className="text-[10px] text-[#555555] font-semibold">{key}</span>
                      <span className="text-xs font-bold text-[#1a1a1a]">{val}</span>
                    </div>
                  ))}
                </div>
              </div>

              {/* Logistics Details */}
              <div>
                <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-3">
                  Logistics and Service
                </h3>
                <div className="space-y-2 text-xs text-[#555555]">
                  <div className="flex items-center gap-2">
                    <MapPin className="w-4 h-4 text-[#33b248]" />
                    <span>Dispatch Warehouse: <strong className="text-[#1a1a1a]">{selectedListing.dispatch_location}</strong></span>
                  </div>
                  <div className="flex items-center gap-2">
                    <Clock className="w-4 h-4 text-[#33b248]" />
                    <span>Estimated Dispatch Window: <strong className="text-[#1a1a1a]">{selectedListing.estimated_dispatch_hours} hours</strong></span>
                  </div>
                  <div className="flex flex-wrap gap-1.5 mt-2">
                    <span className="text-[10px] font-bold text-[#555555] self-center mr-1">Serviceable states:</span>
                    {selectedListing.serviceable_locations.map((loc: string) => (
                      <span key={loc} className="px-2 py-0.5 bg-[#f4f8f5] rounded-md text-[10px] text-[#155c32] font-semibold border border-[#e7ece8]">
                        {loc}
                      </span>
                    ))}
                  </div>
                </div>
              </div>

              {/* Lead Inquiry Form */}
              <div className="border-t border-[#e7ece8] pt-6">
                <h3 className="text-xs font-bold uppercase tracking-widest text-[#555555] mb-4">
                  B2B Lead Inquiry / Quote Request
                </h3>
                {inquirySuccess ? (
                  <div className="p-4 bg-[#f4f8f5] border border-[#33b248] rounded-2xl flex items-center gap-3 text-sm text-[#155c32] font-bold">
                    <CheckCircle2 className="w-5 h-5 text-[#33b248]" />
                    <span>Inquiry submitted successfully! Vendor representative will call you.</span>
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
                        placeholder="Company Contact Number"
                        required
                        className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
                      />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <input
                        type="text"
                        placeholder="Required Quantity (e.g. 50 Tonnes)"
                        required
                        className="h-11 px-4 bg-[#f4f8f5] rounded-xl border border-[#e7ece8] text-xs focus:border-[#33b248] focus:outline-none"
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
                            Request Quote / Callback
                            <FileText className="w-3.5 h-3.5" />
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

      <Footer />
    </>
  );
}
