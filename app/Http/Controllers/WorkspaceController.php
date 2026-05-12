<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $workspace = DB::transaction(function () use ($data, $request) {
            $workspace = Workspace::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5)),
                'description' => $data['description'] ?? null,
                'owner_id' => $request->user()->id,
            ]);

            $workspace->members()->attach($request->user()->id, ['role' => 'owner']);

            return $workspace;
        });

        return redirect()->route('dashboard', ['workspace' => $workspace->uuid]);
    }
}
