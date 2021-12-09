<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends ApiController
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required'
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

            $imageName = uniqid('img_', true) . '.' . $extension;
            
            Storage::disk('s3')->put('image/' . $imageName, base64_decode($image));
            $url = Storage::disk('s3')->url('image/' . $imageName);

            $annotator = new ImageAnnotatorClient();
            $content = file_get_contents($url);
            $response = $annotator->textDetection($content);
            $result = $response->getTextAnnotations()[0]->getDescription();

            if ($error = $response->getError()) {
                return $this->sendError('Gagal membaca', $error->getMessage(), 500);
            }

            $image = Image::create([
                'name' => $imageName,
                'url' => $url,
                // 'result' => $result
            ]);

            return $this->sendResponse("Gambar berhasil tersimpan", $image);
        } catch (\Throwable $th) {
            return $this->sendError('Gagal upload', $th->getMessage(), 500);
        }
    }

    public function list()
    {
        $images = Image::all();

        if($images->isEmpty()) {
            return $this->sendError('Gambar tidak ditemukan', [], 404);
        }

        return $this->sendResponse('Berhasil', $images);
    }
}
