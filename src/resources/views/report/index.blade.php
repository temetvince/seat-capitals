@extends('web::layouts.grids.12')

@section('title', trans('seat-capitals::capitals.title'))
@section('page_header', trans('seat-capitals::capitals.title'))
@section('page_description', trans('seat-capitals::capitals.menu_report'))

@section('full')

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ trans('seat-capitals::capitals.report_filters') }}</h3>
    </div>
    <div class="card-body">
      <div class="form-row align-items-end">
        <div class="form-group col-md-7">
          <label for="capitals-systems">{{ trans('seat-capitals::capitals.filter_systems') }}</label>
          <select id="capitals-systems" class="form-control" multiple="multiple">
            @foreach($home_systems as $system)
              <option value="{{ $system->system_id }}" selected="selected">{{ $system->name }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted">{{ trans('seat-capitals::capitals.filter_systems_help') }}</small>
        </div>
        <div class="form-group col-md-3">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="capitals-include-alts" />
            <label class="custom-control-label" for="capitals-include-alts">{{ trans('seat-capitals::capitals.filter_include_alts') }}</label>
          </div>
          <small class="form-text text-muted">{{ trans('seat-capitals::capitals.filter_include_alts_help') }}</small>
        </div>
        <div class="form-group col-md-2">
          <button type="button" class="btn btn-primary btn-block" id="capitals-report-refresh">
            <i class="fas fa-sync"></i> {{ trans('seat-capitals::capitals.button_refresh') }}
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ trans('seat-capitals::capitals.report_heading') }}</h3>
    </div>
    <div class="card-body">
      {{ $dataTable->table() }}
    </div>
  </div>

@endsection

@push('javascript')
  {{ $dataTable->scripts() }}
  <script>
    $('#capitals-systems').select2({
      allowClear: true,
      placeholder: @json(trans('seat-capitals::capitals.filter_systems_placeholder')),
      ajax: {
        url: '{{ route('seatcore::fastlookup.systems') }}',
        dataType: 'json',
        data: function (params) {
          return {
            term: params.term,
            q: params.term,
            _type: params._type
          };
        }
      }
    });

    $('#capitals-report-refresh').on('click', function () {
      $('#capitals-report').DataTable().draw();
    });

    $('#capitals-include-alts').on('change', function () {
      $('#capitals-report').DataTable().draw();
    });
  </script>
@endpush
