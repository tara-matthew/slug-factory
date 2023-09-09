<?php

namespace Tests\Feature;

use App\Models\FilamentBrand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FilamentBrandControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_list_of_filament_brands()
    {
        // arrange
        $filamentBrands = FilamentBrand::factory()->count(3)->create();

        // act
        $response = $this->getJson('api/filament-brands');

        // assert
        $response
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                  '*' =>  [
                    'id',
                    'name',
                    'created_at',
                    'updated_at',
                    ]
                ]
            ])
            ->assertJson([
                'data' => $filamentBrands->toArray()
            ]);
    }

    /** @test */
    public function it_returns_an_empty_collection_of_filament_brands_when_no_records_exist()
    {
        $response = $this->getJson('api/filament-brands');

        $response
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonStructure([
                'data' => [],
            ]);
    }

    /** @test  */
    public function it_returns_a_specific_filament_brand()
    {
        /**
         * @var FilamentBrand $filamentBrand
         */
        $filamentBrand = FilamentBrand::factory()->create();

        $response = $this->getJson("/api/filament-brands/$filamentBrand->id");

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $filamentBrand->id,
                    'name' => $filamentBrand->name,
                ]
            ]);

    }

    /** @test */
    public function it_stores_a_filament_brand()
    {

    }

    /** @test */
    public function store_validates_using_a_form_request()
    {

    }
}