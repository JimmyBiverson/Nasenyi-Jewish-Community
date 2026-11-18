<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityEvent;
use App\Models\DonationCause;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(string $collection): View
    {
        $collectionData = $this->describe($collection);
        $query = $collectionData['model']::query();
        if ($collection === 'gallery') {
            $query->orderBy('sort_order');
        }

        return view('admin.collections.index', [
            'collection' => $collection,
            'name' => $collectionData['name'],
            'records' => $query->latest('id')->paginate(25),
        ]);
    }

    public function create(string $collection): View
    {
        $collectionData = $this->describe($collection);

        return $this->form($collection, new $collectionData['model']);
    }

    public function store(Request $request, string $collection): RedirectResponse
    {
        $collectionData = $this->describe($collection);
        $record = new $collectionData['model'];
        $this->save($request, $collection, $record);

        return $this->editRedirect($collection, $record)->with('status', 'Record created.');
    }

    public function edit(string $collection, int $record): View
    {
        return $this->form($collection, $this->find($collection, $record));
    }

    public function update(Request $request, string $collection, int $record): RedirectResponse
    {
        $model = $this->find($collection, $record);
        $this->save($request, $collection, $model);

        return $this->editRedirect($collection, $model)->with('status', 'Changes saved.');
    }

    public function destroy(string $collection, int $record): RedirectResponse
    {
        $model = $this->find($collection, $record);
        if ($collection === 'causes') {
            // Archived causes retain their relationship to historical donations.
            $model->update(['is_active' => false]);
        } else {
            $model->delete();
        }

        return to_route('admin.collections.index', $collection)
            ->with('status', $collection === 'causes' ? 'Cause archived.' : 'Record deleted.');
    }

    /** @return array{model: class-string<Model>, name: string, singular: string} */
    private function describe(string $collection): array
    {
        return match ($collection) {
            'gallery' => ['model' => GalleryImage::class, 'name' => 'Gallery', 'singular' => 'image'],
            'events' => ['model' => CommunityEvent::class, 'name' => 'Events', 'singular' => 'event'],
            'causes' => ['model' => DonationCause::class, 'name' => 'Donation causes', 'singular' => 'cause'],
            default => abort(404),
        };
    }

    private function find(string $collection, int $record): Model
    {
        return $this->describe($collection)['model']::query()->findOrFail($record);
    }

    private function form(string $collection, Model $record): View
    {
        return view('admin.collections.form', [
            'collection' => $collection,
            'record' => $record,
            ...$this->describe($collection),
        ]);
    }

    private function editRedirect(string $collection, Model $record): RedirectResponse
    {
        return to_route('admin.collections.edit', ['collection' => $collection, 'record' => $record->getKey()]);
    }

    private function save(Request $request, string $collection, Model $record): void
    {
        $rules = ['title' => ['required', 'string', 'max:255']];

        if ($collection === 'gallery') {
            $data = $request->validate($rules + [
                'image_upload' => [$record->exists ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
                'alt_text' => ['required', 'string', 'max:255'],
                'caption' => ['nullable', 'string', 'max:1000'],
                'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            ]);

            if ($request->hasFile('image_upload')) {
                $path = $request->file('image_upload')->store('gallery', 'public');
                if ($path === false) {
                    throw ValidationException::withMessages(['image_upload' => 'The image could not be saved.']);
                }
                $data['path'] = '/storage/'.$path;
            }
            unset($data['image_upload']);
            $data['is_published'] = $request->boolean('is_published');
        } else {
            $rules += [
                'slug' => ['required', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique($record->getTable())->ignore($record)],
                'description' => ['nullable', 'string', 'max:10000'],
            ];

            if ($collection === 'events') {
                $rules += [
                    'location' => ['nullable', 'string', 'max:255'],
                    'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
                    'ends_at' => ['nullable', 'date_format:Y-m-d\TH:i', 'after_or_equal:starts_at'],
                ];
                $rules['description'][0] = 'required';
            }

            $data = $request->validate($rules);
            $flag = $collection === 'causes' ? 'is_active' : 'is_published';
            $data[$flag] = $request->boolean($flag);
            if ($collection === 'events') {
                foreach (['starts_at', 'ends_at'] as $key) {
                    $data[$key] = filled($data[$key] ?? null) ? Carbon::parse($data[$key], 'Africa/Kampala')->utc() : null;
                }
            }
        }

        $record->fill($data)->save();
    }
}
