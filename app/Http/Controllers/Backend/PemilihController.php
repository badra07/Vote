<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PemilihDatatable;
use App\Mail\SendInfoLogin;
use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Repositories\BaseRepository;
use App\Services\PemilihService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PemilihController extends Controller
{
    protected $pemilih, $kelas;

    public function __construct(User $pemilih, Kelas $kelas)
    {
        $this->pemilih = new BaseRepository($pemilih);
        $this->kelas = new BaseRepository($kelas);
    }

    public function index(PemilihDatatable $datatable)
    {
        try {
            return $datatable->render('backend.pemilih.index');
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    public function create()
    {
        try {
            $data['kelas'] = $this->kelas->get();
            return view('backend.pemilih.create', compact('data'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    public function store(Request $request, PemilihService $pemilihService)
    {
        try {
            $request->validate([
                'nim' => 'required|string|unique:users,nim',
                'email' => 'required|email|unique:users,email',
                'kelas_id' => 'required|integer|exists:kelas,id',
                'name' => 'required|string|max:255',
                'token' => 'nullable|string',
            ]);

            $data = $request->all();
            $pemilihService->store($data);

            return redirect()->route('backend.pemilih.index')->with('success', __('message.store'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    public function edit($id)
    {
        try {
            $data['kelas'] = $this->kelas->get();
            $data['pemilih'] = $this->pemilih->find($id);
            return view('backend.pemilih.edit', compact('data'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'nim' => 'required|string',
                'email' => 'required|email|unique:users,email,' . $id,
                'kelas_id' => 'required|integer|exists:kelas,id',
                'name' => 'required|string|max:255',
                'token' => 'nullable|string',
            ]);

            $data = $request->all();
            $this->pemilih->update($id, $data);

            return redirect()->route('backend.pemilih.index')->with('success', __('message.update'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    public function delete($id)
    {
        try {
            $this->pemilih->delete($id);
            return redirect()->route('backend.pemilih.index')->with('success', __('message.delete'));
        } catch (\Throwable $th) {
            return view('error.index', ['message' => $th->getMessage()]);
        }
    }

    public function sendLoginInfo($id)
    {
        
    // Ambil data pemilih berdasarkan id
    $pemilih = User::find($id);

    // Cek apakah pemilih ditemukan
    if (!$pemilih) {
        return redirect()->route('backend.pemilih.index')->with('error', 'Pemilih tidak ditemukan.');
    }

    // Cek jika NIM atau Token kosong
    if (empty($pemilih->nim) || empty($pemilih->token)) {
        return redirect()->route('backend.pemilih.index')->with('error', 'NIM atau Token tidak tersedia untuk pemilih ini.');
    }

    // Kirim email dengan data yang ditemukan
    Mail::to($pemilih->email)->send(new SendInfoLogin($pemilih->name, $pemilih->nim, $pemilih->token));

    // Redirect kembali setelah email terkirim
    return redirect()->route('backend.pemilih.index')->with('success', 'Informasi login berhasil dikirim.');

    }   

    
}
