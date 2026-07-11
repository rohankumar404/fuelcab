"use client";

import Image from "next/image";
import Link from "next/link";
import { ArrowRight } from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import HeroFuelCard from "./HeroFuelCard";
import HeroFeatureTags from "./HeroFeatureTags";

export default function HeroSection() {
  return (
    <section
      className="relative w-full overflow-hidden bg-[#fafbfa]"
      aria-label="Hero Section"
    >
      {/* ─── Background decorations ─── */}
      <div
        className="pointer-events-none absolute inset-0 z-0"
        aria-hidden="true"
      >
        {/* Soft radial gradient blobs */}
        <div className="absolute top-[-10%] left-[-5%] w-[600px] h-[600px] rounded-full bg-[#33b248]/8 blur-3xl" />
        <div className="absolute bottom-[-20%] right-[-5%] w-[500px] h-[500px] rounded-full bg-[#155c32]/6 blur-3xl" />

        {/* Abstract circular lines (right side) */}
        <svg
          className="absolute right-0 top-1/2 -translate-y-1/2 w-[55%] h-full opacity-30"
          fill="none"
          viewBox="0 0 500 500"
          xmlns="http://www.w3.org/2000/svg"
        >
          <circle
            cx="250"
            cy="250"
            r="220"
            stroke="#e7ece8"
            strokeWidth="1"
            strokeDasharray="6 6"
          />
          <circle cx="250" cy="250" r="170" stroke="#e7ece8" strokeWidth="1" />
          <circle
            cx="250"
            cy="250"
            r="120"
            stroke="#e7ece8"
            strokeWidth="1"
            strokeDasharray="3 3"
          />
          <circle cx="250" cy="250" r="70" stroke="#e7ece8" strokeWidth="1" />
        </svg>
      </div>

      {/* ─── Container ─── */}
      <div className="relative z-10 mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 xl:px-12">
        <div className="grid grid-cols-1 lg:grid-cols-12 items-center gap-8 lg:gap-0 min-h-[750px] lg:min-h-[820px] py-16 lg:py-0">

          {/* ══════════════════════════════════════
              LEFT COLUMN — 5 / 12 columns
          ══════════════════════════════════════ */}
          <div className="lg:col-span-5 flex flex-col items-start">

            {/* B2B pill label */}
            <div className="animate-fade-up flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#155c32]/10 border border-[#155c32]/20 mb-6">
              <span className="w-1.5 h-1.5 rounded-full bg-[#155c32] animate-pulse-dot" />
              <span className="text-[11px] font-bold uppercase tracking-widest text-[#155c32]">
                B2B Fuel Delivery
              </span>
            </div>

            {/* Main Heading */}
            <h1 className="animate-fade-up-delay-1 text-4xl sm:text-5xl lg:text-[3.4rem] xl:text-6xl font-extrabold tracking-tight leading-[1.08] text-[#1a1a1a] mb-6">
              India&apos;s Trusted{" "}
              <span className="text-[#155c32] relative">
                Diesel Delivery
                {/* Underline accent */}
                <span
                  className="absolute -bottom-1 left-0 w-full h-[3px] rounded-full bg-[#33b248]/50"
                  aria-hidden="true"
                />
              </span>{" "}
              Platform
            </h1>

            {/* Sub-paragraph */}
            <p className="animate-fade-up-delay-2 text-[#555555] text-base sm:text-lg leading-relaxed max-w-[480px] mb-9">
              FuelCab is a multi-vendor fuel delivery platform for businesses.
              Order Diesel and other fuels — minimum 100 liters — delivered to
              your site fast, safely, and reliably.
            </p>

            {/* CTA Buttons */}
            <div className="animate-fade-up-delay-3 flex flex-col sm:flex-row gap-3 w-full sm:w-auto mb-10">
              {/* Primary */}
              <Link
                href="/order"
                className={cn(
                  buttonVariants({ variant: "default", size: "lg" }),
                  "h-12 px-7 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] hover:shadow-xl hover:shadow-[#155c32]/25 transition-all duration-300 hover:-translate-y-0.5"
                )}
              >
                Order Diesel Now
                <ArrowRight className="ml-2 w-4 h-4 group-hover/button:translate-x-1 transition-transform duration-200" />
              </Link>

              {/* Secondary outline */}
              <Link
                href="/vendor/register"
                className={cn(
                  buttonVariants({ variant: "outline", size: "lg" }),
                  "h-12 px-7 rounded-xl border-[#155c32] text-[#155c32] font-semibold text-sm hover:bg-[#155c32]/5 hover:border-[#155c32] hover:-translate-y-0.5 transition-all duration-300"
                )}
              >
                Become a Vendor
              </Link>
            </div>

            {/* Feature tags */}
            <div className="animate-fade-up-delay-3">
              <HeroFeatureTags />
            </div>
          </div>

          {/* ══════════════════════════════════════
              RIGHT COLUMN — 7 / 12 columns
          ══════════════════════════════════════ */}
          <div className="lg:col-span-7 relative flex items-center justify-center lg:justify-end lg:pl-12">

            {/* Truck image wrapper with float animation */}
            <div className="animate-slide-right relative w-full max-w-[580px] lg:max-w-none animate-float-truck">
              {/* Glow behind truck */}
              <div
                className="absolute inset-0 rounded-[28px] bg-gradient-to-br from-[#33b248]/20 to-[#155c32]/10 blur-2xl scale-95"
                aria-hidden="true"
              />

              <Image
                src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&q=85&w=900"
                alt="FuelCab white fuel tanker truck delivering diesel to a business site"
                width={900}
                height={560}
                priority
                className="relative z-10 rounded-[24px] shadow-2xl shadow-[#1a1a1a]/12 object-cover w-full aspect-[16/10]"
                sizes="(max-width: 768px) 100vw, (max-width: 1024px) 60vw, 55vw"
              />

              {/* FuelCab brand badge — bottom left of image */}
              <div className="absolute z-20 bottom-4 left-4 sm:bottom-5 sm:left-5 flex items-center gap-2 bg-white/95 backdrop-blur-sm px-3 py-2 rounded-xl shadow-lg border border-[#e7ece8]">
                {/* Mini logo */}
                <div className="w-7 h-7 rounded-lg bg-[#155c32] flex items-center justify-center flex-shrink-0">
                  <svg
                    className="w-3.5 h-3.5 text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path d="M12 2C9.24 2 7 4.24 7 7c0 2.85 2.92 7.21 5 9.88C14.08 14.21 17 9.85 17 7c0-2.76-2.24-5-5-5zm0 6.5A1.5 1.5 0 0 1 10.5 7 1.5 1.5 0 0 1 12 5.5 1.5 1.5 0 0 1 13.5 7 1.5 1.5 0 0 1 12 8.5z" />
                  </svg>
                </div>
                <span className="text-xs font-bold text-[#1a1a1a] tracking-tight">
                  FuelCab Delivery
                </span>
              </div>
            </div>

            {/* ── Floating Diesel Priority Card ── */}
            {/* Positioned top-right relative to the truck container */}
            <div className="absolute top-4 -right-2 sm:top-6 sm:right-0 lg:top-12 lg:-right-4 z-30 hidden sm:block">
              <HeroFuelCard />
            </div>
          </div>

        </div>
      </div>
    </section>
  );
}
