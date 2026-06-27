import AnnouncementBar from "@/components/layout/AnnouncementBar";
import Navbar from "@/components/layout/Navbar";
import HeroSection from "@/components/hero/HeroSection";
import FuelCategories from "@/components/sections/FuelCategories";

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
      </main>
    </>
  );
}
