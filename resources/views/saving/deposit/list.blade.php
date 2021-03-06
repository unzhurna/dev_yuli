@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('vendors/datatables/css/jquery.dataTables.min.css') }}">
@endsection

@section('title', 'Rincian Simpanan Anggota')

@section('content')
<a data-toggle="modal" href="#formModal" class="btn btn-float btn-danger m-btn"><i class="zmdi zmdi-plus"></i></a>
<div class="row">
    <div class="col-md-3">
        <div class="card profile-view">
            <div class="pv-header">
                @if($saving->member->avatar)
                    <img src="{{ asset('media/user/'.$$saving->member->avatar) }}" class="pv-main" alt="{{ $saving->member->nama }}" />
                @else
                    <img src="{{ asset('media/user/no-user-image.png') }}" class="pv-main" alt="{{ $saving->member->nama }}" />
                @endif
            </div>
            <div class="pv-body">
                <h2>{{ $saving->member->nama }}</h2>
                <h4>{{ $saving->no_simpanan }}</h4>
                <small>{{ $saving->member->alamat.', '.$saving->member->kota }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="table-responsive">
            <table id="data-table" class="table table-striped">
                <thead>
                    <tr>
                        <th rowspan="2">Tanggal</th>
                        <th colspan="3" class="text-center">Simpanan</th>
                        <th rowspan="2">Total</th>
                    </tr>
                    <tr>
                        <th>Pokok</th>
                        <th>Wajib</th>
                        <th>Sukarela</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align:right">Total:</th>
                        <th colspan="2" style="text-align:right"></th>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach ($saving->deposit as $deposit)
                        <tr>
                            <td>{{ $deposit->created_at->format('d/m/Y') }}</td>
                            <td>{{ number_format($deposit->sim_pokok, 0, ',', '.') }}</td>
                            <td>{{ number_format($deposit->sim_wajib, 0, ',', '.') }}</td>
                            <td>{{ number_format($deposit->sim_sukarela, 0, ',', '.') }}</td>
                            <td>{{ number_format($deposit->sim_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Form Simpanan</h4>
            </div>
            <div class="modal-body">
                <form id="FormSetoran" action="{{ route('deposit') }}">
                    {{ csrf_field() }}
                    <div class="form-group fg-line">
                        <label>Simpanan Pokok</label>
                        <input type="text" class="hidden" name="id_simpanan" value="{{ $saving->id }}">
                        <input type="text" class="form-control simpanan" name="sim_pokok" data-validation="number">
                    </div>
                    <div class="form-group fg-line">
                        <label>Simpanan Wajib</label>
                        <input type="text" class="form-control simpanan" name="sim_wajib" data-validation="number">
                    </div>
                    <div class="form-group fg-line">
                        <label>Simpanan Sukarela</label>
                        <input type="text" class="form-control simpanan" name="sim_sukarela" data-validation="number">
                    </div>
                    <div class="form-group fg-line">
                        <label>Simpanan Total</label>
                        <input type="text" class="form-control" id="sim_total" name="sim_total" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link" id="submit">Simpan</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">Batal</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
    <script src="{{ asset('vendors/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('vendors/form-validator/jquery.form-validator.min.js') }}"></script>
    <script>
        $.validate();

        $(document).ready(function() {
            $('#data-table').DataTable( {
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,.]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over all pages
                    total = api
                        .column( 4 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    // Total over this page
                    pageTotal = api
                        .column( 4, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );

                    // Update footer
                    $( api.column( 4 ).footer() ).html(
                        pageTotal +' ( '+ total +' total)'
                    );
                }
            } );
        } );

        $(document).on('keyup', '.simpanan', function() {
            SimTotal = 0;
            $('.simpanan').each(function() {
                SimTotal += Number($(this).val());
            });
            $('#sim_total').val(SimTotal);
        });

        $(document).on('click', '#submit', function() {

            dataForm = $('#FormSetoran').serialize();
            actionUrl = $('#FormSetoran').attr('action');

            $.ajax({
                url         : actionUrl,
                method      : 'POST',
                data        : dataForm,
                dataType    : 'json',
                success     : function(data) {
                    $('#formModal').modal('hide');
                    swal("Berhasil!", "Setoran tabungan telah berhasil dilakukan.", "success");
                    window.location.replace('{{ url()->current() }}');
                }
            });
        });

    </script>
@endsection
