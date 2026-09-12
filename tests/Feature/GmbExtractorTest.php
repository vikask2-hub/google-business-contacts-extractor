<?php

namespace Tests\Feature;

use App\Services\BusinessEmailFinder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class GmbExtractorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.default' => 'array']);
        Cache::clear();
    }

    public function test_directory_renders_prefilled_selectors_and_preview_results(): void
    {
        $response = $this->get(route('gmb-extractor.index'));

        $response
            ->assertSee('LocalReach')
            ->assertSee('Mumbai')
            ->assertSee('Digital Marketing Agencies')
            ->assertSee('8 businesses found')
            ->assertSee('Sample data')
            ->assertSee('Export CSV');
    }

    public function test_valid_search_returns_google_places_business_contacts(): void
    {
        config(['services.google_places.key' => 'test-google-key']);
        Http::preventStrayRequests();
        Http::fake([
            'https://places.googleapis.com/v1/places:searchText' => Http::response([
                'places' => [[
                    'id' => 'place-123',
                    'displayName' => ['text' => 'Acme Software'],
                    'formattedAddress' => 'Baner, Pune, Maharashtra',
                    'websiteUri' => 'https://acme.example',
                    'nationalPhoneNumber' => '020 4000 1234',
                    'googleMapsUri' => 'https://maps.google.com/?cid=123',
                ]],
            ]),
        ]);
        $this->mock(BusinessEmailFinder::class, function (MockInterface $mock): void {
            $mock->shouldReceive('findMany')
                ->once()
                ->with(['https://acme.example'])
                ->andReturn(['https://acme.example' => 'sales@acme.example']);
        });

        $response = $this->get(route('gmb-extractor.search', [
            'city' => 'pune',
            'category' => 'software-companies',
        ]));

        $response
            ->assertSee('Acme Software')
            ->assertSee('Baner, Pune, Maharashtra')
            ->assertSee('sales@acme.example')
            ->assertSee('020 4000 1234')
            ->assertSee('Live data');

        $cachedResponse = $this->get(route('gmb-extractor.search', [
            'city' => 'pune',
            'category' => 'software-companies',
        ]));

        $cachedResponse
            ->assertOk()
            ->assertSee('Acme Software')
            ->assertSee('Live data');
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://places.googleapis.com/v1/places:searchText'
            && $request['textQuery'] === 'Software Companies in Pune, India'
            && $request->hasHeader('X-Goog-FieldMask'));
        Http::assertSentCount(1);
    }

    public function test_search_rejects_values_outside_the_prefilled_selectors(): void
    {
        $response = $this->from(route('gmb-extractor.index'))->get(route('gmb-extractor.search', [
            'city' => 'not-a-city',
            'category' => 'not-a-category',
        ]));

        $response
            ->assertRedirect(route('gmb-extractor.index'))
            ->assertSessionHasErrors(['city', 'category']);
    }

    public function test_search_falls_back_to_preview_when_google_places_fails(): void
    {
        config(['services.google_places.key' => 'test-google-key']);
        Http::preventStrayRequests();
        Http::fake([
            'https://places.googleapis.com/v1/places:searchText' => Http::response(['error' => ['status' => 'RESOURCE_EXHAUSTED']], 429),
        ]);

        $response = $this->get(route('gmb-extractor.search', [
            'city' => 'delhi',
            'category' => 'restaurants',
        ]));

        $response
            ->assertSee('8 businesses found')
            ->assertSee('Live Google Places search is temporarily unavailable')
            ->assertSee('Sample data');
    }

    public function test_email_discovery_fetches_public_websites_once_and_caches_results(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://8.8.8.8/contact' => Http::response('<p>Email sales@first-business.example</p>', 200, ['Content-Type' => 'text/html']),
            'https://1.1.1.1/contact' => Http::response('<p>Email hello@second-business.example</p>', 200, ['Content-Type' => 'text/html']),
        ]);
        $websites = ['https://8.8.8.8/contact', 'https://1.1.1.1/contact'];
        $finder = app(BusinessEmailFinder::class);

        $emails = $finder->findMany($websites);
        $cachedEmails = $finder->findMany($websites);

        $this->assertSame('sales@first-business.example', $emails['https://8.8.8.8/contact']);
        $this->assertSame('hello@second-business.example', $emails['https://1.1.1.1/contact']);
        $this->assertSame($emails, $cachedEmails);
        Http::assertSentCount(2);
    }
}
