import { AbsoluteFill, useCurrentFrame, useVideoConfig, interpolate, spring, Easing } from "remotion";
import { brand, fonts, bookend, width, height, fps } from "../theme";

/** Opening — logo + tagline fade in */
export const Opening: React.FC = () => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();

  // Logo big
  const logoScale = spring({ frame, fps, config: { damping: 14, stiffness: 80, mass: 0.8 } });
  const subOp = interpolate(frame, [25, 50], [0, 1], { extrapolateRight: "clamp" });
  const chipOp = interpolate(frame, [55, 75], [0, 1], { extrapolateRight: "clamp" });
  const fadeOut = interpolate(frame, [durationInFrames - 18, durationInFrames], [1, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  // Background sweep line
  const sweepX = interpolate(frame, [0, 50], [-200, 2200], { extrapolateRight: "clamp" });

  return (
    <AbsoluteFill style={{ background: brand.bg }}>
      {/* Subtle radial glow */}
      <div
        style={{
          position: "absolute",
          left: "30%",
          top: "20%",
          width: "60%",
          height: "60%",
          background: "radial-gradient(ellipse, rgba(0,245,184,0.20) 0%, transparent 60%)",
        }}
      />

      {/* Sweep line (sikat) */}
      <div
        style={{
          position: "absolute",
          left: sweepX,
          top: 0,
          width: 2,
          height: "100%",
          background: "linear-gradient(180deg, transparent 0%, #00F5B8 50%, transparent 100%)",
          opacity: 0.4,
        }}
      />

      {/* Eyebrow */}
      <div
        style={{
          position: "absolute",
          top: 80,
          left: 0,
          right: 0,
          textAlign: "center",
          color: brand.brand,
          fontFamily: fonts.display,
          fontWeight: 700,
          fontSize: 18,
          letterSpacing: "0.3em",
          textTransform: "uppercase",
          opacity: chipOp,
        }}
      >
        2026 · SHOWCASE
      </div>

      {/* Logo big */}
      <div
        style={{
          position: "absolute",
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          flexDirection: "column",
          opacity: fadeOut,
        }}
      >
        <div
          style={{
            fontFamily: fonts.display,
            fontWeight: 800,
            fontSize: 180,
            color: brand.text,
            letterSpacing: "-0.04em",
            lineHeight: 1,
            transform: `scale(${0.9 + 0.1 * logoScale})`,
            textShadow: "0 8px 40px rgba(0,0,0,0.6)",
          }}
        >
          <span style={{ color: brand.text }}>Mellogang</span>{" "}
          <span style={{
            color: "transparent",
            WebkitTextStroke: `2px ${brand.brand}`,
            fontStyle: "italic",
            fontWeight: 600,
          }}>Visuals</span>
        </div>

        <div
          style={{
            marginTop: 40,
            fontFamily: fonts.display,
            fontWeight: 500,
            fontSize: 28,
            color: brand.muted,
            letterSpacing: "0.1em",
            opacity: subOp,
          }}
        >
          {bookend.openingSub}
        </div>
      </div>
    </AbsoluteFill>
  );
};

/** Closing — call to action */
export const Closing: React.FC = () => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();

  const titleY = spring({ frame, fps, config: { damping: 14, stiffness: 90, mass: 0.7 } });
  const subOp = interpolate(frame, [30, 60], [0, 1], { extrapolateRight: "clamp" });
  const chipOp = interpolate(frame, [60, 80], [0, 1], { extrapolateRight: "clamp" });
  const ctaOp = interpolate(frame, [70, 100], [0, 1], { extrapolateRight: "clamp" });

  return (
    <AbsoluteFill style={{ background: brand.bg }}>
      <div
        style={{
          position: "absolute",
          left: "20%",
          top: "20%",
          width: "70%",
          height: "60%",
          background: "radial-gradient(ellipse, rgba(0,245,184,0.18) 0%, transparent 60%)",
        }}
      />

      <div
        style={{
          position: "absolute",
          inset: 0,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          flexDirection: "column",
        }}
      >
        <div
          style={{
            fontFamily: fonts.display,
            color: brand.brand,
            fontWeight: 700,
            fontSize: 18,
            letterSpacing: "0.3em",
            textTransform: "uppercase",
            marginBottom: 32,
            opacity: chipOp,
          }}
        >
          LET'S CREATE
        </div>

        <div
          style={{
            fontFamily: fonts.display,
            fontWeight: 800,
            fontSize: 160,
            color: brand.text,
            letterSpacing: "-0.03em",
            transform: `translateY(${(1 - titleY) * 30}px)`,
            textShadow: "0 8px 40px rgba(0,0,0,0.6)",
          }}
        >
          {bookend.closingTitle}
        </div>

        <div
          style={{
            marginTop: 40,
            fontFamily: fonts.body,
            fontSize: 30,
            color: brand.muted,
            letterSpacing: "0.05em",
            opacity: subOp,
          }}
        >
          {bookend.closingSub}
        </div>

        {/* CTA pill */}
        <div
          style={{
            marginTop: 60,
            display: "inline-block",
            padding: "20px 40px",
            background: brand.brand,
            color: brand.bg,
            borderRadius: 999,
            fontFamily: fonts.display,
            fontWeight: 700,
            fontSize: 22,
            letterSpacing: "0.05em",
            opacity: ctaOp,
            boxShadow: "0 0 60px rgba(0,245,184,0.4)",
          }}
        >
          mellogang.test →
        </div>
      </div>
    </AbsoluteFill>
  );
};
