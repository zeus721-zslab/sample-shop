import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
  images: {
    remotePatterns: [
      {
        protocol: "https",
        hostname: "zslab-shop.duckdns.org",
      },
    ],
  },
};

export default nextConfig;
