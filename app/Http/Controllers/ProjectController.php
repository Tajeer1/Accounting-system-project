<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%")
                  ->orWhere('client_name', 'like', "%$search%");
            });
        }

        $projects = $query->paginate(12)->withQueryString();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Project::create($data);
        return redirect()->route('projects.index')->with('success', 'تم إضافة المشروع');
    }

    public function show(Project $project)
    {
        $project->load('purchases.category', 'invoices', 'journalEntries.lines.account');
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validateData($request, $project->id);
        $project->update($data);
        return redirect()->route('projects.show', $project)->with('success', 'تم تحديث المشروع');
    }

    public function destroy(Project $project)
    {
        if ($project->purchases()->exists() || $project->invoices()->exists()) {
            return back()->with('error', 'لا يمكن حذف مشروع له عمليات');
        }
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'تم حذف المشروع');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        $unique = 'unique:projects,code' . ($id ? ",$id" : '');

        return $request->validate([
            'code' => ['required', 'string', 'max:50', $unique],
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_value' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
