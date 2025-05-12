<?php

namespace App\Http\Controllers\Backend;

use App\Models\Kandidat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KandidatController extends Controller
{
    // Index (Menampilkan semua kandidat)
    public function index()
    {
        try {
            $data['kandidat'] = Kandidat::all();
            return view('backend.kandidat.index', compact('data'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    // Form Tambah Kandidat
    public function create()
    {
        try {
            return view('backend.kandidat.create');
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    // Simpan Data Kandidat
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'nomor_urut' => 'required|integer',
                'visi' => 'required|string',
                'misi' => 'required|string',
                'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240', // Validasi file foto
            ]);

            // Upload Foto
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos/kandidat', 'public');
            }

            // Simpan Data ke Database
            Kandidat::create([
                'name' => $request->name,
                'nomor_urut' => $request->nomor_urut,
                'visi' => $request->visi,
                'misi' => $request->misi,
                'photo' => $photoPath, // Simpan path foto
            ]);

            return redirect()->route('backend.kandidat.index')->with('success', __('message.store'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    // Form Edit Kandidat
    public function edit($id)
    {
        try {
            $data['kandidat'] = Kandidat::findOrFail($id);
            return view('backend.kandidat.edit', compact('data'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    // Update Data Kandidat
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'nomor_urut' => 'required|integer',
                'visi' => 'required|string',
                'misi' => 'required|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // Validasi opsional untuk foto
            ]);

            $kandidat = Kandidat::findOrFail($id);

            // Kelola Unggahan Foto Baru
            if ($request->hasFile('photo')) {
                // Hapus Foto Lama
                if ($kandidat->photo) {
                    Storage::disk('public')->delete($kandidat->photo);
                }
                // Simpan Foto Baru
                $photoPath = $request->file('photo')->store('photos/kandidat', 'public');
            } else {
                $photoPath = $kandidat->photo; // Gunakan foto lama jika tidak ada file baru
            }

            // Update Data
            $kandidat->update([
                'name' => $request->name,
                'nomor_urut' => $request->nomor_urut,
                'visi' => $request->visi,
                'misi' => $request->misi,
                'photo' => $photoPath,
            ]);

            return redirect()->route('backend.kandidat.index')->with('success', __('message.update'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    // Hapus Data Kandidat
    public function delete($id)
    {
        try {
            $kandidat = Kandidat::findOrFail($id);

            // Hapus Foto dari Storage
            if ($kandidat->photo) {
                Storage::disk('public')->delete($kandidat->photo);
            }

            // Hapus Data dari Database
            $kandidat->delete();

            return redirect()->route('backend.kandidat.index')->with('success', __('message.delete'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }
}
