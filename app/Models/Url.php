<?php

namespace App\Models;

use Exception;
use Carbon\Carbon;
use App\Resources\UrlResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Url extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'url', 'valid'];
    protected $dates    = ['valid'];

    public static function createUrl($url, Request $request)
    {
        $baseUrl = substr($request->url(), 0, strrpos($request->url(), '/') + 1);

        $url = self::create([
            'slug'  => self::createNewSlug(),
            'url'   => $url,
            'valid' => Carbon::now()->addDays(env('URL_VALID_DAYS'))
        ]);

        return new UrlResult($baseUrl . $url->slug);
    }

    public static function showUrl($slug, Request $request)
    {
        $url = self::getUrlFromSlug($slug);

        if ($url) {
            $url->clicks()->create(['ip' => $request->ip()]);

            return new UrlResult('http://' . $url->url);
        } else {
            return new UrlResult(null, 404, 'Not found');
        }
    }

    protected function expired()
    {
        return Carbon::now()->diffInSeconds($this->valid, false) < 0;
    }

    protected static function getUrlFromSlug($slug)
    {
        $urls = self::where('slug', $slug)->latest()->get();

        foreach ($urls as $optUrl) {
            if (!$optUrl->expired()) {
                return $optUrl;
            }
        }

        return null;
    }

    protected static function createNewSlug()
    {
        do {
            $slug = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 10)), 0, random_int(5, 10));
        } while (self::where('slug', $slug)->count());

        return $slug;
    }

    public function clicks()
    {
        return $this->hasMany(Click::class);
    }
}
