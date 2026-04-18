<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NectaResultsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_necta_results_successfully()
    {
        // Prepare payload
        $payload = [
            'index_number' => 'S1234/0001/2020',
            'exam_type' => 'CSEE',
            'exam_body' => 'NECTA'
        ];

        // Call your API route
        $response = $this->postJson('/necta/results', $payload);

        // Check status
        $response->assertStatus(200);

        // Check structure of returned data
        $response->assertJsonStructure([
            'success',
            'data' => [
                'candidate_name',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'school_name',
                'school_number',
                'year',
                'division',
                'points',
                'subjects' => [
                    ['subject', 'grade', 'points']
                ]
            ]
        ]);

        // Optional: assert success true
        $response->assertJson(['success' => true]);
    }
}
