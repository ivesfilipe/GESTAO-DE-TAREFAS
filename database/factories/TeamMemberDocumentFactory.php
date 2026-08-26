<?php

namespace Database\Factories;

use App\Models\TeamMemberDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMemberDocument>
 */
class TeamMemberDocumentFactory extends Factory
{
    protected $model = TeamMemberDocument::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->liderado(),
            'name' => $this->faker->words(3, true).'.txt',
            'path' => 'team-documents/1/documento.txt',
            'mime_type' => 'text/plain',
            'size' => 1024,
            'extracted_text' => $this->faker->paragraph(),
            'metadata' => null,
        ];
    }
}
