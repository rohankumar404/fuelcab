import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Navbar from "@/components/layout/Navbar";
import HeroSection from "@/components/hero/HeroSection";
import FuelCategories from "@/components/sections/FuelCategories";
import HowItWorks from "@/components/sections/HowItWorks";
import IndustriesWeServe from "@/components/sections/IndustriesWeServe";
import Testimonials from "@/components/sections/Testimonials";

export default function HomePage() {
  return (
    <>
      {/* Top Announcement Bar */}
      <AnnouncementBar />

      {/* Sticky Main Navigation */}
      <Navbar />

      {/* Main Layout Sections */}
      <main id="main-content">
        {/* Hero Section */}
        <HeroSection />

        {/* Fuel Categories Section */}
        <FuelCategories />

        {/* How It Works Section */}
        <HowItWorks />

        {/* Industries We Serve Section */}
        <IndustriesWeServe />

        {/* Testimonials & Statistics Section */}
        <Testimonials />
      </main>
    </>
  );
}
