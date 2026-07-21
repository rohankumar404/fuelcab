import React from "react";
import Metadata from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import {
  Flame,
  Droplet,
  Wind,
  Zap,
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
  SlidersHorizontal,
} from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import {
  getProductMasterBySlug,
  getListingsByProductMaster,
  getRelatedProducts,
  ProductMaster,
  VendorListing,
} from "@/lib/marketplace-data";

interface Props {
  params: Promise<{
    categorySlug: string;
    productSlug: string;
  }>;
}

// ── Dynamic SEO Metadata Generation ───────────────────────────────────────────
export async function generateMetadata({ params }: Props) {
  const resolvedParams = await params;
  const product = getProductMasterBySlug(resolvedParams.categorySlug, resolvedParams.productSlug);

  if (!product) {
    return {
      title: "Product Master Not Found — FuelCab Marketplace",
    };
  }

  return {
    title: `${product.name} Suppliers & Specs — FuelCab Marketplace`,
    description: `Source ${product.name} (${product.code}) from verified suppliers across India. Standard GCV: ${product.gcv_range}. ${product.description}`,
    keywords: `${product.name}, ${product.category_name}, industrial fuel sourcing, B2B ${product.slug}, bulk fuel India`,
    openGraph: {
      title: `${product.name} — Approved Product Master Specs & Suppliers`,
      description: product.description,
      images: [{ url: product.product_image }],
    },
  };
}

// ── Category Icon Map ─────────────────────────────────────────────────────────
const CATEGORY_ICON: Record<string, React.ComponentType<{ className?: string }>> = {
  "Solid Fuels": Flame,
  "Liquid Fuels": Droplet,
  "Gas Fuels": Wind,
  "EV": Zap,
};

