import React from "react";
import Metadata from "next";
import { notFound } from "next/navigation";
import { getListingBySlug } from "@/lib/marketplace-data";
import ListingDetailClient from "@/app/marketplace/listing/[slug]/ListingDetailClient";

interface Props {
  params: Promise<{
    categorySlug: string;
    productSlug: string;
    listingSlug: string;
  }>;
}

export async function generateMetadata({ params }: Props) {
  const resolvedParams = await params;
  const listing = getListingBySlug(resolvedParams.listingSlug);

  if (!listing) {
    return {
      title: "Listing Not Found — FuelCab Marketplace",
    };
  }

  return {
    title: `${listing.listing_title} — ${listing.vendor.brand_name} | FuelCab Marketplace`,
    description: `${listing.short_description} Base Price: ₹${listing.base_price}/${listing.unit}. Dispatch from ${listing.dispatch_location}.`,
  };
}

export default async function NestedListingDetailPage({ params }: Props) {
  const resolvedParams = await params;
  const listing = getListingBySlug(resolvedParams.listingSlug);

  if (!listing) {
    notFound();
  }

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
      availability: listing.available_quantity > 0 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
      seller: {
        "@type": "Organization",
        name: listing.vendor.brand_name,
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
