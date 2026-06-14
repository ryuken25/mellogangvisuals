import { AbsoluteFill, useCurrentFrame, useVideoConfig, interpolate, spring, Easing } from "remotion";

/**
 * Wrapper untuk scene screenshot.
 *
 * Animasi:
 *  - Subtle zoom-in (Ken Burns effect) pada screenshot
 *  - Vignette + brand gradient overlay
 *  - Label muncul slide dari kiri
 *  - Headline + subtitle fade + slide up
 *  - Border glow tepi (animated)
 */
export const SceneFrame: React.FC<{
  screenshotSrc: string;
  label: string;
  title: string;
  subtitle: string;
  /** frame offset in scene (0..durationInFrames) */
  frame: number;
  durationInFrames: number;
}> = ({ screenshotSrc, label, title, subtitle, frame, durationInFrames }) => {
  // Ken Burns: zoom 1.0 -> 1.06 over the whole scene
  const zoom = interpolate(frame, [0, durationInFrames], [1.0, 1.06], {
    extrapolateRight: "clamp",
    easing: Easing.bezier(0.4, 0, 0.2, 1),
  });
  // Pan sedikit: dari center ke sedikit ke atas-kanan
  const panY = interpolate(frame, [0, durationInFrames], [0, -20], {
    extrapolateRight: "clamp",
  });
  const panX = interpolate(frame, [0, durationInFrames], [0, -10], {
    extrapolateRight: "clamp",
  });

  // Animasi masuk
  const intro = spring({
    frame,
    fps: 30,
    config: { damping: 18, stiffness: 110, mass: 0.6 },
  });
  const labelX = interpolate(intro, [0, 1], [-200, 0]);
  const titleY = interpolate(intro, [0, 1], [40, 0]);
  const titleOp = interpolate(intro, [0, 1], [0, 1]);
  const subOp = interpolate(intro, [0, 1], [0, 1], { extrapolateRight: "clamp" });
  const subDelay = interpolate(frame, [10, 35], [0, 1], { extrapolateRight: "clamp" });

  // Animasi keluar di 0.8s terakhir
  const fadeOutStart = durationInFrames - 18;
  const fadeOut = interpolate(frame, [fadeOutStart, durationInFrames], [1, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  return (
    <AbsoluteFill style={{ backgroundColor: "#0A0E0D" }}>
      {/* Screenshot with Ken Burns */}
      <div
        style={{
          position: "absolute",
          inset: 0,
          backgroundImage: `url("${screenshotSrc}")`,
          backgroundSize: "cover",
          backgroundPosition: "center",
          transform: `scale(${zoom}) translate(${panX}px, ${panY}px)`,
          opacity: fadeOut,
        }}
      />

      {/* Vignette gelap supaya teks kontras */}
      <div
        style={{
          position: "absolute",
          inset: 0,
          background:
            "radial-gradient(ellipse at center, rgba(10,14,13,0.55) 0%, rgba(10,14,13,0.85) 70%, rgba(10,14,13,0.95) 100%)",
          opacity: fadeOut,
        }}
      />

      {/* Gradient brand accent (teal glow) di kiri-bawah */}
      <div
        style={{
          position: "absolute",
          left: "-10%",
          bottom: "-10%",
          width: "60%",
          height: "60%",
          background:
            "radial-gradient(ellipse, rgba(0,245,184,0.18) 0%, transparent 60%)",
          opacity: fadeOut,
        }}
      />

      {/* Brand watermark top-left */}
      <div
        style={{
          position: "absolute",
          top: 40,
          left: 48,
          color: "#E8F1EE",
          fontFamily: "'Space Grotesk', 'Inter', sans-serif",
          fontWeight: 700,
          fontSize: 18,
          letterSpacing: "0.18em",
          textTransform: "uppercase",
          opacity: 0.85,
          transform: `translateX(${labelX}px)`,
        }}
      >
        <span style={{ color: "#00F5B8" }}>●</span>&nbsp;&nbsp;Mellogang Visuals
      </div>

      {/* Frame number top-right */}
      <div
        style={{
          position: "absolute",
          top: 40,
          right: 48,
          color: "#8FA39D",
          fontFamily: "'Space Grotesk', 'Inter', sans-serif",
          fontWeight: 500,
          fontSize: 16,
          letterSpacing: "0.2em",
          textTransform: "uppercase",
          opacity: fadeOut,
        }}
      >
        Scene {label} / 08
      </div>

      {/* Bottom-left headline block */}
      <div
        style={{
          position: "absolute",
          left: 80,
          bottom: 110,
          right: 80,
          color: "#E8F1EE",
          fontFamily: "'Space Grotesk', 'Inter', sans-serif",
        }}
      >
        {/* Tag */}
        <div
          style={{
            display: "inline-block",
            padding: "8px 16px",
            background: "rgba(0,245,184,0.12)",
            border: "1px solid rgba(0,245,184,0.4)",
            borderRadius: 999,
            color: "#00F5B8",
            fontSize: 14,
            fontWeight: 700,
            letterSpacing: "0.18em",
            textTransform: "uppercase",
            marginBottom: 24,
            opacity: titleOp,
            transform: `translateX(${labelX}px)`,
          }}
        >
          {label}
        </div>

        <div
          style={{
            fontSize: 76,
            fontWeight: 700,
            lineHeight: 1.05,
            letterSpacing: "-0.02em",
            maxWidth: 1500,
            opacity: titleOp,
            transform: `translateY(${titleY}px)`,
            textShadow: "0 4px 30px rgba(0,0,0,0.5)",
          }}
        >
          {title}
        </div>

        <div
          style={{
            marginTop: 18,
            fontSize: 28,
            fontWeight: 400,
            color: "#8FA39D",
            maxWidth: 1200,
            lineHeight: 1.4,
            opacity: subOp * subDelay,
            transform: `translateY(${titleY}px)`,
          }}
        >
          {subtitle}
        </div>
      </div>

      {/* Animated brand line (bottom) */}
      <div
        style={{
          position: "absolute",
          left: 80,
          bottom: 60,
          width: interpolate(frame, [0, 60], [0, 320], { extrapolateRight: "clamp" }),
          height: 2,
          background: "linear-gradient(90deg, #00F5B8 0%, transparent 100%)",
          opacity: fadeOut,
        }}
      />
    </AbsoluteFill>
  );
};
