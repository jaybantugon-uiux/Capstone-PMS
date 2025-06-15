<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified');
    }

   public function index()
{
    $user = Auth::user();
    
    if ($user->role === 'admin') {
        $activeProjects = Project::where('archived', false)->with('creator')->orderBy('created_at', 'desc')->get();
        $archivedProjects = Project::where('archived', true)->with('creator')->orderBy('created_at', 'desc')->get();
    } else {
        $activeProjects = Project::where('created_by', $user->id)
            ->where('archived', false)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
        $archivedProjects = Project::where('created_by', $user->id)
            ->where('archived', true)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    return view('projects.index', compact('activeProjects', 'archivedProjects'));
}

    public function create()
    {
        // Check if user has permission to create projects
        $user = Auth::user();
        if (!in_array($user->role, ['pm', 'admin'])) {
            abort(403, 'Unauthorized to create projects.');
        }
        
        return view('projects.create');
    }

    public function store(Request $request)
    {
        // Check if user has permission to create projects
        $user = Auth::user();
        if (!in_array($user->role, ['pm', 'admin'])) {
            abort(403, 'Unauthorized to create projects.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $project = Project::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => $user->id,
            ]);

            return redirect()->route('projects.index')
                ->with('status', 'Project created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create project. Please try again.']);
        }
    }

    public function show(Project $project)
    {
        $user = Auth::user();
        
        // Check if user can view this project
        if ($user->role !== 'admin' && $project->created_by !== $user->id) {
            abort(403, 'Unauthorized to view this project.');
        }
        
        // Eager load tasks relationship if needed
        $project->load('tasks');
        
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $user = Auth::user();
        
        // Check if user can edit this project
        if ($user->role !== 'admin' && $project->created_by !== $user->id) {
            abort(403, 'Unauthorized to edit this project.');
        }
        
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Check if user can update this project
        if ($user->role !== 'admin' && $project->created_by !== $user->id) {
            abort(403, 'Unauthorized to update this project.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $project->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('projects.index')
                ->with('status', 'Project updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update project. Please try again.']);
        }
    }
    public function archive($id)
{
    $project = Project::findOrFail($id);
    $project->archived = true; // Set archived status to true
    $project->save();

    return redirect()->route('projects.index')->with('success', 'Project archived successfully.');
}

    public function restore($id)
    {
        $project = Project::findOrFail($id);
        $project->archived = false; // Set archived status to false
        $project->save();
    // Removed duplicate show method to avoid redeclaration error.
        $project = Project::with('tasks')->findOrFail($id);
        return view('projects.show', compact('project'));
    }
}