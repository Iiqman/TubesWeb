<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Campaign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    public function transaksi()
    {
        $transaksi = Transaksi::with('user')->get();
        return view('admin.transaksi', [
            'title' => 'Transaksi - We Care',
            'transaksi' => $transaksi,
        ]);
    }

    public function mydonation()
    {
        $transaksi = Transaksi::with('campaign')
            ->where('user_id', Auth::user()->id)
            ->get();

        return view('landing.mydonasi', [
            'transaksi'  => $transaksi,
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'nominal' => 'required|numeric',
            'pesan' => 'required',
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $nominal = (int) str_replace(['Rp', '.', ','], '', $request->input('nominal'));

        $buktiPath = $request->file('bukti_transfer')->store('bukti-transfer', 'public');

        Transaksi::create([
            'user_id' => $request->user_id,
            'campaign_id' => $request->campaign_id,
            'nominal_transaksi' => $nominal,
            'nama' => $request->nama,
            'tgl_transaksi' => Carbon::now(),
            'keterangan' => $request->pesan,
            'status_transaksi' => 0, 
            'bukti_transfer' => $buktiPath,
            'status_verifikasi' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Donasi berhasil dikirim. Menunggu verifikasi admin.');
    }

    public function verifikasi($id)
    {
        $transaksi = Transaksi::findOrFail($id);

        if ($transaksi->status_verifikasi !== 'terverifikasi') {
            $transaksi->status_verifikasi = 'terverifikasi';
            $transaksi->save();

            $campaign = $transaksi->campaign;
            $campaign->dana_terkumpul += $transaksi->nominal_transaksi;
            $campaign->save();
        }

        return redirect()->back()->with('success', 'Transaksi berhasil diverifikasi.');
    }
}
