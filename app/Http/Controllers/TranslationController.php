<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JoggApp\GoogleTranslate\GoogleTranslateFacade;

class TranslationController extends ApiController
{
    public function getAvailableLanguages($lang)
    {
        try {
            $available = GoogleTranslateFacade::getAvaliableTranslationsFor($lang);
            return $this->sendResponse("Daftar bahasa berhasil didapatkan", $available);
        } catch (\Throwable $th) {
            return $this->sendError("Kode bahasa tidak valid", 400);
        }
    }

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
            return $this->sendResponse("Berhasil diterjemahkan", $translate);
        } catch (\Throwable $th) {
            return $this->sendError("Kode bahasa tidak valid", 400);
        }
    }
}
