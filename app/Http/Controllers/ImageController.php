<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\Null_;

class ImageController extends ApiController
{
    public function makeImageResponse($name, $link, $base64 = NULL) {
        $response = [
            'name' => $name,
            'link' => $link
        ];

        if ($base64 != NULL) {
            $response['base64'] = $base64;
        }
        
        return $response;
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

            $imageName = uniqid('img_', true) . '.' . $extension;

            Storage::disk('s3')->put('image/' . $imageName, base64_decode($image));
        } catch (\Throwable $th) {
            return $this->sendError('Gagal upload', $th->getMessage(), 500);
        }

        $response = $this->makeImageResponse(
            $imageName,
            Storage::disk('s3')->url('image/' . $imageName)
        );

        return $this->sendResponse("Gambar berhasil tersimpan", $response);
    }

    public function list()
    {
        $images = Storage::disk('s3')->files('image');

        if(count($images) == 0) {
            return $this->sendError('Gambar tidak ditemukan', NULL, 404);
        }

        $response = [];

        foreach ($images as $imageName) {
            $response[] = $this->makeImageResponse(
                substr($imageName, 6),
                Storage::disk('s3')->url($imageName)
            );
        }

        return $this->sendResponse('Berhasil', $response);
    }

    public function getImage($name)
    {
        $imageName = 'image/' . $name;

        try {
            $image = Storage::disk('s3')->get($imageName);
        } catch (FileNotFoundException $e) {
            return $this->sendError('Gambar tidak ditemukan', NULL, 404);
        } catch (\Throwable $th) {
            return $this->sendError('Server Error', $th->getMessage(), 500);
        }
        
        $response = $this->makeImageResponse(
            $name,
            Storage::disk('s3')->url($imageName),
            base64_encode($image)
        );

        return $this->sendResponse("Gambar berhasil diambil", $response);
    }
}
