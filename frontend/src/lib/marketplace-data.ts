// ── FuelCab Marketplace Data Layer & Types ─────────────────────────────────────

export type CommerceMode = "DIRECT_ORDER" | "REQUEST_QUOTE";

export interface Vendor {
  id: string;
  brand_name: string;
  city: string;
  state: string;
  is_verified: boolean;
  rating?: number;
  phone?: string;
  email?: string;
  established_year?: number;
}

export interface ProductMaster {
  id: string;
  name: string;
  slug: string;
  category_name: string;
  category_slug: string;
  code: string;
  standard_unit: string;
  gcv_range: string;
  description: string;
  specifications_schema: Record<string, string>;
  product_image: string;
  active_listings_count: number;
}

export interface VendorListing {
  id: string;
  listing_title: string;
  slug: string;
  sku: string;
  category_name: string;
  category_slug: string;
  marketplace_product_id: string;
  marketplace_product_slug: string;
  marketplace_product_name: string;
  commerce_mode: CommerceMode;
  short_description: string;
  full_description: string;
  product_images: string[];
  base_price: number;
  unit: string;
  available_quantity: number;
  min_order_quantity: number;
  max_order_quantity?: number;
  tax_rate: number;
  tax_inclusive: boolean;
  dispatch_location: string;
  serviceable_locations: string[];
  estimated_dispatch_hours: number;
  quality_specifications: Record<string, string>;
  certificate_documents: Array<{ name: string; size: string; url: string }>;
  vendor: Vendor;
  is_featured: boolean;
  approval_status: "APPROVED";
  created_at: string;
}

// ── Master Products Catalog Data ──────────────────────────────────────────────
export const PRODUCT_MASTERS: ProductMaster[] = [
  {
    id: "mp-1",
    name: "Refuse Derived Fuel (RDF)",
    slug: "rdf",
    category_name: "Solid Fuels",
    category_slug: "solid-fuels",
    code: "RDF-IND-3500",
    standard_unit: "metric_tonnes",
    gcv_range: "3500 – 4200 kcal/kg",
    description: "High-calorific processed non-hazardous industrial waste fuel ideal for cement kilns, captive power plants, and thermal boilers.",
    product_image: "https://images.unsplash.com/photo-1579847255504-450f14067c74?auto=format&fit=crop&q=80&w=800",
    specifications_schema: {
      "Calorific Value (GCV)": "3500 – 4200 kcal/kg",
      "Moisture Content": "Max 12%",
      "Ash Content": "Max 15%",
      "Flake Size": "30 – 50 mm",
      "Chlorine (Cl)": "Max 0.5%",
      "Sulphur (S)": "Max 0.3%",
    },
    active_listings_count: 14,
  },
  {
    id: "mp-2",
    name: "Biomass Briquettes / Bio Coal",
    slug: "biomass-briquettes",
    category_name: "Solid Fuels",
    category_slug: "solid-fuels",
    code: "BIO-BRIQ-90",
    standard_unit: "metric_tonnes",
    gcv_range: "3800 – 4300 kcal/kg",
    description: "Eco-friendly dense cylindrical biofuel made from agricultural residue and sawdust with sub-8% moisture.",
    product_image: "https://images.unsplash.com/photo-1540324155974-7265d7cb6d1b?auto=format&fit=crop&q=80&w=800",
    specifications_schema: {
      "Calorific Value (GCV)": "3800 – 4300 kcal/kg",
      "Moisture Content": "Max 8%",
      "Ash Content": "Max 7%",
      "Density": "1.2 – 1.3 g/cm³",
      "Diameter": "90 mm",
    },
    active_listings_count: 22,
  },
  {
    id: "mp-3",
    name: "Bio Diesel (B-100)",
    slug: "bio-diesel-b100",
    category_name: "Liquid Fuels",
    category_slug: "liquid-fuels",
    code: "BIO-DSL-B100",
    standard_unit: "litres",
    gcv_range: "9000 – 9500 kcal/kg",
    description: "100% pure fatty acid methyl ester (FAME) bio-diesel conforming to IS 15607 standards for commercial fleets & DGs.",
    product_image: "https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&q=80&w=800",
    specifications_schema: {
      "Flash Point": "Min 130 °C",
      "Density at 15°C": "875 – 900 kg/m³",
      "Kinematic Viscosity at 40°C": "3.5 – 5.0 cSt",
      "Water Content": "Max 500 mg/kg",
      "Cetane Number": "Min 51",
    },
    active_listings_count: 18,
  },
  {
    id: "mp-4",
    name: "Compressed Natural Gas (CNG)",
    slug: "cng",
    category_name: "Gas Fuels",
    category_slug: "gas-fuels",
    code: "CNG-IND-PURE",
    standard_unit: "kilograms",
    gcv_range: "11500 – 12000 kcal/kg",
    description: "High-pressure clean natural gas with >90% methane purity for industrial furnace and captive power usage.",
    product_image: "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80&w=800",
    specifications_schema: {
      "Methane (C1)": "Min 90%",
      "Ethane (C2)": "Max 5%",
      "Gross Calorific Value": "11500 – 12000 kcal/kg",
      "Delivery Pressure": "200 Bar",
    },
    active_listings_count: 9,
  },
  {
    id: "mp-5",
    name: "Bio-Furnace Oil",
    slug: "bio-furnace-oil",
    category_name: "Liquid Fuels",
    category_slug: "liquid-fuels",
    code: "BIO-FO-HV",
    standard_unit: "litres",
    gcv_range: "9600 – 9900 kcal/kg",
    description: "Low-sulfur eco-replacement for traditional heavy furnace oil in textile, chemical, and metal processing plants.",
    product_image: "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=800",
    specifications_schema: {
      "Calorific Value (GCV)": "9600 – 9900 kcal/kg",
      "Viscosity at 50°C": "110 – 130 cSt",
      "Flash Point": "Min 66 °C",
      "Ash Content": "Max 0.1%",
    },
    active_listings_count: 11,
  },
  {
    id: "mp-6",
    name: "Raw Rice Husk / Paddy Husk",
    slug: "rice-husk",
    category_name: "Solid Fuels",
    category_slug: "solid-fuels",
    code: "AGRO-RH-RAW",
    standard_unit: "metric_tonnes",
    gcv_range: "3000 – 3300 kcal/kg",
    description: "Bulk agricultural byproduct biomass fuel for fluidized bed combustion (FBC) boilers.",
    product_image: "https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&q=80&w=800",
    specifications_schema: {
      "Calorific Value (GCV)": "3000 – 3300 kcal/kg",
      "Moisture Content": "Max 10%",
      "Ash Content": "Max 18%",
      "Bulk Density": "100 kg/m³",
    },
    active_listings_count: 16,
  },
];

