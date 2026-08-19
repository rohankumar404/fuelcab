import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700", "800"],
  display: "swap",
});

const BASE_URL = process.env.NEXT_PUBLIC_SITE_URL || "https://fuelcab.com";

export const metadata: Metadata = {
  metadataBase: new URL(BASE_URL),
  title: {
    default: "FuelCab — B2B Fuel Delivery Platform",
    template: "%s | FuelCab",
  },
  description:
    "FuelCab is India's trusted on-demand diesel & commercial fuel delivery platform. Order Diesel, HSD, AdBlue, and lubricants delivered directly to your business site. Minimum 100 litres.",
  keywords: [
    "fuel delivery",
    "B2B diesel delivery",
    "bulk fuel India",
    "HSD delivery",
    "AdBlue supply",
    "lubricants bulk",
    "industrial fuel platform",
    "fleet fuel management",
    "on-site fuel delivery",
    "FuelCab",
  ],
  authors: [{ name: "FuelCab", url: BASE_URL }],
  creator: "FuelCab Technologies Pvt. Ltd.",
  publisher: "FuelCab Technologies Pvt. Ltd.",
  robots: {
    index: true,
    follow: true,
    googleBot: { index: true, follow: true, "max-image-preview": "large" },
  },
  openGraph: {
    type: "website",
    locale: "en_IN",
    url: BASE_URL,
    siteName: "FuelCab",
    title: "FuelCab — India's B2B Fuel Delivery Platform",
    description:
      "Order diesel, HSD, AdBlue, and lubricants delivered directly to your business site. Trusted by 500+ companies. Minimum 100 litres.",
    images: [
      {
        url: `${BASE_URL}/og-image.png`,
        width: 1200,
        height: 630,
        alt: "FuelCab — B2B Fuel Delivery Platform",
      },
    ],
  },
  twitter: {
    card: "summary_large_image",
    site: "@fuelcab",
    creator: "@fuelcab",
    title: "FuelCab — India's B2B Fuel Delivery Platform",
    description:
      "Order diesel, HSD, AdBlue, and lubricants delivered directly to your business site. Trusted by 500+ companies.",
    images: [`${BASE_URL}/og-image.png`],
  },
  alternates: {
    canonical: BASE_URL,
  },
  icons: {
    icon: "/favicon.ico",
    shortcut: "/favicon.ico",
  },
  category: "B2B Fuel Delivery",
};

// Organization Structured Data
const ORGANIZATION_SCHEMA = {
  "@context": "https://schema.org",
  "@type": "Organization",
  name: "FuelCab",
  url: BASE_URL,
  logo: `${BASE_URL}/logo.png`,
  description:
    "FuelCab is India's trusted B2B multi-vendor fuel delivery marketplace. Order diesel, HSD, AdBlue, and lubricants delivered to your business site.",
  contactPoint: {
    "@type": "ContactPoint",
    telephone: "+91-1800-100-200",
    contactType: "customer service",
    areaServed: "IN",
    availableLanguage: ["English", "Hindi"],
  },
  address: {
    "@type": "PostalAddress",
    streetAddress: "Sector-62, Industrial Area",
    addressLocality: "Noida",
    addressRegion: "Uttar Pradesh",
    postalCode: "201309",
    addressCountry: "IN",
  },
  sameAs: [
    "https://www.facebook.com/fuelcab",
    "https://twitter.com/fuelcab",
    "https://www.linkedin.com/company/fuelcab",
    "https://www.instagram.com/fuelcab",
  ],
};

// Website Structured Data
const WEBSITE_SCHEMA = {
  "@context": "https://schema.org",
  "@type": "WebSite",
  url: BASE_URL,
  name: "FuelCab",
  description: "India's B2B fuel delivery platform",
  potentialAction: {
    "@type": "SearchAction",
    target: {
      "@type": "EntryPoint",
      urlTemplate: `${BASE_URL}/marketplace?q={search_term_string}`,
    },
    "query-input": "required name=search_term_string",
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" suppressHydrationWarning className={`${inter.variable} h-full`}>
      <head>
        {/* Organization Schema */}
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(ORGANIZATION_SCHEMA) }}
        />
        {/* WebSite Schema with SearchAction */}
        <script
          type="application/ld+json"
          dangerouslySetInnerHTML={{ __html: JSON.stringify(WEBSITE_SCHEMA) }}
        />
        {/* Performance hints */}
        <link rel="dns-prefetch" href="//images.unsplash.com" />
        <link rel="preconnect" href="https://images.unsplash.com" crossOrigin="anonymous" />
      </head>
      <body suppressHydrationWarning className="min-h-full flex flex-col antialiased text-[#1a1a1a] bg-[#fafbfa]">
        {children}
      </body>
    </html>
  );
}
