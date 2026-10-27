<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $categories = Category::query()
            ->withCount('books')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori buku berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['required', 'string', 'max:500'],
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori buku berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->books()->exists()) {
            return redirect()->route('admin.categories.index')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh buku.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Kategori buku berhasil dihapus.');
    }
}
