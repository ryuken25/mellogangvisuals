import { Composition } from "remotion";
import { showcaseReelConfig, ShowcaseReel } from "./compositions/ShowcaseReel";
import { Opening, Closing } from "./compositions/Bookends";
import { SceneFrame } from "./compositions/SceneFrame";
import { bookend, width, height, fps } from "./theme";

export const RemotionRoot: React.FC = () => {
  return (
    <>
      <Composition
        id="ShowcaseReel"
        component={ShowcaseReel}
        durationInFrames={showcaseReelConfig.durationInFrames}
        fps={fps}
        width={width}
        height={height}
      />
      <Composition
        id="Opening"
        component={Opening}
        durationInFrames={bookend.durationInFrames}
        fps={fps}
        width={width}
        height={height}
      />
      <Composition
        id="Closing"
        component={Closing}
        durationInFrames={bookend.durationInFrames}
        fps={fps}
        width={width}
        height={height}
      />
    </>
  );
};