export default async function ProductMasterPage({ params }: Props) {
  const resolvedParams = await params;
  const product = getProductMasterBySlug(resolvedParams.categorySlug, resolvedParams.productSlug);

  if (!product) {
    notFound();
  }

  const listings = getListingsByProductMaster(product.slug);
  const relatedProducts = getRelatedProducts(product.slug, product.category_slug);
  const Icon = CATEGORY_ICON[product.category_name] || Flame;

  // JSON-LD Structured Data Schema for Search Engines
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Product",
    name: product.name,
    description: product.description,
    category: product.category_name,
    sku: product.code,
    image: product.product_image,
    offers: listings.map((l) => ({
      "@type": "Offer",
      name: l.listing_title,
      price: l.base_price,
      priceCurrency: "INR",
      availability: "https://schema.org/InStock",
      seller: {
        "@type": "Organization",
        name: l.vendor.brand_name,
      },
    })),
  };

  return (
    <>
      {/* Inject JSON-LD Schema */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />

      <div className="min-h-screen bg-[#fafbfa] text-[#1a1a1a]">

        {/* ── Breadcrumbs & Back Bar ────────────────────────────────────────── */}
        <div className="bg-white border-b border-[#e7ece8]">
          <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-3">
            <nav className="flex items-center gap-2 text-xs font-semibold text-[#555555]">
              <Link href="/" className="hover:text-[#155c32] transition-colors">Home</Link>
              <ChevronRight className="w-3.5 h-3.5 text-gray-400" />
              <Link href="/marketplace" className="hover:text-[#155c32] transition-colors">Marketplace</Link>
              <ChevronRight className="w-3.5 h-3.5 text-gray-400" />
              <span className="capitalize">{product.category_name}</span>
              <ChevronRight className="w-3.5 h-3.5 text-gray-400" />
              <span className="text-[#155c32] font-bold">{product.name}</span>
            </nav>
          </div>
        </div>

        {/* ── Product Master Hero Section ───────────────────────────────────── */}
        <section className="bg-[#0d3a1f] text-white py-16 lg:py-20 relative overflow-hidden">
          <div className="absolute inset-0 pointer-events-none opacity-25" aria-hidden="true">
            <div className="absolute top-0 right-10 w-[500px] h-[500px] rounded-full bg-[#33b248]/20 blur-3xl" />
          </div>

          <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
            <div className="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">
              
              <div className="lg:col-span-8 space-y-4">
                <div className="flex flex-wrap items-center gap-2.5">
                  <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#33b248]/15 border border-[#33b248]/30 text-[#33b248] text-xs font-bold uppercase tracking-wider">
                    <Icon className="w-3.5 h-3.5" />
                    {product.category_name}
                  </span>
                  <span className="text-xs font-mono bg-white/10 px-2.5 py-1 rounded text-gray-200">
                    Master Code: {product.code}
                  </span>
                  <span className="text-xs font-bold text-[#ffb400] bg-[#ffb400]/15 px-3 py-1 rounded-full border border-[#ffb400]/30">
                    Super Admin Approved Master
                  </span>
                </div>

                <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight">
                  {product.name}
                </h1>

                <p className="text-gray-300 text-base sm:text-lg max-w-3xl leading-relaxed">
                  {product.description}
                </p>

                <div className="flex flex-wrap gap-4 pt-2">
                  <div className="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm space-y-0.5">
                    <span className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Standard GCV Range</span>
                    <p className="text-base font-extrabold text-[#33b248]">{product.gcv_range}</p>
                  </div>
                  <div className="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm space-y-0.5">
                    <span className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Trading Unit</span>
                    <p className="text-base font-extrabold text-white capitalize">{product.standard_unit.replace("_", " ")}</p>
                  </div>
                  <div className="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm space-y-0.5">
                    <span className="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Active Verified Vendors</span>
                    <p className="text-base font-extrabold text-white">{listings.length} Active Listings</p>
                  </div>
                </div>
              </div>

              <div className="lg:col-span-4">
                <div className="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl h-64 lg:h-72">
                  <img
                    src={product.product_image}
                    alt={product.name}
                    className="w-full h-full object-cover"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent flex items-end p-4">
                    <p className="text-xs text-gray-200 font-semibold flex items-center gap-1.5">
                      <ShieldCheck className="w-4 h-4 text-[#33b248]" />
                      Verified Industrial Grade Energy Standard
                    </p>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </section>

        {/* ── Main Content Area: Master Specs + Vendor Listings Catalog ────── */}
        <section className="py-16 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
          
          <div className="grid lg:grid-cols-12 gap-10">
            
            {/* Left Column: Master Specifications & Requirements */}
            <aside className="lg:col-span-4 space-y-8">
              
              {/* Product Specifications Card */}
              <div className="bg-white rounded-2xl p-6 border border-[#e7ece8] shadow-sm space-y-4">
                <h2 className="text-base font-bold text-[#1a1a1a] flex items-center gap-2 border-b border-[#e7ece8] pb-3">
                  <SlidersHorizontal className="w-4 h-4 text-[#155c32]" />
                  Master Product Specifications
                </h2>

                <p className="text-xs text-[#555555] leading-relaxed">
                  All vendor listings under this master category must comply with these technical parameters.
                </p>

                <div className="space-y-2.5 pt-1">
                  {Object.entries(product.specifications_schema).map(([key, val]) => (
                    <div key={key} className="p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] flex justify-between items-center text-xs">
                      <span className="text-[#555555] font-semibold">{key}</span>
                      <strong className="text-[#1a1a1a] font-bold">{val}</strong>
                    </div>
                  ))}
                </div>
              </div>

              {/* Quality & Procurement Assurance */}
              <div className="bg-[#0d3a1f] text-white rounded-2xl p-6 space-y-4 shadow-lg">
                <h3 className="text-sm font-bold flex items-center gap-2 text-[#33b248]">
                  <Award className="w-4 h-4" />
                  FuelCab Quality Guarantee
                </h3>
                <ul className="space-y-2.5 text-xs text-gray-300">
                  <li className="flex items-start gap-2">
                    <CheckCircle2 className="w-4 h-4 text-[#33b248] shrink-0 mt-0.5" />
                    <span>Every batch verified with independent NABL lab report.</span>
                  </li>
                  <li className="flex items-start gap-2">
                    <CheckCircle2 className="w-4 h-4 text-[#33b248] shrink-0 mt-0.5" />
                    <span>Transparent tax breakdown (+5% / 18% GST).</span>
                  </li>
                  <li className="flex items-start gap-2">
                    <CheckCircle2 className="w-4 h-4 text-[#33b248] shrink-0 mt-0.5" />
                    <span>Vendor identity & GSTIN pre-checked by Super Admin.</span>
                  </li>
                </ul>
              </div>

            </aside>

            {/* Right Column: Approved Vendor Listings Cards */}
            <main className="lg:col-span-8 space-y-6">
              
              <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 border-b border-[#e7ece8] pb-4">
                <div>
                  <span className="text-[10px] font-extrabold uppercase tracking-widest text-[#33b248] block">
                    Verified Vendor Offers
                  </span>
                  <h2 className="text-xl font-bold text-[#1a1a1a]">
                    Available Vendor Listings ({listings.length})
                  </h2>
                </div>

                <span className="text-xs text-[#555555] font-semibold bg-[#f4f8f5] px-3.5 py-1.5 rounded-xl border border-[#e7ece8]">
                  Only Approved & Active Sellers
                </span>
              </div>

              {listings.length === 0 ? (
                <div className="bg-white rounded-2xl border border-[#e7ece8] p-12 text-center space-y-3">
                  <Package className="w-12 h-12 text-[#ffb400] mx-auto" />
                  <h3 className="text-base font-bold text-[#1a1a1a]">No Active Vendor Listings Currently</h3>
                  <p className="text-xs text-[#555555] max-w-sm mx-auto">
                    Be the first vendor to list {product.name} on FuelCab Marketplace.
                  </p>
                  <Link
                    href="/vendor/register"
                    className={cn(buttonVariants({ variant: "default" }), "rounded-xl bg-[#155c32] text-white text-xs")}
                  >
                    Register as Vendor
                  </Link>
                </div>
              ) : (
                <div className="space-y-6">
                  {listings.map((item: VendorListing) => (
                    <article
                      key={item.id}
                      className="bg-white rounded-2xl border border-[#e7ece8] hover:border-[#33b248] p-6 transition-all duration-300 hover:shadow-xl hover:shadow-[#155c32]/5 group"
                    >
                      <div className="flex flex-col sm:flex-row justify-between items-start gap-4 mb-4">
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <span className="text-xs font-bold text-[#155c32] flex items-center gap-1">
                              <Building2 className="w-3.5 h-3.5" />
                              {item.vendor.brand_name}
                            </span>
                            {item.vendor.is_verified && (
                              <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-[#33b248]/15 text-[#155c32] border border-[#33b248]/30">
                                <CheckCircle2 className="w-3 h-3 text-[#33b248]" />
                                Verified Vendor
                              </span>
                            )}
                          </div>

                          <h3 className="text-lg font-bold text-[#1a1a1a] group-hover:text-[#155c32] transition-colors leading-snug">
                            <Link href={`/marketplace/listing/${item.slug}`}>
                              {item.listing_title}
                            </Link>
                          </h3>
                        </div>

                        <div className="text-left sm:text-right shrink-0">
                          <span className="text-[10px] uppercase font-bold text-[#555555]/70 tracking-wider block">
                            Base Price
                          </span>
                          <p className="text-xl font-extrabold text-[#1a1a1a]">
                            ₹{item.base_price.toLocaleString()} <span className="text-xs text-[#555555] font-semibold">/ {item.unit}</span>
                          </p>
                          <span className="text-[10px] text-[#555555] block">
                            {item.tax_inclusive ? "GST Inclusive" : `+${item.tax_rate}% GST`}
                          </span>
                        </div>
                      </div>

                      <p className="text-xs text-[#555555] leading-relaxed mb-4 line-clamp-2">
                        {item.short_description}
                      </p>

                      {/* Specs Highlights */}
                      <div className="grid grid-cols-2 sm:grid-cols-3 gap-2 p-3 rounded-xl bg-[#f4f8f5] border border-[#e7ece8] mb-5 text-xs">
                        {Object.entries(item.quality_specifications).slice(0, 3).map(([k, v]) => (
                          <div key={k} className="space-y-0.5">
                            <span className="text-[10px] text-[#555555] block truncate">{k}:</span>
                            <strong className="text-[#1a1a1a] font-semibold block truncate">{v}</strong>
                          </div>
                        ))}
                      </div>

                      {/* Logistics & CTA Row */}
                      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-t border-[#e7ece8] pt-4">
                        <div className="flex flex-wrap items-center gap-4 text-xs text-[#555555]">
                          <span className="flex items-center gap-1.5">
                            <MapPin className="w-3.5 h-3.5 text-[#33b248]" />
                            Dispatch: <strong className="text-[#1a1a1a]">{item.dispatch_location}</strong>
                          </span>
                          <span className="flex items-center gap-1.5">
                            <Clock className="w-3.5 h-3.5 text-[#33b248]" />
                            ETA: <strong className="text-[#1a1a1a]">{item.estimated_dispatch_hours}h</strong>
                          </span>
                          <span className="flex items-center gap-1.5">
                            <Package className="w-3.5 h-3.5 text-[#33b248]" />
                            MOQ: <strong className="text-[#1a1a1a]">{item.min_order_quantity} {item.unit}</strong>
                          </span>
                        </div>

                        <Link
                          href={`/marketplace/listing/${item.slug}`}
                          className={cn(
                            buttonVariants({ variant: "default" }),
                            "h-10 px-5 rounded-xl bg-[#155c32] hover:bg-[#0d3a1f] text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-all shrink-0 w-full sm:w-auto"
                          )}
                          aria-label={`View offer for ${item.listing_title}`}
                        >
                          View Offer
                          <ArrowRight className="w-3.5 h-3.5" />
                        </Link>
                      </div>
                    </article>
                  ))}
                </div>
              )}

            </main>

          </div>

        </section>

        {/* ── Related Product Masters Section ────────────────────────────────── */}
        <section className="py-16 bg-[#f4f8f5] border-t border-[#e7ece8]" aria-label="Related Master Products">
          <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 space-y-8">
            
            <div>
              <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] block">
                Explore Alternative Fuels
              </span>
              <h2 className="text-2xl font-bold text-[#1a1a1a]">
                Related Energy Products
              </h2>
            </div>

            <div className="grid sm:grid-cols-3 gap-6">
              {relatedProducts.map((rp) => (
                <div key={rp.id} className="bg-white rounded-2xl p-6 border border-[#e7ece8] hover:border-[#33b248] transition-all space-y-3 flex flex-col justify-between">
                  <div className="space-y-2">
                    <span className="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-[#f4f8f5] text-[#155c32]">
                      {rp.category_name}
                    </span>
                    <h3 className="text-base font-bold text-[#1a1a1a]">{rp.name}</h3>
                    <p className="text-xs text-[#555555] line-clamp-2">{rp.description}</p>
                  </div>
                  
                  <Link
                    href={`/marketplace/${rp.category_slug}/${rp.slug}`}
                    className="text-xs font-bold text-[#155c32] hover:text-[#33b248] flex items-center gap-1 pt-2 border-t border-[#e7ece8]"
                  >
                    View Product Specs
                    <ChevronRight className="w-3.5 h-3.5" />
                  </Link>
                </div>
              ))}
            </div>

          </div>
        </section>

      </div>
    </>
  );
}
