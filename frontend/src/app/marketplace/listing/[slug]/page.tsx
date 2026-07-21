import React from "react";
import Metadata from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { getListingBySlug, VendorListing } from "@/lib/marketplace-data";
import ListingDetailClient from "./ListingDetailClient";

interface Props {
  params: Promise<{
    slug: string;
  }>;
}

// ── Dynamic SEO Metadata Generation ───────────────────────────────────────────
export async function generateMetadata({ params }: Props) {
  const resolvedParams = await params;
  const listing = getListingBySlug(resolvedParams.slug);

  if (!listing) {
    return {
      title: "Vendor Listing Not Found — FuelCab Marketplace",
    };
  }

  return {
    title: `${listing.listing_title} — ${listing.vendor.brand_name} | FuelCab Marketplace`,
    description: `${listing.short_description} Base Price: ₹${listing.base_price}/${listing.unit}. Dispatch from ${listing.dispatch_location}.`,
    keywords: `${listing.listing_title}, ${listing.marketplace_product_name}, ${listing.vendor.brand_name}, bulk ${listing.unit} fuel India`,
    openGraph: {
      title: `${listing.listing_title} — Verified Bulk Fuel Supplier`,
      description: listing.short_description,
      images: listing.product_images.map((img) => ({ url: img })),
    },
  };
}

export default async function ListingDetailPage({ params }: Props) {
  const resolvedParams = await params;
  const listing = getListingBySlug(resolvedParams.slug);

  if (!listing) {
    notFound();
  }

  // JSON-LD Product & Offer Structured Data for Google Rich Results
  const jsonLd = {
    "@context": "https://schema.org",
    "@type": "Product",
    name: listing.listing_title,
    description: listing.full_description || listing.short_description,
    image: listing.product_images,
    sku: listing.sku,
    category: listing.category_name,
    offers: {
      "@type": "Offer",
      price: listing.base_price,
      priceCurrency: "INR",
      priceValidUntil: "2027-12-31",
      itemCondition: "https://schema.org/NewCondition",
      availability: listing.available_quantity > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
      seller: {
        "@type": "Organization",
        name: listing.vendor.brand_name,
        address: {
          "@type": "PostalAddress",
          addressLocality: listing.vendor.city,
          addressRegion: listing.vendor.state,
          addressCountry: "IN",
        },
      },
    },
  };

  return (
    <>
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }}
      />

      <ListingDetailClient listing={listing} />
    </>
  );
}
