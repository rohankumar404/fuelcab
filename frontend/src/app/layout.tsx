import type { Metadata } from "next";
import { Inter } from "next/font/google";
import "./globals.css";

const inter = Inter({
  variable: "--font-inter",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700", "800"],
  display: "swap",
});

export const metadata: Metadata = {
  title: "FuelCab — B2B Fuel Delivery Platform",
  description:
    "FuelCab is India's trusted on-demand diesel & commercial fuel delivery platform. Order Diesel, HSD, AdBlue, and lubricants delivered directly to your business site.",
  keywords: "fuel delivery, B2B diesel, bulk fuel, HSD delivery, AdBlue, lubricants",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en" className={`${inter.variable} h-full`}>
      <body className="min-h-full flex flex-col">{children}</body>
    </html>
  );
}
