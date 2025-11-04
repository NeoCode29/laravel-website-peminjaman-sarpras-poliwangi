<?php

namespace Tests\Feature;

use App\Models\KategoriPrasarana;
use App\Models\Peminjaman;
use App\Models\Permission;
use App\Models\Prasarana;
use App\Models\PrasaranaImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PrasaranaManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_index_displays_prasarana_list(): void
    {
        $user = $this->createUserWithPermissions(['sarpras.view']);

        $prasarana = Prasarana::factory()->count(2)->create();

        $response = $this->actingAs($user)->get(route('prasarana.index'));

        $response->assertOk()
            ->assertViewIs('prasarana.index')
            ->assertViewHas('prasarana', function ($collection) use ($prasarana) {
                return $collection->pluck('id')->intersect($prasarana->pluck('id'))->count() === $prasarana->count();
            });
    }

    public function test_can_store_prasarana_with_images(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermissions(['sarpras.create']);
        $kategori = KategoriPrasarana::factory()->create();

        $payload = [
            'name' => 'Aula Serbaguna',
            'kategori_id' => $kategori->id,
            'description' => 'Tempat serbaguna untuk kegiatan kampus.',
            'lokasi' => 'Gedung Utama Lt. 2',
            'kapasitas' => 150,
            'status' => 'tersedia',
            'images' => [UploadedFile::fake()->image('foto.jpg')],
        ];

        $response = $this->actingAs($user)->post(route('prasarana.store'), $payload);

        $response->assertRedirect(route('prasarana.index'));
        $response->assertSessionHas('success', 'Prasarana berhasil ditambahkan.');

        $stored = Prasarana::where('name', 'Aula Serbaguna')->first();
        $this->assertNotNull($stored);
        $this->assertEquals($user->id, $stored->created_by);
        $this->assertEquals('tersedia', $stored->status);

        $stored->load('images');
        $this->assertCount(1, $stored->images);
        $imagePath = $stored->images->first()->image_url;
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_cannot_delete_prasarana_with_active_peminjaman(): void
    {
        $user = $this->createUserWithPermissions(['sarpras.delete']);
        $prasarana = Prasarana::factory()->create();

        $borrower = User::factory()->create();
        Peminjaman::factory()->create([
            'prasarana_id' => $prasarana->id,
            'user_id' => $borrower->id,
            'status' => Peminjaman::STATUS_PENDING,
        ]);

        $response = $this->from(route('prasarana.show', $prasarana))
            ->actingAs($user)
            ->delete(route('prasarana.destroy', $prasarana));

        $response->assertRedirect(route('prasarana.show', $prasarana));
        $response->assertSessionHas('error', 'Tidak dapat menghapus prasarana yang memiliki peminjaman aktif.');
        $this->assertDatabaseHas('prasarana', ['id' => $prasarana->id]);
    }

    public function test_can_delete_prasarana_without_active_usage(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermissions(['sarpras.delete']);
        $creator = User::factory()->create();
        $prasarana = Prasarana::factory()->create(['created_by' => $creator->id]);

        Storage::disk('public')->put('prasarana/images/sample.jpg', 'fake-image');
        PrasaranaImage::factory()->for($prasarana, 'prasarana')->create([
            'image_url' => 'prasarana/images/sample.jpg',
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($user)->delete(route('prasarana.destroy', $prasarana));

        $response->assertRedirect(route('prasarana.index'));
        $response->assertSessionHas('success', 'Prasarana berhasil dihapus.');
        $this->assertDatabaseMissing('prasarana', ['id' => $prasarana->id]);
        Storage::disk('public')->assertMissing('prasarana/images/sample.jpg');
        $this->assertDatabaseMissing('prasarana_images', ['prasarana_id' => $prasarana->id]);
    }

    private function createUserWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();

        foreach ($permissions as $permission) {
            $this->createPermissionIfMissing($permission);
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    private function createPermissionIfMissing(string $name): void
    {
        if (!Permission::where('name', $name)->exists()) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
                'display_name' => ucfirst(str_replace('.', ' ', $name)),
                'description' => 'Generated for tests.',
                'category' => explode('.', $name, 2)[0] ?? 'general',
                'is_active' => true,
            ]);
        }
    }
}
