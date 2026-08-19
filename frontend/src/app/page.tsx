import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Navbar from "@/components/layout/Navbar";
import HeroSection from "@/components/hero/HeroSection";
import FuelCategories from "@/components/sections/FuelCategories";
import MarketplacePromo from "@/components/sections/MarketplacePromo";
import dynamic from "next/dynamic";

// Below-the-fold components are loaded dynamically to decrease initial bundle size
const HowItWorks = dynamic(() => import("@/components/sections/HowItWorks"), { ssr: true });
const IndustriesWeServe = dynamic(() => import("@/components/sections/IndustriesWeServe"), { ssr: true });
const Testimonials = dynamic(() => import("@/components/sections/Testimonials"), { ssr: true });
const FAQ = dynamic(() => import("@/components/sections/FAQ"), { ssr: true });
const CTASection = dynamic(() => import("@/components/sections/CTASection"), { ssr: true });
const Footer = dynamic(() => import("@/components/layout/Footer"), { ssr: true });

export default function HomePage() {
  return (
    <>
      {/* Top Announcement Bar */}
      <AnnouncementBar />

      {/* Sticky Main Navigation */}
      <Navbar />

      {/* Main Layout Sections */}
      <main id="main-content" className="flex-1">
        {/* Hero Section */}
        <HeroSection />

        {/* Fuel Categories Section */}
        <FuelCategories />

        {/* Marketplace Section */}
        <MarketplacePromo />

        {/* How It Works Section */}
        <HowItWorks />

        {/* Industries We Serve Section */}
        <IndustriesWeServe />

        {/* Testimonials & Statistics Section */}
        <Testimonials />

        {/* FAQ Accordion Section */}
        <FAQ />

        {/* Pre-Footer CTA Section */}
        <CTASection />
      </main>

      {/* Footer Section */}
      <Footer />
    </>
  );
}
