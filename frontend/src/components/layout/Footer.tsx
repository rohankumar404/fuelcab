"use client";

import React, { useState } from "react";
import Link from "next/link";
import {
  Droplet,
  Mail,
  Phone,
  MapPin,
  Clock,
  ChevronDown,
  Send,
} from "lucide-react";
import { cn } from "@/lib/utils";

interface FooterLink {
  label: string;
  href: string;
}

interface FooterSection {
  title: string;
  links: FooterLink[];
}

const SECTIONS: FooterSection[] = [
  {
    title: "Company",
    links: [
      { label: "About Us", href: "#about" },
      { label: "Careers", href: "#careers" },
      { label: "Partner With Us", href: "#partner" },
      { label: "News & Blogs", href: "/blogs" },
    ],
  },
  {
    title: "Products",
    links: [
      { label: "Diesel (HSD)", href: "#products" },
      { label: "Petrol (MS)", href: "#products" },
      { label: "CNG & LPG", href: "#products" },
      { label: "Lubricants", href: "#products" },
    ],
  },
  {
    title: "Support",
    links: [
      { label: "Help Center", href: "#support" },
      { label: "FAQs", href: "#faqs" },
      { label: "Vendor Portal", href: "/vendor/login" },
      { label: "Customer Portal", href: "/login" },
    ],
  },
];

