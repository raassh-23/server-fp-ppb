<?php

namespace App\Http\Controllers;

use App\Models\Translation;
use Illuminate\Http\Request;
use JoggApp\GoogleTranslate\GoogleTranslateFacade;

class TranslationController extends ApiController
{
    public function translate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'text' => 'required',
            'to' => 'string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal', $validator->errors(), 400);
        }

        try {
            $translate = GoogleTranslateFacade::translate($request->text, $request->to);

            $translateModel = Translation::create($translate);

            return $this->sendResponse("Berhasil diterjemahkan", $translateModel);
        } catch (\Throwable $th) {
            return $this->sendError("Kode bahasa tidak valid", 400);
        }
    }

    public function history()
    {
        $translations = Translation::all();

        if ($translations->isEmpty()) {
            return $this->sendError('Terjemahan tidak ditemukan', [], 404);
        }

        return $this->sendResponse("Daftar terjemahan berhasil didapatkan", $translations);
    }
}
