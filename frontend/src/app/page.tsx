import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Navbar from "@/components/layout/Navbar";
import HeroSection from "@/components/hero/HeroSection";
import FuelCategories from "@/components/sections/FuelCategories";
import HowItWorks from "@/components/sections/HowItWorks";

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
      </main>
    </>
  );
}
