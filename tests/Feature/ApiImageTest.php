<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ApiImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('CategoryTableSeeder');
    }

    public function testImageUpload(): void
    {
        Passport::actingAs(factory(User::class)->make());
        Storage::fake('heroku');
        $file = UploadedFile::fake()->image('image.jpg');

        $this->json('POST', '/api/images', ['file' => $file]);

        Storage::disk('heroku')->assertExists(
            config('app.image_path') . '/' . $file->hashName()
        );
    }

    public function testGuestImageUpload(): void
    {
        Storage::fake('heroku');
        $file = UploadedFile::fake()->image('image.jpg');

        $this->json('POST', '/api/images', ['file' => $file]);

        Storage::disk('heroku')->assertMissing(
            config('app.image_path') . '/' . $file->hashName()
        );
    }

    public function testImageDelete(): void
    {
        Passport::actingAs(factory(User::class)->make());
        Storage::fake('heroku');
        $file = UploadedFile::fake()->image('image.jpg');
        $this->json('POST', '/api/images', ['file' => $file]);
        Storage::disk('heroku')->assertExists(
            config('app.image_path') . '/' . $file->hashName()
        );

        $this->delete('/api/images/' . $file->hashName());

        Storage::disk('heroku')->assertMissing(
            config('app.image_path') . '/' . $file->hashName()
        );
    }

    public function testGuestImageDelete(): void
    {
        Storage::fake('heroku');

        $res = $this->delete('/api/images/BDLL9569402321714.jpeg');

        $res->assertStatus(302);
        $res->assertRedirect(route('login'));
    }
}
