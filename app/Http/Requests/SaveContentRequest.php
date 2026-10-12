<?php

namespace App\Http\Requests;

use App\Models\ContentEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $entry = $this->route('entry');

        return [
            'kind' => ['required', Rule::in(ContentEntry::KINDS)],
            'slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('content_entries')->where('kind', $this->input('kind'))->ignore($entry)],
            'title' => ['required', 'string', 'max:255'], 'summary' => ['nullable', 'string', 'max:2000'],
            'body' => ['nullable', 'string', 'max:80000'], 'category' => ['nullable', 'string', 'max:150'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'image_alt' => ['nullable', 'string', 'max:255'], 'published' => ['required', 'boolean'],
            'position' => ['required', 'integer', 'min:0', 'max:9999'],
            'items' => ['nullable', 'string', 'max:4000'], 'note' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', Rule::in(['sand', 'lavender', 'mint'])],
            'source_url' => ['nullable', 'url:http,https', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $entry = $this->route('entry');
            if ($entry && ($entry->kind !== $this->input('kind') || $entry->slug !== $this->input('slug'))) {
                $validator->errors()->add('slug', 'Published addresses are stable. Create a new entry and an explicit redirect to change an address.');
            }
            if ($this->input('kind') === 'page' && ! in_array($this->input('slug'), ['privacy-policy', 'terms-and-conditions', 'about', 'careers'], true)) {
                $validator->errors()->add('slug', 'Supported page addresses: about, careers, privacy-policy, terms-and-conditions.');
            }
            if ($this->boolean('published') && $this->input('kind') === 'project' && ! $this->hasFile('image') && ! $entry?->image_path) {
                $validator->errors()->add('image', 'A published project needs an image.');
            }
        }];
    }
}