// ── Master Approved Vendor Listings Data ──────────────────────────────────────
export const VENDOR_LISTINGS: VendorListing[] = [
  {
    id: "l-1",
    listing_title: "Premium Industrial RDF — 3500+ GCV Bulk Supply",
    slug: "premium-industrial-rdf-3500-gcv",
    sku: "RDF-GUJ-3500",
    category_name: "Solid Fuels",
    category_slug: "solid-fuels",
    marketplace_product_id: "mp-1",
    marketplace_product_slug: "rdf",
    marketplace_product_name: "Refuse Derived Fuel (RDF)",
    commerce_mode: "REQUEST_QUOTE",
    short_description: "Refuse Derived Fuel (RDF) with guaranteed GCV above 3500 kcal/kg, screened for low ash & zero moisture spikes.",
    full_description: "Processed from segregated dry combustible municipal and industrial fraction. Shredded to 30-50mm flake size, tested for low chlorine (<0.4%) and high calorific consistency. Ideal for cement kilns, captive power plants, and heavy steam generation boilers across Western & Central India.",
    product_images: [
      "https://images.unsplash.com/photo-1579847255504-450f14067c74?auto=format&fit=crop&q=80&w=1200",
      "https://images.unsplash.com/photo-1540324155974-7265d7cb6d1b?auto=format&fit=crop&q=80&w=1200",
      "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=1200",
    ],
    base_price: 4200,
    unit: "metric_tonnes",
    available_quantity: 1200,
    min_order_quantity: 25,
    max_order_quantity: 5000,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Surat, Gujarat",
    serviceable_locations: ["Gujarat", "Maharashtra", "Rajasthan", "Madhya Pradesh"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      "Calorific Value (GCV)": "3500 – 3800 kcal/kg",
      "Moisture Content": "Max 12%",
      "Ash Content": "Max 14%",
      "Flake Size": "30 – 50 mm",
      "Chlorine (Cl)": "Max 0.4%",
      "Sulphur (S)": "Max 0.2%",
    },
    certificate_documents: [
      { name: "Lab_Test_Report_RDF_2026.pdf", size: "1.4 MB", url: "#" },
      { name: "ISO_9001_Quality_Cert.pdf", size: "890 KB", url: "#" },
    ],
    vendor: {
      id: "v-101",
      brand_name: "Gujarat Eco-Energy Solutions",
      city: "Surat",
      state: "Gujarat",
      is_verified: true,
      rating: 4.9,
      phone: "+91 98765 43210",
      email: "b2b@gujaratecoenergy.com",
      established_year: 2014,
    },
    is_featured: true,
    approval_status: "APPROVED",
    created_at: "2026-06-15T10:00:00Z",
  },
  {
    id: "l-2",
    listing_title: "High-Density Biomass Briquettes (90mm Sawdust & Husk)",
    slug: "high-density-biomass-briquettes-90mm",
    sku: "BRIQ-MH-90",
    category_name: "Solid Fuels",
    category_slug: "solid-fuels",
    marketplace_product_id: "mp-2",
    marketplace_product_slug: "biomass-briquettes",
    marketplace_product_name: "Biomass Briquettes / Bio Coal",
    commerce_mode: "DIRECT_ORDER",
    short_description: "90mm diameter bio-briquettes manufactured from saw dust and crop husk with low ash residue.",
    full_description: "Extruded biomass briquettes under high pressure without chemical binders. Delivers steady heat output and minimal smoke emissions. Perfect substitute for Indonesian coal in industrial boilers.",
    product_images: [
      "https://images.unsplash.com/photo-1540324155974-7265d7cb6d1b?auto=format&fit=crop&q=80&w=1200",
      "https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&q=80&w=1200",
    ],
    base_price: 6800,
    unit: "metric_tonnes",
    available_quantity: 650,
    min_order_quantity: 15,
    max_order_quantity: 2000,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Nagpur, Maharashtra",
    serviceable_locations: ["Maharashtra", "Chhattisgarh", "Madhya Pradesh", "Telangana"],
    estimated_dispatch_hours: 36,
    quality_specifications: {
      "Calorific Value (GCV)": "3900 – 4200 kcal/kg",
      "Moisture Content": "Max 7%",
      "Ash Content": "Max 6.5%",
      "Density": "1.25 g/cm³",
      "Diameter": "90 mm",
    },
    certificate_documents: [
      { name: "SGS_Calorific_Analysis_Briquette.pdf", size: "2.1 MB", url: "#" },
    ],
    vendor: {
      id: "v-102",
      brand_name: "Vidarbha Bio-Coal Energy",
      city: "Nagpur",
      state: "Maharashtra",
      is_verified: true,
      rating: 4.8,
      phone: "+91 98123 45678",
      email: "orders@vidarbhabiocoal.in",
      established_year: 2017,
    },
    is_featured: true,
    approval_status: "APPROVED",
    created_at: "2026-06-18T12:30:00Z",
  },
  {
    id: "l-3",
    listing_title: "Industrial Bio-Diesel (B-100) — IS 15607 Certified",
    slug: "industrial-bio-diesel-b100-is15607",
    sku: "BD100-MUM-01",
    category_name: "Liquid Fuels",
    category_slug: "liquid-fuels",
    marketplace_product_id: "mp-3",
    marketplace_product_slug: "bio-diesel-b100",
    marketplace_product_name: "Bio Diesel (B-100)",
    commerce_mode: "DIRECT_ORDER",
    short_description: "Pure B-100 Bio-Diesel derived from non-edible oilseeds for commercial DG sets & fleet engines.",
    full_description: "IS 15607 compliant ultra-clean liquid biofuel. Zero sulfur content, high cetane value (53+), superior lubrication property extending engine injector lifespan while reducing carbon footprint.",
    product_images: [
      "https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&q=80&w=1200",
      "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=1200",
    ],
    base_price: 84,
    unit: "litres",
    available_quantity: 35000,
    min_order_quantity: 1000,
    max_order_quantity: 100000,
    tax_rate: 18,
    tax_inclusive: false,
    dispatch_location: "Navi Mumbai, Maharashtra",
    serviceable_locations: ["Maharashtra", "Goa", "Gujarat", "Karnataka"],
    estimated_dispatch_hours: 24,
    quality_specifications: {
      "Flash Point": "Min 135 °C",
      "Density at 15°C": "880 kg/m³",
      "Kinematic Viscosity at 40°C": "4.2 cSt",
      "Water Content": "Max 300 mg/kg",
      "Cetane Number": "Min 53",
      "Sulfur Content": "Below 10 ppm",
    },
    certificate_documents: [
      { name: "IS15607_Certificate_Apex.pdf", size: "1.8 MB", url: "#" },
      { name: "MSDS_BioDiesel_B100.pdf", size: "1.1 MB", url: "#" },
    ],
    vendor: {
      id: "v-103",
      brand_name: "Apex Biofuels Logistics",
      city: "Navi Mumbai",
      state: "Maharashtra",
      is_verified: true,
      rating: 5.0,
      phone: "+91 97777 88888",
      email: "supply@apexbiofuels.com",
      established_year: 2012,
    },
    is_featured: true,
    approval_status: "APPROVED",
    created_at: "2026-06-20T09:15:00Z",
  },
  {
    id: "l-4",
    listing_title: "High-Pressure Compressed Natural Gas (CNG) — Industrial Tankers",
    slug: "high-pressure-cng-industrial-tankers",
    sku: "CNG-NCR-IND",
    category_name: "Gas Fuels",
    category_slug: "gas-fuels",
    marketplace_product_id: "mp-4",
    marketplace_product_slug: "cng",
    marketplace_product_name: "Compressed Natural Gas (CNG)",
    commerce_mode: "REQUEST_QUOTE",
    short_description: "Cascade CNG delivery for factories without pipeline connectivity in Delhi NCR & Haryana.",
    full_description: "Delivered in mobile cascade storage units. Methane content > 92%. Ideal for continuous annealing furnaces, ceramic kilns, and captive power generators seeking clean energy transition.",
    product_images: [
      "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80&w=1200",
    ],
    base_price: 78,
    unit: "kilograms",
    available_quantity: 15000,
    min_order_quantity: 500,
    max_order_quantity: 20000,
    tax_rate: 12,
    tax_inclusive: true,
    dispatch_location: "Gurugram, Haryana",
    serviceable_locations: ["Delhi NCR", "Haryana", "Uttar Pradesh", "Punjab"],
    estimated_dispatch_hours: 12,
    quality_specifications: {
      "Methane (C1)": "Min 92.5%",
      "Ethane (C2)": "Max 3.8%",
      "Gross Calorific Value": "11800 kcal/kg",
      "Delivery Pressure": "200 Bar",
      "Hydrogen Sulfide": "Nil",
    },
    certificate_documents: [
      { name: "PESO_Tanker_Safety_Approval.pdf", size: "3.2 MB", url: "#" },
    ],
    vendor: {
      id: "v-104",
      brand_name: "NCR Clean Gas Infra",
      city: "Gurugram",
      state: "Haryana",
      is_verified: true,
      rating: 4.7,
      phone: "+91 99999 11111",
      email: "corporate@ncrcleangas.in",
      established_year: 2018,
    },
    is_featured: false,
    approval_status: "APPROVED",
    created_at: "2026-06-22T14:20:00Z",
  },
  {
    id: "l-5",
    listing_title: "Low Sulfur Bio-Furnace Oil — Commercial Boiler Grade",
    slug: "low-sulfur-bio-furnace-oil-boiler-grade",
    sku: "BFO-VAD-9600",
    category_name: "Liquid Fuels",
    category_slug: "liquid-fuels",
    marketplace_product_id: "mp-5",
    marketplace_product_slug: "bio-furnace-oil",
    marketplace_product_name: "Bio-Furnace Oil",
    commerce_mode: "DIRECT_ORDER",
    short_description: "9600 GCV alternative furnace oil designed to lower soot and boiler maintenance.",
    full_description: "Viscosity-optimized liquid fuel blend. Compatible with standard FO burners without pre-heating modifications. Viscosity at 50°C is 110 cSt, ensuring smooth atomization and clean combustion.",
    product_images: [
      "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=1200",
    ],
    base_price: 54,
    unit: "litres",
    available_quantity: 50000,
    min_order_quantity: 2000,
    max_order_quantity: 150000,
    tax_rate: 18,
    tax_inclusive: false,
    dispatch_location: "Vadodara, Gujarat",
    serviceable_locations: ["Gujarat", "Maharashtra", "Madhya Pradesh"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      "Calorific Value (GCV)": "9600 kcal/kg",
      "Viscosity at 50°C": "110 cSt",
      "Flash Point": "Min 68 °C",
      "Water & Sediment": "Max 0.2%",
      "Ash Content": "Max 0.08%",
    },
    certificate_documents: [
      { name: "Vadodara_Lab_Analysis_FO.pdf", size: "1.5 MB", url: "#" },
    ],
    vendor: {
      id: "v-105",
      brand_name: "Western India Eco-Fuels",
      city: "Vadodara",
      state: "Gujarat",
      is_verified: true,
      rating: 4.9,
      phone: "+91 94444 33333",
      email: "sales@westernindiaecofuels.com",
      established_year: 2015,
    },
    is_featured: false,
    approval_status: "APPROVED",
    created_at: "2026-06-25T11:45:00Z",
  },
  {
    id: "l-6",
    listing_title: "Screened Agro Rice Husk — High Calorific Boiler Fuel",
    slug: "screened-agro-rice-husk-boiler-fuel",
    sku: "RH-PB-RAW",
    category_name: "Solid Fuels",
    category_slug: "solid-fuels",
    marketplace_product_id: "mp-6",
    marketplace_product_slug: "rice-husk",
    marketplace_product_name: "Raw Rice Husk / Paddy Husk",
    commerce_mode: "REQUEST_QUOTE",
    short_description: "Clean dry paddy husk with uniform moisture content under 10% for agricultural & textile boilers.",
    full_description: "Sourced directly from automated Punjab rice mills. Screened to eliminate mud and stones. Consistently high silicon oxide ash suitable for silica extraction post combustion.",
    product_images: [
      "https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&q=80&w=1200",
    ],
    base_price: 3600,
    unit: "metric_tonnes",
    available_quantity: 900,
    min_order_quantity: 20,
    max_order_quantity: 3000,
    tax_rate: 5,
    tax_inclusive: false,
    dispatch_location: "Ludhiana, Punjab",
    serviceable_locations: ["Punjab", "Haryana", "Himachal Pradesh", "Delhi NCR", "Uttarakhand"],
    estimated_dispatch_hours: 48,
    quality_specifications: {
      "Calorific Value (GCV)": "3200 kcal/kg",
      "Moisture Content": "Max 9.5%",
      "Ash Content": "Max 17.5%",
      "Bulk Density": "105 kg/m³",
    },
    certificate_documents: [
      { name: "Punjab_Agro_Quality_Cert.pdf", size: "1.2 MB", url: "#" },
    ],
    vendor: {
      id: "v-106",
      brand_name: "Punjab Agro Products Ltd",
      city: "Ludhiana",
      state: "Punjab",
      is_verified: true,
      rating: 4.6,
      phone: "+91 98888 22222",
      email: "agro@punjabagroproducts.com",
      established_year: 2011,
    },
    is_featured: false,
    approval_status: "APPROVED",
    created_at: "2026-06-28T16:10:00Z",
  },
];

// ── Data Query Helpers ────────────────────────────────────────────────────────

export function getProductMasterBySlug(categorySlug: string, productSlug: string): ProductMaster | undefined {
  return PRODUCT_MASTERS.find(
    (pm) => pm.category_slug.toLowerCase() === categorySlug.toLowerCase() && pm.slug.toLowerCase() === productSlug.toLowerCase()
  ) || PRODUCT_MASTERS.find((pm) => pm.slug.toLowerCase() === productSlug.toLowerCase());
}

export function getListingsByProductMaster(marketplaceProductSlug: string): VendorListing[] {
  return VENDOR_LISTINGS.filter(
    (l) => l.marketplace_product_slug.toLowerCase() === marketplaceProductSlug.toLowerCase()
  );
}

export function getListingBySlug(slug: string): VendorListing | undefined {
  return VENDOR_LISTINGS.find((l) => l.slug.toLowerCase() === slug.toLowerCase());
}

export function getRelatedProducts(currentProductSlug: string, categorySlug?: string): ProductMaster[] {
  return PRODUCT_MASTERS.filter(
    (pm) => pm.slug.toLowerCase() !== currentProductSlug.toLowerCase()
  ).slice(0, 3);
}
