<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;

class ImageController extends ApiController
{
    public function makeImageResponse($name, $link, $base64) {
        return [
            'name' => $name,
            'link' => $link,
            'base64' => $base64
        ];
    }

    public function getBase64($imageName, $image) {
        $type = pathinfo(public_path($imageName), PATHINFO_EXTENSION);
        return 'data:image/' . $type . ';base64,' . base64_encode($image);
    }

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

            $imageName = 'image/' . uniqid('img_', true) . '.' . $extension;

            Storage::disk('public')->put($imageName, base64_decode($image));
        } catch (\Throwable $th) {
            return $this->sendError('Gagal upload', $th->getMessage(), 500);
        }

        $response = $this->makeImageResponse(
            $imageName,
            asset('storage/' . $imageName),
            $image_64
        );

        return $this->sendResponse("Gambar berhasil tersimpan", $response);
    }

    public function list()
    {
        $images = Storage::disk('public')->files('image');

        if(count($images) == 0) {
            return $this->sendError('Gambar tidak ditemukan', NULL, 404);
        }

        $response = [];

        foreach ($images as $imageName) {
            $response[] = $this->makeImageResponse(
                substr($imageName, 6),
                asset('storage/' . $imageName),
                $this->getBase64($imageName, Storage::disk('public')->get($imageName))
            );
        }

        return $this->sendResponse('Berhasil', $response);
    }

    public function getImage($name)
    {
        $imageName = 'image/' . $name;

        try {
            $image = Storage::disk('public')->get($imageName);
        } catch (FileNotFoundException $e) {
            return $this->sendError('Gambar tidak ditemukan', NULL, 404);
        } catch (\Throwable $th) {
            return $this->sendError('Server Error', $th->getMessage(), 500);
        }
        
        $response = $this->makeImageResponse(
            $name,
            asset('storage/' . $imageName),
            $this->getBase64($imageName, $image)
        );

        return $this->sendResponse("Gambar berhasil diambil", $response);
    }
}
