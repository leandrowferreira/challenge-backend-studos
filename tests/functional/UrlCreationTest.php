<?php

use App\Models\Url;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UrlCreationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test if API is working.
     *
     * @return void
     */
    public function testAppIsOn()
    {
        $this->get('/');

        $this->assertEquals(
            json_encode([
                'description' => 'StudoSlug URL Shortener',
                'version'     => '1.0'
            ]),
            $this->response->getContent()
        );
    }

    /**
     * Test if a known URL returns from your slug.
     *
     * @return void
     */
    public function testReturnBasicUrlFromSlug()
    {
        $this->get('/abc123ab');

        $this->assertStringContainsString('http://studos.com.br', $this->response->getContent());
    }

    /**
     * Test if a new URL return from your slug.
     *
     * @return void
     */
    public function testReturnNewUrlFromSlug()
    {
        config(['url.check_before' => 0]);

        $data = Url::factory()->create();

        $this->get('/' . $data->slug);

        $this->assertStringContainsString('http-equiv="refresh"', $this->response->getContent());
        $this->assertStringContainsString($data->url, $this->response->getContent());
    }

    /**
     * Test if a slug return from some invalid URL.
     * The option URL_CHECK_BEFORE is disabled for this test
     *
     * @return void
     */
    public function testCreateNewSlug()
    {
        config(['url.check_before' => 0]);

        $url = 'some.invalid.url.' . random_int(0, 10000) . '.absurd';
        $this->post('/' . $url);

        $this->assertResponseStatus(201);
    }

    /**
     * Test if a slug don't return from some invalid URL.
     * The option URL_CHECK_BEFORE is enabled for this test
     *
     * @return void
     */
    public function testDontCreateNewSlug()
    {
        config(['url.check_before' => 1]);

        $url = 'some.invalid.url.' . random_int(0, 99999) . '.absurd';
        $this->post('/' . $url);

        $this->assertResponseStatus(422);
    }

    /**
     * Test if a the same slug returns from the same URL creation
     * The option URL_ALLOW_MULTIPLE is disabled for this test
     *
     * @return void
     */
    public function testReturnSameSlug()
    {
        //Sample URL to be shortened
        $url = 'test.same.slug.' . random_int(0, 99999) . '.com.br';

        config([
            'url.check_before'   => 0,
            'url.allow_multiple' => 1, // Enabled just for the first creation
        ]);

        //Get first slug
        $slug  = explode('/', $this->post('/' . $url)->response->getContent());
        $slug1 = end($slug);

        $this->assertResponseStatus(201);

        config(['url.allow_multiple' => 0]);

        //Get second slug
        $slug  = explode('/', $this->post('/' . $url)->response->getContent());
        $slug2 = end($slug);

        $this->assertResponseStatus(200);

        $this->assertEquals($slug1, $slug2);
    }

    /**
     * Test if different slugs returns from the same URL creation
     * The option URL_ALLOW_MULTIPLE is enabled for this test
     *
     * @return void
     */
    public function testReturnDifferentSlug()
    {
        config([
            'url.check_before'   => 0,
            'url.allow_multiple' => 1,
        ]);

        //Sample URL to be shortened
        $url = 'test.different.slug.' . random_int(0, 99999) . '.com.br';

        //Get first slug
        $slug  = explode('/', $this->post('/' . $url)->response->getContent());
        $slug1 = end($slug);

        //Get second slug
        $slug  = explode('/', $this->post('/' . $url)->response->getContent());
        $slug2 = end($slug);

        $this->assertNotEquals($slug1, $slug2);
    }

    /**
     * Test if expiration datetime doesn't change after call
     * The option RENOVATE_ON_ACCESS is disabled for this test
     *
     * @return void
     */
    public function testNotChangingExpiration()
    {
        config([
            'url.check_before'       => 0,
            'url.renovate_on_access' => 0,
        ]);

        $data = Url::factory()->create();

        // Make first query
        $this->get('/' . $data->slug);
        $date1 = Url::where('slug', $data->slug)->first()->valid;

        sleep(1);

        // Make second query after 2 secs
        $this->get('/' . $data->slug);

        $date2 = Url::where('slug', $data->slug)->first()->valid;

        $this->assertEquals($date1->toString(), $date2->toString());
    }

    /**
     * Test if expiration datetime changes after call
     * The option RENOVATE_ON_ACCESS is enabled for this test
     *
     * @return void
     */
    public function testChangingExpiration()
    {
        config([
            'url.check_before'       => 0,
            'url.renovate_on_access' => 1,
        ]);

        $data = Url::factory()->create();

        // Make first query
        $this->get('/' . $data->slug);
        $date1 = Url::where('slug', $data->slug)->first()->valid;

        sleep(1);

        // Make second query after 1 sec
        $this->get('/' . $data->slug);
        $date2 = Url::where('slug', $data->slug)->first()->valid;

        $this->assertNotEquals($date1->toString(), $date2->toString());
    }

    /**
     * Test if an expired slug returns HTTP 404 error.
     *
     * @return void
     */
    public function testExpiredSlug()
    {
        config(['url.check_before' => 0]);

        //Valid entry
        $data = Url::factory()->create();

        $this->get('/' . $data->slug);
        $this->assertResponseStatus(302);

        //Mocks an expired entry
        $data->update([
            'valid' => Carbon::now()->subSecond(),
        ]);

        $this->get('/' . $data->slug);
        $this->assertResponseStatus(404);
    }
}
