<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Models\Berita;
use App\Models\Kategori;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index($slug)
    {
        $campaign = Campaign::where('slug_campaign', $slug)->first();
        $berita = Berita::where('campaign_id', $campaign->id)->latest()->get();
        $doa = Transaksi::with('user')->where('campaign_id', $campaign->id)->where('status_transaksi', 1)->limit(3)->latest()->get();
        if ($campaign && $campaign->status_campaign == 1) {
            return view('landing.campaign', compact('campaign', 'berita', 'doa'));
        }
        return view('errors.404');
    }

    public function berita($slug, $slugberita)
    {
        $campaign = Campaign::where('slug_campaign', $slug)->first();
        $berita = Berita::where('slug_berita', $slugberita)->get();
        if ($campaign && $campaign->status_campaign == 1) {
            return view('landing.beritacampaign', compact('campaign', 'berita'));
        }
        return view('errors.404');
    }

    public function campaign()
    {
        $campaign = Campaign::all();
        return view('admin.campaign', [
            'campaign' => $campaign,
            'title' => 'Data Campaign - We Care',
        ]);
    }

    public function lihatcampaign($id)
    {
        $campaign = Campaign::where('id', $id)->get();
        return view('admin.lihatcampaign', [
            'campaign' => $campaign,
            'title' => 'Data Campaign - We Care',
        ]);
    }

    public function mycampaign()
    {
        $campaign = Campaign::where('user_id', Auth::id())->get();
        return view('landing.mycampaign', compact('campaign'));
    }

    public function editstatuscampaign(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'status' => 'required',
        ]);

        Campaign::where('id', $request->id)->update([
            'status_campaign' => $request->status,
        ]);

        return redirect('/admin/campaign/campaign')->with('message', 'Status berhasil diedit');
    }

    public function uploadgambar(Request $request)
    {
        if ($request->hasFile('upload')) {
            $path = $request->file('upload')->store('images/berita', 'public');
            $url = asset('storage/' . $path);
            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'Gagal upload gambar'], 400);
    }

    public function posttambahberita(Request $request)
    {
        $request->validate([
            'judul' => 'required|string',
            'tgl_terbit' => 'date|required',
            'user_id' => 'required',
            'campaign_id' => 'required',
            'gambar' => 'image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=4096,max_height=4096',
            'isi' => 'required',
        ]);

        $path = $request->file('gambar')->store('images/berita', 'public');

        Berita::create([
            'judul_berita' => $request->judul,
            'slug_berita' => Str::slug($request->judul),
            'tgl_terbit_berita' => $request->tgl_terbit,
            'user_id' => $request->user_id,
            'campaign_id' => $request->campaign_id,
            'gambar_berita' => $path,
            'isi_berita' => $request->isi,
        ]);

        return redirect('/admin/campaign/berita')->with('message', 'Berita berhasil ditambahkan');
    }

    public function posteditberita(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'judul' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048|dimensions:max_width=2048,max_height=2048',
            'isi' => 'required',
        ]);

        $berita = Berita::findOrFail($request->id);

        $berita->judul_berita = $request->judul;
        $berita->isi_berita = $request->isi;

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('images/thumbnail', 'public');
            $berita->gambar_berita = $path;
        }

        $berita->save();

        return redirect('/admin/campaign/berita')->with('message', 'Berita berhasil diedit');
    }

    public function deleteberita()
    {
        Berita::where('id', request('id'))->delete();
        return back()->with('message', 'Berita berhasil dihapus');
    }

    public function kategori()
    {
        $kategori = Kategori::all();
        return view('admin.kategori', [
            'title' => 'Kategori - We Care',
            'kategori' => $kategori,
        ]);
    }

    public function tambahkategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|unique:kategori',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
        ]);

        return back()->with('message', 'Kategori berhasil ditambahkan');
    }

    public function deletekategori()
    {
        if (Campaign::where('category_id', request('id'))->exists()) {
            return back()->with('salah', 'Kategori tidak berhasil dihapus');
        }

        Kategori::where('id', request('id'))->delete();
        return back()->with('message', 'Kategori berhasil dihapus');
    }

    public function uploadgambarcampaign(Request $request)
    {
        if ($request->hasFile('upload')) {
            $path = $request->file('upload')->store('images/campaign', 'public');
            $url = asset('storage/' . $path);
            return response()->json(['url' => $url]);
        }

        return response()->json(['error' => 'Gagal upload gambar'], 400);
    }

    public function createcampaigndonatur(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required',
                'category_id' => 'required',
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'judul_campaign' => 'required|string',
                'deskripsi_campaign' => 'required|string',
                'tgl_akhir' => 'required|date',
                'target_campaign' => 'required|numeric',
            ]);

            $path = $request->file('image')->store('images/campaign', 'public');


            Campaign::create([
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'foto_campaign' => $path,
                'judul_campaign' => $request->judul_campaign,
                'deskripsi_campaign' => $request->deskripsi_campaign,
                'slug_campaign' => Str::slug($request->judul_campaign),
                'tgl_mulai_campaign' => Carbon::now(),
                'tgl_akhir_campaign' => $request->tgl_akhir,
                'target_campaign' => $request->target_campaign,
                'status_campaign' => 0,
            ]);

            return redirect('/')->with('message', 'Campaign berhasil dibuat!');
        }

        return view('/');
    }
}
