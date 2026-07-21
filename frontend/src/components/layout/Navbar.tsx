"use client";

import { useState, useEffect } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { Menu, X, Droplet, Store, ShoppingCart } from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { useCartStore } from "@/store/useCartStore";

interface NavLink {
  label: string;
  href: string;
  isMarketplace?: boolean;
}

const NAV_LINKS: NavLink[] = [
  { label: "Home",          href: "/"              },
  { label: "About Us",      href: "/#about"        },
  { label: "Products",      href: "/#products"     },
  { label: "Industries",    href: "/#industries"   },
  { label: "Marketplace",   href: "/marketplace", isMarketplace: true },
  { label: "How It Works",  href: "/#how-it-works" },
  { label: "Partner",       href: "/#partner"      },
  { label: "FAQs",          href: "/#faqs"         },
];

export default function Navbar() {
  const [scrolled, setScrolled]     = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const pathname                     = usePathname();

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 12);
    window.addEventListener("scroll", handleScroll, { passive: true });
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  // Close mobile menu on route change
  useEffect(() => { setMobileOpen(false); }, [pathname]);

  const isMarketplaceActive = pathname.startsWith("/marketplace");

  const { cart, fetchCart } = useCartStore();
  const itemCount = cart?.item_count || 0;

  useEffect(() => {
    fetchCart();
  }, [fetchCart]);

  return (
    <header
      className={cn(
        "sticky top-0 z-50 w-full transition-all duration-300",
        scrolled
          ? "bg-white/90 backdrop-blur-md shadow-sm shadow-[#155c32]/8 border-b border-[#e7ece8]/80"
          : "bg-white border-b border-[#e7ece8]"
      )}
    >
      <div className="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8 xl:px-12 h-[80px] flex items-center justify-between gap-6">

        {/* ── Logo ── */}
        <Link
          href="/"
          className="flex items-center gap-2.5 flex-shrink-0 group"
          aria-label="FuelCab home"
        >
          <div className="w-9 h-9 rounded-xl bg-[#155c32] flex items-center justify-center transition-transform duration-200 group-hover:scale-105">
            <Droplet className="w-5 h-5 text-[#33b248] fill-[#33b248]" />
          </div>
          <span className="text-xl font-extrabold tracking-tight text-[#1a1a1a]">
            Fuel<span className="text-[#155c32]">Cab</span>
          </span>
        </Link>

        {/* ── Desktop Navigation ── */}
        <nav
          className="hidden lg:flex items-center gap-1"
          aria-label="Main navigation"
        >
          {NAV_LINKS.map(({ label, href, isMarketplace }) => {
            const isActive = isMarketplace
              ? isMarketplaceActive
              : pathname === href;

            if (isMarketplace) {
              return (
                <Link
                  key={label}
                  href={href}
                  aria-current={isMarketplaceActive ? "page" : undefined}
                  className={cn(
                    "inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-bold transition-all duration-150 ml-1",
                    isMarketplaceActive
                      ? "bg-[#155c32] text-white shadow-md shadow-[#155c32]/20"
                      : "bg-[#155c32]/8 text-[#155c32] hover:bg-[#155c32]/15 border border-[#155c32]/15"
                  )}
                >
                  <Store className="w-3.5 h-3.5" aria-hidden="true" />
                  {label}
                </Link>
              );
            }

            return (
              <Link
                key={label}
                href={href}
                aria-current={isActive ? "page" : undefined}
                className={cn(
                  "px-3 py-1.5 text-sm font-medium transition-colors duration-150 relative group rounded-lg",
                  isActive
                    ? "text-[#155c32] font-semibold"
                    : "text-[#555555] hover:text-[#155c32]"
                )}
              >
                {label}
                <span
                  className={cn(
                    "absolute bottom-0 left-3 right-3 h-[2px] rounded-full bg-[#155c32] transition-all duration-200",
                    isActive ? "opacity-100" : "opacity-0 group-hover:opacity-100"
                  )}
                />
              </Link>
            );
          })}
        </nav>

        {/* ── Desktop Auth & Cart Buttons ── */}
        <div className="hidden md:flex items-center gap-3 flex-shrink-0">
          <Link
            href="/cart"
            className="relative p-2.5 rounded-xl text-gray-700 hover:text-[#155c32] hover:bg-[#f4f8f5] transition-colors"
            aria-label="View Shopping Cart"
          >
            <ShoppingCart className="w-5 h-5" />
            {itemCount > 0 && (
              <span className="absolute -top-1 -right-1 bg-[#155c32] text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-white shadow-xs animate-in zoom-in">
                {itemCount}
              </span>
            )}
          </Link>
          <Link
            href="/vendor/register"
            className={cn(
              buttonVariants({ variant: "outline", size: "sm" }),
              "h-10 px-4 rounded-xl border-[#33b248]/50 text-[#33b248] font-bold text-sm hover:border-[#155c32] hover:text-[#155c32] hover:bg-[#33b248]/5 transition-all duration-200"
            )}
          >
            Become a Vendor
          </Link>
          <Link
            href="/login"
            className={cn(
              buttonVariants({ variant: "outline", size: "sm" }),
              "h-10 px-5 rounded-xl border-[#e7ece8] text-[#1a1a1a] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition-all duration-200"
            )}
          >
            Login
          </Link>
          <Link
            href="/register"
            className={cn(
              buttonVariants({ variant: "default", size: "sm" }),
              "h-10 px-5 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] hover:shadow-lg hover:shadow-[#155c32]/20 transition-all duration-200 hover:-translate-y-px"
            )}
          >
            Register
          </Link>
        </div>

        {/* ── Mobile hamburger ── */}
        <button
          className="lg:hidden p-2 rounded-xl text-[#555555] hover:text-[#155c32] hover:bg-[#f4f8f5] transition-colors duration-150"
          onClick={() => setMobileOpen((v) => !v)}
          aria-label={mobileOpen ? "Close menu" : "Open menu"}
          aria-expanded={mobileOpen}
          aria-controls="mobile-nav"
        >
          {mobileOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
        </button>
      </div>

      {/* ── Mobile drawer ── */}
      {mobileOpen && (
        <div
          id="mobile-nav"
          className="lg:hidden absolute top-[80px] left-0 right-0 bg-white border-b border-[#e7ece8] shadow-xl z-40"
          role="dialog"
          aria-modal="true"
          aria-label="Mobile navigation menu"
        >
          <nav className="flex flex-col px-4 py-4 gap-0.5" aria-label="Mobile navigation">
            {NAV_LINKS.map(({ label, href, isMarketplace }) => {
              const isActive = isMarketplace ? isMarketplaceActive : pathname === href;

              if (isMarketplace) {
                return (
                  <Link
                    key={label}
                    href={href}
                    onClick={() => setMobileOpen(false)}
                    aria-current={isMarketplaceActive ? "page" : undefined}
                    className={cn(
                      "flex items-center gap-2 text-sm font-bold px-4 py-3 rounded-xl transition-colors duration-150",
                      isMarketplaceActive
                        ? "bg-[#155c32] text-white"
                        : "bg-[#155c32]/8 text-[#155c32] hover:bg-[#155c32]/15"
                    )}
                  >
                    <Store className="w-4 h-4" aria-hidden="true" />
                    {label}
                  </Link>
                );
              }

              return (
                <Link
                  key={label}
                  href={href}
                  onClick={() => setMobileOpen(false)}
                  aria-current={isActive ? "page" : undefined}
                  className={cn(
                    "text-sm font-medium px-4 py-3 rounded-xl transition-colors duration-150",
                    isActive
                      ? "text-[#155c32] font-semibold bg-[#f4f8f5]"
                      : "text-[#1a1a1a] hover:text-[#155c32] hover:bg-[#f4f8f5]"
                  )}
                >
                  {label}
                </Link>
              );
            })}
          </nav>

          <div className="flex flex-col gap-2 px-4 py-4 border-t border-[#e7ece8]">
            <Link
              href="/vendor/register"
              onClick={() => setMobileOpen(false)}
              className={cn(
                buttonVariants({ variant: "outline" }),
                "w-full h-11 rounded-xl border-[#33b248] text-[#33b248] font-bold flex items-center justify-center"
              )}
            >
              Become a Vendor
            </Link>
            <Link
              href="/login"
              onClick={() => setMobileOpen(false)}
              className={cn(
                buttonVariants({ variant: "outline" }),
                "w-full h-11 rounded-xl border-[#e7ece8] text-[#1a1a1a] font-semibold flex items-center justify-center"
              )}
            >
              Login
            </Link>
            <Link
              href="/register"
              onClick={() => setMobileOpen(false)}
              className={cn(
                buttonVariants({ variant: "default" }),
                "w-full h-11 rounded-xl bg-[#155c32] text-white font-semibold hover:bg-[#0d3a1f] flex items-center justify-center"
              )}
            >
              Register
            </Link>
          </div>
        </div>
      )}
    </header>
  );
}
