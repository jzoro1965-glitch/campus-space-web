<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Desk;
use Illuminate\Http\Request;

class DeskController extends Controller
{
    public function index()
    {
        $desks = Desk::withCount('bookings')->orderBy('code')->get();
        return view('admin.desks.index', compact('desks'));
    }

    public function create()
    {
        return view('admin.desks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'     => ['required', 'string', 'max:20', 'unique:desks'],
            'location' => ['required', 'string', 'max:100'],
        ]);

        Desk::create($request->only('code', 'location'));

        return redirect()->route('admin.desks.index')
            ->with('success', "Meja {$request->code} berhasil ditambahkan.");
    }

    public function edit(Desk $desk)
    {
        return view('admin.desks.edit', compact('desk'));
    }

    public function update(Request $request, Desk $desk)
    {
        $request->validate([
            'code'     => ['required', 'string', 'max:20', 'unique:desks,code,' . $desk->id],
            'location' => ['required', 'string', 'max:100'],
        ]);

        $desk->update($request->only('code', 'location'));

        return redirect()->route('admin.desks.index')
            ->with('success', "Meja {$desk->code} berhasil diperbarui.");
    }

    public function destroy(Desk $desk)
    {
        $desk->delete();

        return redirect()->route('admin.desks.index')
            ->with('success', 'Meja berhasil dihapus.');
    }
}
