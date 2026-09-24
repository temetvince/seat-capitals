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
      <div class="row align-items-end">
        <div class="form-group col-md-6">
          <label for="capitals-systems">{{ trans('seat-capitals::capitals.filter_systems') }}</label>
          <select id="capitals-systems" class="form-control" multiple="multiple">
            @foreach($home_systems as $system)
              <option value="{{ $system->system_id }}" selected="selected">{{ $system->name }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted">{{ trans('seat-capitals::capitals.filter_systems_help') }}</small>
        </div>
        <div class="form-group col-md-4 pl-md-4">
          <label for="capitals-scope">{{ trans('seat-capitals::capitals.filter_scope') }}</label>
          <select id="capitals-scope" class="form-control">
            @foreach(\temetvince\SeatCapitals\Models\ReportScope::cases() as $scope)
              @if(! $scope->isUnrestricted() || $can_report_all)
                <option value="{{ $scope->value }}" @if($scope === $default_scope) selected="selected" @endif>
                  {{ trans($scope->labelKey()) }}
                </option>
              @endif
            @endforeach
          </select>
          <small class="form-text text-muted">{{ trans('seat-capitals::capitals.filter_scope_help') }}</small>
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

    $('#capitals-scope').on('change', function () {
      $('#capitals-report').DataTable().draw();
    });
  </script>
@endpush
