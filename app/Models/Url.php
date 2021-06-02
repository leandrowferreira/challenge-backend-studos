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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['slug', 'url', 'valid'];

    /**
     * Properties that need to be treated as date.
     *
     * @var array
     */
    protected $dates    = ['valid'];

    /**
     * Creates slug from URL.
     *
     * @param  string  $url      The URL to shorten
     * @param  string  $baseUrl  The base URL to generate full shortened path
     * @return App\Resources\UrlResult
     */
    public static function createSlug($url, $baseUrl)
    {
        $url = self::create([
            'slug'  => self::createNewSlug(),
            'url'   => $url,
            'valid' => Carbon::now()->addDays(env('URL_VALID_DAYS'))
        ]);

        return new UrlResult($baseUrl . $url->slug);
    }

    /**
     * Returns URL from slug. If the slug doesn't exist, return a 404 UrlResult.
     *
     * @param  string  $slug  The slug to search
     * @param  string  $ip    (optional) The caller IP to register the "click"
     * @return App\Resources\UrlResult
     */
    public static function showUrl($slug, $ip = null)
    {
        $instance = self::getUrlFromSlug($slug);

        if (!$instance) {
            return new UrlResult(null, 404, 'Not found');
        }

        $instance->clicks()->create(['ip' => $ip]);

        return new UrlResult('http://' . $instance->url);
    }

    /**
     * Checks if the current instance has expired
     * @return bool
     */
    protected function expired()
    {
        return Carbon::now()->diffInSeconds($this->valid, false) < 0;
    }

    /**
     * Checks for slug. If so, return your URL.
     * @param  string  $slug  The given slug
     * @return App\Models\Url|null
     */
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

    /**
     * Creates a slug not yet used.
     * @return string
     */
    protected static function createNewSlug()
    {
        do {
            $slug = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 10)), 0, random_int(5, 10));
        } while (self::where('slug', $slug)->count());

        return $slug;
    }

    /**
     * The "has many" relationship to URL clicks.
     */
    public function clicks()
    {
        return $this->hasMany(Click::class);
    }
}