export default function Footer() {
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({});
  const [email, setEmail] = useState("");
  const [subscribed, setSubscribed] = useState(false);

  const toggleSection = (title: string) => {
    setOpenSections((prev) => ({
      ...prev,
      [title]: !prev[title],
    }));
  };

  const handleSubscribe = (e: React.FormEvent) => {
    e.preventDefault();
    if (email) {
      setSubscribed(true);
      setEmail("");
    }
  };

  return (
    <footer
      className="bg-[#0d3a1f] text-gray-300 relative border-t-2 border-transparent"
      aria-label="FuelCab Site Footer"
    >
      {/* Subtle animated neon green top border line */}
      <div className="absolute top-0 left-0 right-0 h-[2px] bg-gradient-to-r from-transparent via-[#33b248] to-transparent animate-pulse" />

      {/* Main footer blocks */}
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-12">
          
          {/* Column 1: Brand & Contact Info (3/12 cols) */}
          <div className="lg:col-span-3 flex flex-col items-start text-left">
            <Link href="/" className="flex items-center gap-2.5 mb-6 group">
              <div className="w-8 h-8 rounded-lg bg-[#33b248] flex items-center justify-center">
                <Droplet className="w-4.5 h-4.5 text-white fill-white" />
              </div>
              <span className="text-xl font-extrabold text-white tracking-tight">
                Fuel<span className="text-[#33b248]">Cab</span>
              </span>
            </Link>
            
            <p className="text-xs text-gray-400 leading-relaxed mb-6 max-w-[280px]">
              Premium multi-vendor fuel delivery marketplace logistics platform. On-demand supply for heavy infrastructure and businesses.
            </p>

            {/* Contact Information */}
            <ul className="space-y-3.5 text-xs text-gray-300 mb-6">
              <li className="flex items-start gap-2.5">
                <MapPin className="w-4 h-4 text-[#33b248] flex-shrink-0 mt-0.5" />
                <span>Sector-62, Industrial Area, Noida, UP, India</span>
              </li>
              <li className="flex items-center gap-2.5">
                <Phone className="w-4 h-4 text-[#33b248] flex-shrink-0" />
                <a href="tel:+911800100200" className="hover:text-white transition">1800-100-200</a>
              </li>
              <li className="flex items-center gap-2.5">
                <Mail className="w-4 h-4 text-[#33b248] flex-shrink-0" />
                <a href="mailto:support@fuelcab.com" className="hover:text-white transition">support@fuelcab.com</a>
              </li>
              <li className="flex items-center gap-2.5">
                <Clock className="w-4 h-4 text-[#33b248] flex-shrink-0" />
                <span>Mon - Sat: 9:00 AM - 9:00 PM</span>
              </li>
            </ul>

            {/* Social Icons (Custom SVG handles) */}
            <div className="flex items-center gap-3">
              <a
                href="https://facebook.com/fuelcab"
                target="_blank"
                rel="noopener noreferrer"
                className="w-8 h-8 rounded-lg bg-white/5 hover:bg-[#33b248] hover:text-white flex items-center justify-center transition-colors duration-250"
                aria-label="Facebook"
              >
                <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c4.56-.93 8-4.96 8-9.75z" />
                </svg>
              </a>
              <a
                href="https://twitter.com/fuelcab"
                target="_blank"
                rel="noopener noreferrer"
                className="w-8 h-8 rounded-lg bg-white/5 hover:bg-[#33b248] hover:text-white flex items-center justify-center transition-colors duration-250"
                aria-label="Twitter"
              >
                <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                </svg>
              </a>
              <a
                href="https://linkedin.com/company/fuelcab"
                target="_blank"
                rel="noopener noreferrer"
                className="w-8 h-8 rounded-lg bg-white/5 hover:bg-[#33b248] hover:text-white flex items-center justify-center transition-colors duration-250"
                aria-label="LinkedIn"
              >
                <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                </svg>
              </a>
              <a
                href="https://instagram.com/fuelcab"
                target="_blank"
                rel="noopener noreferrer"
                className="w-8 h-8 rounded-lg bg-white/5 hover:bg-[#33b248] hover:text-white flex items-center justify-center transition-colors duration-250"
                aria-label="Instagram"
              >
                <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.051.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z" />
                </svg>
              </a>
            </div>
          </div>

          {/* Columns 2-4: Responsive Links Sections (6/12 cols) */}
          <div className="lg:col-span-6 grid grid-cols-1 md:grid-cols-3 gap-8">
            {SECTIONS.map((section) => (
              <div key={section.title} className="text-left">
                {/* Desktop static header */}
                <h4 className="hidden md:block text-white font-bold text-xs uppercase tracking-wider mb-6">
                  {section.title}
                </h4>
                
                {/* Mobile accordion trigger */}
                <button
                  type="button"
                  onClick={() => toggleSection(section.title)}
                  className="md:hidden w-full flex items-center justify-between py-3 border-b border-white/5 text-white font-bold text-xs uppercase tracking-wider"
                >
                  <span>{section.title}</span>
                  <ChevronDown
                    className={cn(
                      "w-4 h-4 transition-transform duration-200",
                      openSections[section.title] && "transform rotate-180 text-[#33b248]"
                    )}
                  />
                </button>

                {/* Links list */}
                <ul
                  className={cn(
                    "flex-col space-y-3 mt-4 md:mt-0 text-xs md:flex",
                    openSections[section.title] ? "flex" : "hidden"
                  )}
                >
                  {section.links.map((link) => (
                    <li key={link.label}>
                      <Link
                        href={link.href}
                        className="hover:text-white transition-colors duration-150 relative group py-0.5 inline-block"
                      >
                        {link.label}
                        <span className="absolute -bottom-0.5 left-0 w-0 h-[1.5px] bg-[#33b248] transition-all duration-200 group-hover:w-full" />
                      </Link>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>

          {/* Column 5: Newsletter & App Downloads (3/12 cols) */}
          <div className="lg:col-span-3 text-left">
            <h4 className="text-white font-bold text-xs uppercase tracking-wider mb-6">
              Stay Updated
            </h4>
            <p className="text-xs text-gray-400 leading-relaxed mb-4">
              Subscribe to our monthly newsletters for pricing updates and industry reports.
            </p>

            {/* Subscription Form */}
            <form onSubmit={handleSubscribe} className="relative w-full max-w-[280px] mb-6">
              <input
                type="email"
                required
                placeholder="Enter business email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                className="w-full h-10 pl-3 pr-10 rounded-xl bg-white/5 border border-white/10 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-[#33b248] transition"
              />
              <button
                type="submit"
                className="absolute right-1 top-1 w-8 h-8 rounded-lg bg-[#33b248] text-white flex items-center justify-center hover:bg-white hover:text-[#0d3a1f] transition cursor-pointer"
                aria-label="Subscribe"
              >
                <Send className="w-3.5 h-3.5" />
              </button>
            </form>

            {subscribed && (
              <p className="text-[10px] text-[#33b248] font-bold mb-4">
                Thank you for subscribing!
              </p>
            )}

            {/* Mobile App Download */}
            <h4 className="text-white font-bold text-[10px] uppercase tracking-wider mb-3">
              Download App
            </h4>
            <div className="flex gap-2.5">
              <a href="#" className="hover:opacity-85 transition">
                <img
                  src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                  alt="Download on Google Play Store"
                  className="h-8"
                />
              </a>
              <a href="#" className="hover:opacity-85 transition">
                <img
                  src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                  alt="Download on Apple App Store"
                  className="h-8"
                />
              </a>
            </div>
          </div>

        </div>

        {/* ─── Bottom copyright bar ─── */}
        <div className="border-t border-white/5 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] text-gray-500">
          <span>&copy; {new Date().getFullYear()} FuelCab. All rights reserved.</span>
          
          <div className="flex gap-5 font-semibold">
            <Link href="#privacy" className="hover:text-white transition">Privacy Policy</Link>
            <Link href="#terms" className="hover:text-white transition">Terms & Conditions</Link>
            <Link href="#cookies" className="hover:text-white transition">Cookie Preferences</Link>
          </div>
        </div>

      </div>
    </footer>
  );
}
