import { AbsoluteFill, Sequence, useCurrentFrame } from "remotion";
import { scenes, bookend, width, height, fps } from "../theme";
import { SceneFrame } from "./SceneFrame";
import { Opening, Closing } from "./Bookends";

/**
 * Showcase reel - sequence all scenes + opening + closing.
 */
export const ShowcaseReel: React.FC = () => {
  const totalScenes = scenes.reduce((s, sc) => s + sc.durationInFrames, 0);
  const totalDuration =
    bookend.durationInFrames + totalScenes + bookend.durationInFrames;

  return (
    <AbsoluteFill style={{ background: "#0A0E0D" }}>
      <Sequence from={0} durationInFrames={bookend.durationInFrames}>
        <Opening />
      </Sequence>

      {scenes.map((scene, i) => {
        const offset = scenes
          .slice(0, i)
          .reduce((s, sc) => s + sc.durationInFrames, 0);
        const from = bookend.durationInFrames + offset;
        return (
          <Sequence key={i} from={from} durationInFrames={scene.durationInFrames}>
            <Scene
              screenshotSrc={scene.file}
              label={scene.label}
              title={scene.title}
              subtitle={scene.subtitle}
              durationInFrames={scene.durationInFrames}
            />
          </Sequence>
        );
      })}

      <Sequence
        from={totalDuration - bookend.durationInFrames}
        durationInFrames={bookend.durationInFrames}
      >
        <Closing />
      </Sequence>
    </AbsoluteFill>
  );
};

const Scene: React.FC<{
  screenshotSrc: string;
  label: string;
  title: string;
  subtitle: string;
  durationInFrames: number;
}> = ({ screenshotSrc, label, title, subtitle, durationInFrames }) => {
  const frame = useCurrentFrame();
  return (
    <SceneFrame
      screenshotSrc={screenshotSrc}
      label={label}
      title={title}
      subtitle={subtitle}
      frame={frame}
      durationInFrames={durationInFrames}
    />
  );
};

export const showcaseReelConfig = {
  id: "ShowcaseReel",
  component: ShowcaseReel,
  durationInFrames: (() => {
    const totalScenes = scenes.reduce((s, sc) => s + sc.durationInFrames, 0);
    return bookend.durationInFrames + totalScenes + bookend.durationInFrames;
  })(),
  fps,
  width,
  height,
};
