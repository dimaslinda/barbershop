<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BranchSelectionController extends Controller
{
    /**
     * Menampilkan halaman untuk memilih cabang.
     */
    public function showSelectionForm()
    {
        $branches = Branch::all();
        // Jika hanya ada 1 cabang dan belum ada di session, langsung pilih cabang itu
        if ($branches->count() === 1 && !Session::has('selected_branch_id')) {
            Session::put('selected_branch_id', $branches->first()->id);
            Session::put('selected_branch_code', $branches->first()->code);
            return redirect()->route('pos.home'); // Redirect ke halaman POS
        }

        return view('branch_selection', compact('branches'));
    }

    /**
     * Memproses pilihan cabang dari user.
     */
    public function selectBranch(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);

        $branch = Branch::find($request->branch_id);

        if ($branch) {
            Session::put('selected_branch_id', $branch->id);
            Session::put('selected_branch_code', $branch->code);
            return redirect()->route('pos.home')->with('success', 'Cabang ' . $branch->name . ' telah dipilih.');
        }

        return back()->withErrors('Cabang tidak valid.');
    }

    /**
     * Mengubah atau keluar dari pemilihan cabang saat ini.
     */
    public function clearSelection()
    {
        Session::forget('selected_branch_id');
        Session::forget('selected_branch_code');
        return redirect()->route('branch.select')->with('info', 'Pilihan cabang telah dihapus. Silakan pilih cabang baru.');
    }
}
