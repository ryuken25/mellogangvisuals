/**
 * Brand tokens — diselaraskan dengan app.css di CodeIgniter app.
 */
export const brand = {
  bg: "#0A0E0D",
  surface: "#111817",
  surface2: "#161F1D",
  border: "#20302C",
  text: "#E8F1EE",
  muted: "#8FA39D",
  brand: "#00F5B8",
  brand600: "#00D9A3",
  warn: "#FFC857",
  danger: "#FF5C6C",
  ok: "#2EE6A8",
} as const;

export const fonts = {
  display: "'Space Grotesk', 'Inter', system-ui, sans-serif",
  body: "'Inter', system-ui, sans-serif",
  mono: "'JetBrains Mono', 'Space Grotesk', monospace",
};

/**
 * Path ke folder screenshots di repo ini.
 * Path ini RELATIF terhadap project root tempat `npx remotion` dijalankan
 * (yaitu folder `video/`). Karena Remotion dijalankan dari `video/`,
 * path-nya `../pages/screenshots/...`.
 *
 * Untuk CLI pass lewat env kalau perlu override (mis. di CI pakai path absolut).
 */
const REPO = "..";

export interface Scene {
  file: string;
  label: string;
  title: string;
  subtitle: string;
  durationInFrames: number;
}

export const scenes: Scene[] = [
  {
    file: `${REPO}/pages/screenshots/publik/home-desktop.png`,
    label: "01",
    title: "A studio that moves with you.",
    subtitle: "Premium photo & video for weddings, corporate, products, and events.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/publik/portofolio-desktop.png`,
    label: "02",
    title: "Selected work, made with care.",
    subtitle: "Browse a curated archive of recent projects.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/publik/katalog-desktop.png`,
    label: "03",
    title: "Packages for every moment.",
    subtitle: "Find the right fit — from quick reels to full-day coverage.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/publik/login-desktop.png`,
    label: "04",
    title: "Sign in with one tap.",
    subtitle: "Google, email + OTP, or password. Pick your flow.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/pelanggan/dashboard-desktop.png`,
    label: "05",
    title: "Your bookings, in one place.",
    subtitle: "Track production, pay, and download results from Drive.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/admin/dashboard-desktop.png`,
    label: "06",
    title: "Operations at a glance.",
    subtitle: "KPIs, recent orders, and pending payments — live.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/admin/pembayaran-desktop.png`,
    label: "07",
    title: "Verify in seconds.",
    subtitle: "Approve payments, audit reports, and manage the team.",
    durationInFrames: 240,
  },
  {
    file: `${REPO}/pages/screenshots/editor/dashboard-desktop.png`,
    label: "08",
    title: "Built for editors.",
    subtitle: "Stage-by-stage production, notifications, and Drive delivery.",
    durationInFrames: 240,
  },
];

export const bookend = {
  openingTitle: "Mellogang Visuals",
  openingSub: "Photo & Video Maker - 2026 showcase",
  closingTitle: "Let's create.",
  closingSub: "mellogang.test  -  instagram  -  youtube",
  durationInFrames: 150,
};

export const fps = 30;
export const width = 1920;
export const height = 1080;
