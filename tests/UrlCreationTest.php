<?php

use App\Models\Url;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UrlCreationTest extends TestCase
{
    /**
     * Test if API is working.
     *
     * @return void
     */
    public function testApiIsOn()
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
}