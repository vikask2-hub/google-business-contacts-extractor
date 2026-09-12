<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchBusinessesRequest;
use App\Services\GooglePlacesService;
use Illuminate\View\View;

class GmbExtractorController extends Controller
{
    public function index(GooglePlacesService $places): View
    {
        $city = (string) config('gmb_extractor.default_city');
        $category = (string) config('gmb_extractor.default_category');

        return $this->render($city, $category, $places->search($city, $category));
    }

    public function search(SearchBusinessesRequest $request, GooglePlacesService $places): View
    {
        $search = $request->validated();

        return $this->render(
            $search['city'],
            $search['category'],
            $places->search($search['city'], $search['category']),
        );
    }

    /** @param array{results: array<int, array<string, string|null>>, mode: string, notice: string|null} $search */
    private function render(string $city, string $category, array $search): View
    {
        return view('gmb-extractor', [
            'cities' => config('gmb_extractor.cities'),
            'categories' => config('gmb_extractor.categories'),
            'selectedCity' => $city,
            'selectedCategory' => $category,
            ...$search,
        ]);
    }
}
