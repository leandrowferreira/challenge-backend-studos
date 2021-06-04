<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;

class UrlController extends Controller
{
    public function create($url)
    {
        $baseUrl = config('app.url') . '/';

        $res = Url::createSlug($url, $baseUrl);
        if (in_array($res->getCode(), ['200', '201']) !== false) {
            return response($res->getResult(), $res->getCode());
        }

        return response($res->getMessage(), $res->getCode());
    }

    public function show($slug, Request $request)
    {
        $res = Url::showUrl($slug, $request->ip());
        if ($res->getCode() == 200) {
            return redirect($res->getResult());
        }

        return response($res->getMessage(), $res->getCode());
    }
}
