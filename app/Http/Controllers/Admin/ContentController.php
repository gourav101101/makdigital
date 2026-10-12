<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveContentRequest;
use App\Models\ContentEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $kind = in_array($request->query('kind'), ContentEntry::KINDS, true) ? $request->query('kind') : null;
        $entries = ContentEntry::query()->when($kind, fn ($query) => $query->where('kind', $kind))->orderBy('kind')->orderBy('position')->paginate(20)->withQueryString();

        return view('admin.content-index', compact('entries', 'kind'));
    }

    public function create(Request $request): View
    {
        $kind = in_array($request->query('kind'), ContentEntry::KINDS, true) ? $request->query('kind') : 'project';

        return view('admin.content-edit', ['entry' => new ContentEntry(['kind' => $kind, 'published' => false, 'position' => 0])]);
    }

    public function edit(ContentEntry $entry): View
    {
        return view('admin.content-edit', compact('entry'));
    }

    public function store(SaveContentRequest $request): RedirectResponse
    {
        return $this->save($request, new ContentEntry);
    }

    public function update(SaveContentRequest $request, ContentEntry $entry): RedirectResponse
    {
        return $this->save($request, $entry);
    }

    private function save(SaveContentRequest $request, ContentEntry $entry): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'items', 'note', 'color']);
        $data['details'] = array_replace($entry->details ?? [], [
            'items' => array_values(array_filter(array_map('trim', preg_split('/\r?\n/', $request->input('items') ?? '')))),
            'note' => $request->input('note'), 'color' => $request->input('color') ?: 'sand',
        ]);
        if ($request->hasFile('image')) {
            $image = imagecreatefromstring(file_get_contents($request->file('image')->getRealPath()));
            abort_unless($image !== false, 422);
            $width = min(1600, imagesx($image));
            $height = (int) round(imagesy($image) * $width / imagesx($image));
            $output = imagecreatetruecolor($width, $height);
            imagealphablending($output, false);
            imagesavealpha($output, true);
            imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
            $directory = public_path('images/uploads');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $filename = Str::uuid().'.webp';
            if (! imagewebp($output, $directory.'/'.$filename, 84)) {
                throw new \RuntimeException('Could not save image.');
            }
            imagedestroy($image);
            imagedestroy($output);
            $data['image_path'] = 'images/uploads/'.$filename;
        }
        $entry->fill($data)->save();

        return redirect()->route('admin.content.edit', $entry)->with('status', 'Content saved.');
    }
}
