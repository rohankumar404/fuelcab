import type { Metadata } from "next";
import type { ReactNode } from "react";
import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

export const metadata: Metadata = {
  title: "FuelCab Marketplace — Industrial Energy Sourcing Platform",
  description:
    "Source biomass briquettes, bio-diesel, CNG, furnace oil, and other industrial energy products from verified bulk suppliers across India on FuelCab Marketplace.",
  keywords:
    "industrial fuel marketplace, biomass briquettes, bio-diesel, CNG supplier, bulk fuel sourcing India, B2B energy marketplace",
  openGraph: {
    title: "FuelCab Marketplace — One Marketplace. Multiple Energy Solutions.",
    description:
      "Discover industrial fuels, alternative fuels, biomass, liquid fuels and gas solutions from verified suppliers across India.",
    url: "https://fuelcab.in/marketplace",
    siteName: "FuelCab",
    type: "website",
  },
};

export default function MarketplaceLayout({ children }: { children: ReactNode }) {
  return (
    <>
      <AnnouncementBar />
      <Navbar />
      <main className="flex-1 bg-[#fafbfa]">{children}</main>
      <Footer />
    </>
  );
}

