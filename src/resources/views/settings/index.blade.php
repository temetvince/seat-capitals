@extends('web::layouts.grids.12')

@section('title', trans('seat-capitals::capitals.title'))
@section('page_header', trans('seat-capitals::capitals.title'))
@section('page_description', trans('seat-capitals::capitals.menu_settings'))

@section('full')

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ trans('seat-capitals::capitals.settings_heading') }}</h3>
    </div>
    <div class="card-body">
      <form method="post" action="{{ route('seat-capitals::settings.update') }}" id="capitals-settings-form">
        {!! csrf_field() !!}

        <div class="form-group">
          <label for="capitals-home-systems">{{ trans('seat-capitals::capitals.settings_home_systems') }}</label>
          <select name="systems[]" id="capitals-home-systems" class="form-control" multiple="multiple">
            @foreach($home_systems as $system)
              <option value="{{ $system->system_id }}" selected="selected">{{ $system->name }}</option>
            @endforeach
          </select>
          <small class="form-text text-muted">{{ trans('seat-capitals::capitals.settings_home_systems_help') }}</small>
        </div>

        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> {{ trans('seat-capitals::capitals.button_save') }}
        </button>
      </form>
    </div>
  </div>

@endsection

@push('javascript')
  <script>
    $('#capitals-home-systems').select2({
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
  </script>
@endpush
