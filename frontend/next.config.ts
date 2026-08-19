import type { NextConfig } from "next";

const backendHost = process.env.NEXT_PUBLIC_BACKEND_URL || "http://localhost:8000";

const nextConfig: NextConfig = {
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "images.unsplash.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "fuelcab.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "www.fuelcab.com",
        pathname: "/**",
      },
      {
        protocol: "https",
        hostname: "upload.wikimedia.org",
        pathname: "/**",
      },
    ],
  },
  async redirects() {
    return [
      // ── www → non-www canonical redirect ──────────────────────────────
      {
        source: "/:path*",
        has: [{ type: "host", value: "www.fuelcab.com" }],
        destination: "https://fuelcab.com/:path*",
        permanent: true,
      },
      // ── Admin / Vendor / Operations panel (served by Laravel) ─────────
      {
        source: "/admin",
        destination: `${backendHost}/admin`,
        permanent: false,
      },
      {
        source: "/admin/:path*",
        destination: `${backendHost}/admin/:path*`,
        permanent: false,
      },
      {
        source: "/operations",
        destination: `${backendHost}/operations`,
        permanent: false,
      },
      {
        source: "/operations/:path*",
        destination: `${backendHost}/operations/:path*`,
        permanent: false,
      },
      {
        source: "/vendor",
        destination: `${backendHost}/vendor`,
        permanent: false,
      },
      {
        source: "/vendor/panel",
        destination: `${backendHost}/vendor`,
        permanent: false,
      },
      {
        source: "/vendor/panel/:path*",
        destination: `${backendHost}/vendor/:path*`,
        permanent: false,
      },
      {
        source: "/storefront",
        destination: "/",
        permanent: false,
      },
    ];
  },
};

export default nextConfig;
