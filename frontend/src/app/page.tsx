import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Navbar from "@/components/layout/Navbar";
import HeroSection from "@/components/hero/HeroSection";
import FuelCategories from "@/components/sections/FuelCategories";
import MarketplacePromo from "@/components/sections/MarketplacePromo";
import HowItWorks from "@/components/sections/HowItWorks";
import IndustriesWeServe from "@/components/sections/IndustriesWeServe";
import Testimonials from "@/components/sections/Testimonials";
import FAQ from "@/components/sections/FAQ";
import Footer from "@/components/layout/Footer";

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
      </main>

      {/* Footer Section */}
      <Footer />
    </>
  );
}
