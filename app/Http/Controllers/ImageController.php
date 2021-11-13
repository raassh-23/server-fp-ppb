<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
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

            $imageName = 'image/' . uniqid('img_', true) . '.' . $extension;

            Storage::disk('public')->put($imageName, base64_decode($image));
        } catch (\Throwable $th) {
            return $this->sendError('Gagal upload', $th->getMessage(), 500);
        }

        $response = [
            'name' => $imageName,
            'link' => asset('storage/' . $imageName),
            'base64' => $image_64
        ];

        return $this->sendResponse("Gambar berhasil tersimpan",);
    }

    public function list()
    {
        $images = Storage::disk('public')->files('image');

        $response = [];

        foreach ($images as $image) {
            $response[] = [
                'name' => substr($image, 6),
                'link' => asset('storage/' . $image),
                'base64' => base64_encode(Storage::disk('public')->get($image))
            ];
        }

        return $this->sendResponse($response, 'Berhasil');
    }

    public function getImage($name)
    {
        $imageName = 'image/' . $name;

        try {
            $image = Storage::disk('public')->get($imageName);
        } catch (FileNotFoundException $e) {
            return $this->sendError('Gambar tidak ditemukan', 404);
        } catch (\Throwable $th) {
            return $this->sendError('Server Error', $th->getMessage(), 500);
        }

        $type = pathinfo(public_path($imageName), PATHINFO_EXTENSION);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($image);

        $response = [
            'name' => $name,
            'link' => asset('storage/' . $name),
            'base64' => $base64
        ];

        return $this->sendResponse("Gambar berhasil diambil", $response);
    }
}
