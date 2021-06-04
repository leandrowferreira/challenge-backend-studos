<?php

namespace App\Models;

use Exception;
use Carbon\Carbon;
use App\Resources\UrlResult;
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
        // If the "Allow Multiple" option is off, we need
        // to check if URL has an existing slug. If so,
        // return this slug and don't create a new one.
        if (!config('url.allow_multiple')) {
            $checkUrl = self::getSlugFromUrl($url);

            if ($checkUrl) {
                return new UrlResult($baseUrl . $checkUrl->slug, 200);
            }
        }

        // If the "Check Before" option is on, we will
        // check if a call to the given URL will return
        // an error. If so, the result is HTTP "Unprocessable Entity"
        if (config('url.check_before') && !self::isValidUrl($url)) {
            return new UrlResult(null, 422, 'URL to shorten is invalid');
        }

        // Create and return the new slug
        $url = self::create([
            'slug'  => self::createNewSlug(),
            'url'   => $url,
            'valid' => Carbon::now()->addDays(config('url.valid_days'))
        ]);

        return new UrlResult($baseUrl . $url->slug, 201);
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

        //Slug not found or expired
        if (!$instance) {
            return new UrlResult(null, 404, 'Not found');
        }

        //Register a "click" into the shortened URL
        $instance->clicks()->create(['ip' => $ip]);

        // If the "Renovate on Access" option is on, every access
        // refreshes the URL expiration time
        if (config('url.renovate_on_access')) {
            $instance->update([
                'valid' => Carbon::now()->addDays(config('url.valid_days'))
            ]);
        }

        return new UrlResult('http://' . str_replace(['http://', 'https://'], '', $instance->url));
    }

    /**
     * Checks if the current instance has expired
     *
     * @return bool
     */
    protected function expired()
    {
        return Carbon::now()->diffInSeconds($this->valid, false) < 0;
    }

    /**
     * Checks for slug. If so, return your URL.
     *
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
     * Checks for URL. If so, return your slug.
     *
     * @param  string  $url  The given url
     * @return App\Models\Url|null
     */
    protected static function getSlugFromUrl($url)
    {
        $urls = self::where('url', $url)->latest()->get();

        foreach ($urls as $optUrl) {
            if (!$optUrl->expired()) {
                return $optUrl;
            }
        }

        return null;
    }

    /**
     * Creates a slug not yet used.
     *
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
     * Checks whether provided URL responds with informational (100) or successfull (200) code.
     *
     * @param  string $url  The URL to be checked
     * @return bool
     */
    protected static function isValidUrl($url)
    {
        try {
            return Http::get('http://' . str_replace(['http://', 'https://'], '', $url))->status() < 300;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * The "has many" relationship to URL clicks.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clicks()
    {
        return $this->hasMany(Click::class);
    }
}
