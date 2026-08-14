<?php

namespace App\Http\Controllers;

use App\Models\ShipNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipNoteController extends Controller
{
    public function index(): View
    {
        $notes = ShipNote::query()->latest()->get()->groupBy('weekday');

        return view('ship.index', [
            'notes' => $notes,
            'weekdays' => ShipNote::WEEKDAYS,
        ]);
    }

    public function create(): View
    {
        return view('ship.form', [
            'note' => new ShipNote(['weekday' => 'mon', 'verdict' => 'pending']),
            'weekdays' => ShipNote::WEEKDAYS,
            'verdicts' => ShipNote::VERDICTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        ShipNote::query()->create($data);

        return redirect()->route('ship.index')->with('ok', 'Note saved.');
    }

    public function edit(ShipNote $ship): View
    {
        return view('ship.form', [
            'note' => $ship,
            'weekdays' => ShipNote::WEEKDAYS,
            'verdicts' => ShipNote::VERDICTS,
        ]);
    }

    public function update(Request $request, ShipNote $ship): RedirectResponse
    {
        $ship->update($this->validated($request));

        return redirect()->route('ship.index')->with('ok', 'Note updated.');
    }

    public function destroy(ShipNote $ship): RedirectResponse
    {
        $ship->delete();

        return redirect()->route('ship.index')->with('ok', 'Note deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'weekday' => ['required', 'in:'.implode(',', array_keys(ShipNote::WEEKDAYS))],
            'title' => ['required', 'string', 'max:160'],
            'region' => ['required', 'string', 'max:120'],
            'company_habit' => ['required', 'string', 'max:160'],
            'project_type' => ['required', 'string', 'max:160'],
            'practice' => ['nullable', 'string', 'max:5000'],
            'verdict' => ['required', 'in:'.implode(',', ShipNote::VERDICTS)],
        ]);
    }
}
