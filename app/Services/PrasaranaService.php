<?php

namespace App\Services;

use App\Models\Prasarana;
use App\Models\PrasaranaImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PrasaranaService
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Prasarana::query()->with(['kategori', 'images']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }
        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data, int $creatorId, array $images = []): Prasarana
    {
        return DB::transaction(function () use ($data, $creatorId, $images) {
            $data['created_by'] = $creatorId;
            $prasarana = Prasarana::create($data);
            $this->storeImages($prasarana, $images);
            return $prasarana->load(['kategori', 'images']);
        });
    }

    public function update(Prasarana $prasarana, array $data, array $newImages = []): Prasarana
    {
        return DB::transaction(function () use ($prasarana, $data, $newImages) {
            $prasarana->update($data);
            if (!empty($newImages)) {
                $this->storeImages($prasarana, $newImages);
            }
            return $prasarana->load(['kategori', 'images']);
        });
    }

    public function delete(Prasarana $prasarana): void
    {
        DB::transaction(function () use ($prasarana) {
            foreach ($prasarana->images as $img) {
                $this->deleteImageFile($img->image_url);
            }
            $prasarana->delete();
        });
    }

    public function deleteImage(PrasaranaImage $image): void
    {
        DB::transaction(function () use ($image) {
            $this->deleteImageFile($image->image_url);
            $image->delete();
        });
    }

    private function storeImages(Prasarana $prasarana, array $images): void
    {
        $currentMax = (int) $prasarana->images()->max('sort_order');
        foreach ($images as $index => $file) {
            if ($file instanceof UploadedFile) {
                $path = $file->store('prasarana', 'public');
                $prasarana->images()->create([
                    'image_url' => $path,
                    'sort_order' => $currentMax + $index + 1,
                ]);
            }
        }
    }

    private function deleteImageFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        if (str_starts_with($path, 'http')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = $parsedPath ?: $path;
        }

        $prefix = '/storage/';
        if (str_starts_with($path, $prefix)) {
            $path = substr($path, strlen($prefix));
        }

        $path = ltrim($path, '/');

        if ($path !== '') {
            Storage::disk('public')->delete($path);
        }
    }
}



