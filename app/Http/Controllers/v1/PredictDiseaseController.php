<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\v1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PredictDiseaseController extends Controller
{
    private const DETECTOR_ENDPOINT = 'https://shunda012-fake-detector.hf.space/predict';

    /**
     * Receive an uploaded image and forward it to the remote FastAPI detector.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|file|image|max:5120',
        ]);

        /** @var \Illuminate\Http\UploadedFile $image */
        $image = $validated['image'];

        $stream = fopen($image->getRealPath(), 'rb');

        try {
            $detectorResponse = Http::timeout(30)
                ->acceptJson()
                ->attach('file', $stream, $image->getClientOriginalName())
                ->post(self::DETECTOR_ENDPOINT);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if ($detectorResponse->failed()) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to analyze image using remote detector.',
                'details' => $detectorResponse->body(),
            ], 502);
        }

        return response()->json([
            'status' => true,
            'message' => 'Image analyzed successfully.',
            'analysis' => $detectorResponse->json(),
        ]);
    }
}
