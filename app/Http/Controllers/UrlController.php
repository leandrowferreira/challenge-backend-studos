<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;

class UrlController extends Controller
{
    public function create($url, Request $request)
    {
        $res = Url::createUrl($url, $request);
        if ($res->getCode() == 200) {
            return $res->getResult();
        }

        return response($res->getMessage(), $res->getCode());
    }

    public function show($slug, Request $request)
    {
        $res = Url::showUrl($slug, $request);
        if ($res->getCode() == 200) {
            return redirect($res->getResult());
        }

        return response($res->getMessage(), $res->getCode());
    }
}
