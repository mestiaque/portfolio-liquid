<?php

namespace ME\Portfolio\Http\Controllers;

use ME\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use ME\Portfolio\Models\Project;
use ME\Services\RoleHierarchy;
use ME\Http\Middleware\AuthorizationMiddleware;

/**
 * Admin CRUD for the projects shown in the public "Selected work" section.
 * Once at least one project here is active, the public page uses these
 * instead of config('portfolio.projects').
 */
class ProjectController extends Controller
{

    public function __construct(private RoleHierarchy $hierarchy)
    {
        $this->middleware('authorization:project.view')->only(['index', 'show']);
        $this->middleware('authorization:project.create')->only(['create', 'store']);
        $this->middleware('authorization:project.edit')->only(['edit', 'update']);
        $this->middleware('authorization:project.delete')->only('destroy');
    }

    public function index()
    {
        $projects = Project::orderBy('order')->orderByDesc('created_at')->get();
        return view('portfolio::projects.index', compact('projects'));
    }

    public function create()
    {
        return view('portfolio::projects.create', [
            'project'    => new Project(['is_active' => true, 'order' => 0]),
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('projects', 'public');
        }

        Project::create($data);

        return redirect()->route(me_prefix() . '.projects.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        return view('portfolio::projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('portfolio::projects.edit', [
            'project'    => $project,
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($project->image && Storage::disk('public')->exists($project->image)) {
                Storage::disk('public')->delete($project->image);
            }
            $data['image'] = $request->hasFile('image') ? $request->file('image')->store('projects', 'public') : null;
        }

        $project->update($data);

        return redirect()->route(me_prefix() . '.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if ($project->image && Storage::disk('public')->exists($project->image)) {
            Storage::disk('public')->delete($project->image);
        }

        $project->delete();

        return redirect()->route(me_prefix() . '.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /** Category filter buttons on the public page come from config('portfolio.project_categories'). */
    protected function categoryOptions(): array
    {
        return collect(config('portfolio.project_categories', []))->pluck('label', 'id')->all();
    }

    /** Shared validation for create and update; returns the attributes to save (image excluded). */
    protected function validated(Request $request): array
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'summary'      => 'nullable|string|max:500',
            'description'  => 'nullable|string',
            'problem'      => 'nullable|string|max:5000',
            'solution'     => 'nullable|string|max:5000',
            'challenges'   => 'nullable|string|max:5000',
            'features'     => 'nullable|string|max:5000',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'icon'         => ['nullable', 'string', 'max:60', 'regex:/^[a-z0-9-]+$/'],
            'url'          => 'nullable|url|max:255',
            'github_url'   => 'nullable|url|max:255',
            'technologies' => 'nullable|string|max:1000',
            'categories'   => 'nullable|array',
            'categories.*' => ['string', Rule::in(array_keys($this->categoryOptions()))],
            'order'        => 'nullable|integer|min:0',
        ], [
            'icon.regex' => 'Use a Bootstrap Icons name such as "cart3" or "building-gear".',
        ]);

        // "a, b, c" and one-per-line text become clean arrays; an empty field clears the list.
        $list = fn (?string $text, string $separator) => array_values(array_filter(array_map('trim', explode($separator, (string) $text))));

        return [
            'title'        => $request->input('title'),
            'summary'      => $request->input('summary'),
            'description'  => $request->input('description'),
            'problem'      => $request->input('problem'),
            'solution'     => $request->input('solution'),
            'challenges'   => $request->input('challenges'),
            'features'     => $list(str_replace("\r", '', (string) $request->input('features')), "\n"),
            'icon'         => $request->input('icon'),
            'url'          => $request->input('url'),
            'github_url'   => $request->input('github_url'),
            'technologies' => $list($request->input('technologies'), ','),
            'categories'   => array_values($request->input('categories', [])),
            'order'        => (int) $request->input('order', 0),
            'is_active'    => $request->has('is_active'),
        ];
    }
}
