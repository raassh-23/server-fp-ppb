<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use JoggApp\GoogleTranslate\GoogleTranslateFacade;

class ImageController extends ApiController
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required',
            'translate_to' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors(), 400);
        }

        try {
            $image_64 = $request->image;

            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageFile = base64_decode($image);

            $imageName = uniqid('img_', true) . '.' . $extension;

            Storage::disk('s3')->put('image/' . $imageName, $imageFile);

            $url = Storage::disk('s3')->url('image/' . $imageName);

            $annotator = new ImageAnnotatorClient();
            $response = $annotator->textDetection($imageFile);
            $texts = $response->getTextAnnotations();

            if ($error = $response->getError()) {
                return $this->sendError('Gagal membaca', $error->getMessage(), 500);
            }
            
            $result = null;
            $langCode = null;

            if(count($texts) > 0) {
                $result = $response->getTextAnnotations()[0]->getDescription();
                $langCode = GoogleTranslateFacade::detectLanguage($result)['language_code'];
            }

            $image = Image::create([
                'name' => $imageName,
                'url' => $url,
                'text' => $result,
                'language' => $langCode,
            ]);

            return $this->sendResponse("Gambar berhasil tersimpan", $image);
        } catch (\Throwable $th) {
            return $this->sendError('Gagal upload', $th->getMessage(), 500);
        }
    }

    public function list()
    {
        $images = Image::all();

        if ($images->isEmpty()) {
            return $this->sendError('Gambar tidak ditemukan', [], 404);
        }

        return $this->sendResponse('Berhasil', $images);
    }
}
