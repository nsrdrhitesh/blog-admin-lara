<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAuthorRequest;
use App\Http\Requests\Admin\UpdateAuthorRequest;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function __construct(protected AuthorService $authors) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Author::class);

        return view('admin.authors.index', [
            'authors' => $this->authors->list($request->only('search'), (int) $request->input('per_page', 20)),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Author::class);

        return view('admin.authors.form', ['author' => new Author]);
    }

    public function store(StoreAuthorRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('authors', 'public');
        }

        $this->authors->create($data);

        return redirect()->route('admin.authors.index')->with('status', 'author-created');
    }

    public function edit(Author $author): View
    {
        $this->authorize('update', $author);

        return view('admin.authors.form', ['author' => $author]);
    }

    public function update(UpdateAuthorRequest $request, Author $author): RedirectResponse
    {
        $data = $this->prepareData($request);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('authors', 'public');
        }

        $this->authors->update($author, $data);

        return redirect()->route('admin.authors.index')->with('status', 'author-updated');
    }

    public function destroy(Author $author): RedirectResponse
    {
        $this->authorize('delete', $author);

        $this->authors->delete($author);

        return back()->with('status', 'author-deleted');
    }

    /**
     * Convert the form's comma-separated "skills" text field into the
     * array the authors.skills JSON column expects.
     */
    protected function prepareData(Request $request): array
    {
        $data = $request->validated();

        $data['skills'] = ! empty($data['skills'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['skills']))))
            : [];

        return $data;
    }
}
