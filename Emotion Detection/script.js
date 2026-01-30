const video = document.getElementById("video");
const locationBaseUrl = new URL(".", window.location.href).href;
const scriptBaseUrl = document.currentScript?.src
  ? new URL(".", document.currentScript.src).href
  : null;
const candidateBaseUrls = [
  scriptBaseUrl,
  locationBaseUrl,
  `${window.location.origin}/ANALYSE-FACIALE/Emotion%20Detection/`,
  `${window.location.origin}/Emotion%20Detection/`,
].filter(Boolean);

let assetsBaseUrl = locationBaseUrl;

async function resolveAssetsBaseUrl() {
  for (const baseUrl of candidateBaseUrls) {
    try {
      const response = await fetch(
        `${baseUrl}models/tiny_face_detector_model-weights_manifest.json`,
        { cache: "no-store" }
      );
      if (response.ok) {
        return baseUrl;
      }
    } catch (error) {
      // Ignore and try the next candidate
    }
  }
  return locationBaseUrl;
}

async function loadModels() {
  assetsBaseUrl = await resolveAssetsBaseUrl();
  const modelUrl = `${assetsBaseUrl}models`;
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
    faceapi.nets.ssdMobilenetv1.loadFromUri(modelUrl),
    faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
    faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
    faceapi.nets.faceExpressionNet.loadFromUri(modelUrl),
  ]);
}

loadModels()
  .then(startWebcam)
  .catch((error) => {
    console.error("Model load failed.", error);
  });

function startWebcam() {
  navigator.mediaDevices
    .getUserMedia({
      video: true,
      audio: false,
    })
    .then((stream) => {
      video.srcObject = stream;
    })
    .catch((error) => {
      console.error(error);
    });
}

function getLabeledFaceDescriptions() {
  const labels = ["ahmed", "elmostafa", "youssef"];
  return Promise.all(
    labels.map(async (label) => {
      const descriptions = [];
      for (let i = 1; i <= 2; i++) {
        const img = await faceapi.fetchImage(
          `${assetsBaseUrl}labels/${label}/${i}.png`
        );
        const detections = await faceapi
          .detectSingleFace(img)
          .withFaceLandmarks()
          .withFaceDescriptor();
        descriptions.push(detections.descriptor);
      }
      return new faceapi.LabeledFaceDescriptors(label, descriptions);
    })
  );
}

video.addEventListener("play", async () => {
  const labeledFaceDescriptors = await getLabeledFaceDescriptions();
  const faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors);

  const canvas = faceapi.createCanvasFromMedia(video);
  const videoWrap = document.getElementById("video-wrap");
  if (videoWrap) {
    videoWrap.append(canvas);
  } else {
    document.body.append(canvas);
  }

  const displaySize = { width: video.width, height: video.height };
  faceapi.matchDimensions(canvas, displaySize);

  setInterval(async () => {
    const detections = await faceapi
    .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
      .withFaceLandmarks()
      .withFaceDescriptors()
      .withFaceExpressions();

    const resizedDetections = faceapi.resizeResults(detections, displaySize);

    canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
    faceapi.draw.drawDetections(canvas, resizedDetections)
    faceapi.draw.drawFaceLandmarks(canvas, resizedDetections)
    faceapi.draw.drawFaceExpressions(canvas, resizedDetections)
    const results = resizedDetections.map((d) => {
      return faceMatcher.findBestMatch(d.descriptor);
    });
    results.forEach((result, i) => {
      const box = resizedDetections[i].detection.box;
      const drawBox = new faceapi.draw.DrawBox(box, {
        label: result,
      });
      drawBox.draw(canvas);
    });
  }, 100);
});
