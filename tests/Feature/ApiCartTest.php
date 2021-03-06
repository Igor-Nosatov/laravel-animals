<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Pet;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;
use function array_pop;
use function json_decode;
use function reset;

class ApiCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('CategoryTableSeeder');
        $this->seed('PetTableSeeder');
    }

    public function testGuestAccess(): void
    {
        $new = Pet::whereId(2)->first()->toArray();
        $new['quantity'] = 1;
        $response = $this->json('POST', '/api/cart', $new);
        $response->assertStatus(401);
    }

    public function testAddOnlyOneItem(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $old = Pet::whereId(1)->first()->toArray();
        $old['quantity'] = 1;
        $this->json('POST', '/api/cart', $old)
            ->assertStatus(200);

        $new = Pet::whereId(2)->first()->toArray();
        $new['quantity'] = 1;
        $response = $this->json('POST', '/api/cart', $new);
        $response->assertStatus(200);

        $result = $response->json();

        $expected = [
            'id' => $new['id'],
            'name' => $new['name'],
            'price' => (int) $new['price'],
            'quantity' => $new['quantity'],
            'attributes' => [],
            'conditions' => [],
        ];
        self::assertSame($expected, $result[2]);
    }

    public function testShowItem(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $new = Pet::inRandomOrder()->first()->toArray();
        $new['quantity'] = 1;
        $this->json('POST', '/api/cart', $new)
            ->assertStatus(200);

        $response = $this->get('/api/cart');

        $result = $response->json();

        $expected = [
            'id' => $new['id'],
            'name' => $new['name'],
            'price' => (int) $new['price'],
            'quantity' => $new['quantity'],
            'attributes' => [],
            'conditions' => [],
        ];
        self::assertSame($expected, reset($result));
    }

    public function testModifyItem(): void
    {
        $this->withoutExceptionHandling();
        Passport::actingAs(factory(User::class)->create());

        $new = Pet::inRandomOrder()->first()->toArray();
        $new['quantity'] = 1;
        $this->json('POST', '/api/cart', $new)
            ->assertStatus(200);

        $response = $this->patch('/api/cart/' . $new['id'], ['quantity' => +1]);

        $result = $response->json();

        $expected = [
            'id' => $new['id'],
            'name' => $new['name'],
            'price' => (int) $new['price'],
            'quantity' => $new['quantity'] + 1,
            'attributes' => [],
            'conditions' => [],
        ];
        self::assertSame($expected, reset($result));
    }

    public function testRemoveItem(): void
    {
        Passport::actingAs(factory(User::class)->create());

        $new = Pet::inRandomOrder()->first()->toArray();
        $new['quantity'] = 1;
        $this->json('POST', '/api/cart', $new)
            ->assertStatus(200);

        $response = $this->delete('/api/cart/' . $new['id']);

        $result = json_decode($response->getContent(), true);

        self::assertNull($result);
    }

    public function testTotal(): void
    {
        $this->withoutExceptionHandling();
        Passport::actingAs(factory(User::class)->create());

        $new = Pet::inRandomOrder()->first()->toArray();
        $new['quantity'] = 1;
        $this->json('POST', '/api/cart', $new)
            ->assertStatus(200);

        $response = $this->get('/api/cart/total');

        self::assertSame($new['price'], (float) $response->getContent());
    }

    public function testTotalQuantity(): void
    {
        $this->withoutExceptionHandling();
        Passport::actingAs(factory(User::class)->create());

        $new = Pet::inRandomOrder()->first()->toArray();
        $new['quantity'] = 1;
        $this->json('POST', '/api/cart', $new)
            ->assertStatus(200);

        $response = $this->get('/api/cart/quantity');

        self::assertSame($new['quantity'], (int) $response->getContent());
    }
}
