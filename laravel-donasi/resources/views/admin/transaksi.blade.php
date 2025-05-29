@extends('layouts.master')

@section('style')
    <link rel="stylesheet" href="/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="/assets/css/pages/datatables.css" />
@endsection

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Data Transaksi Donasi</h3>
            </div>
        </div>
    </div>

    <!-- Basic Tables start -->
    <section class="section">
        <div class="card">
            <div class="card-header">Data Transaksi Donasi</div>
            <div class="card-body">
                <table class="table" id="table1">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Donatur</th>
                            <th>Nama Campaign</th>
                            <th>Tanggal Donasi</th>
                            <th>Nominal Donasi</th>
                            <th>Keterangan</th>
                            <th>Bukti Transfer</th>
                            <th>Status Verifikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 0 @endphp
                        @foreach ($transaksi as $item)
                            <tr>
                                @php $i++ @endphp
                                <td>{{ $i }}</td>
                                <td>{{ $item->user->name }}</td>
                                <td>{{ $item->campaign->judul_campaign }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tgl_transaksi)->format('d-m-Y') }}</td>
                                <td>Rp{{ number_format($item->nominal_transaksi, 0, ',', '.') }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>
                                    @if ($item->bukti_transfer)
                                        <a href="{{ asset('storage/' . $item->bukti_transfer) }}" target="_blank" class="btn btn-sm btn-outline-primary">Lihat Bukti</a>
                                    @else
                                        <span class="text-muted">Belum Ada</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->status_verifikasi === 'terverifikasi')
                                        <span class="badge bg-success">Terverifikasi</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($item->status_verifikasi === 'pending')
                                        <form method="POST" action="{{ route('transaksi.verifikasi', $item->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success">Verifikasi</button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>Sudah Diverifikasi</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    <!-- Basic Tables end -->
</div>
@endsection

@section('script')
    <script src="/assets/extensions/jquery/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.js"></script>
    <script src="/assets/js/pages/datatables.js"></script>
@endsection
