@extends('web::layouts.grids.12')

@section('title', trans('seat-capitals::capitals.title'))
@section('page_header', trans('seat-capitals::capitals.title'))
@section('page_description', trans('seat-capitals::capitals.menu_applications'))

@section('full')

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ trans('seat-capitals::capitals.apply_heading') }}</h3>
    </div>
    <div class="card-body">
      <p class="text-muted">{{ trans('seat-capitals::capitals.apply_intro') }}</p>

      <form method="post" action="{{ route('seat-capitals::applications.store') }}" id="capitals-apply-form">
        {!! csrf_field() !!}

        <div class="form-group row">
          <label for="capitals-character" class="col-sm-2 col-form-label">{{ trans('seat-capitals::capitals.field_character') }}</label>
          <div class="col-sm-10">
            <select name="character_id" id="capitals-character" class="form-control" required>
              @foreach($characters as $character)
                <option value="{{ $character->character_id }}" @if(old('character_id') == $character->character_id) selected @endif>
                  {{ $character->name }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group row">
          <label for="capitals-type" class="col-sm-2 col-form-label">{{ trans('seat-capitals::capitals.field_hull') }}</label>
          <div class="col-sm-10">
            <select name="type_id" id="capitals-type" class="form-control" required>
              @foreach($types->groupBy(fn($type) => $type->group->groupName ?? '') as $group_name => $group_types)
                <optgroup label="{{ $group_name }}">
                  @foreach($group_types as $type)
                    <option value="{{ $type->typeID }}" @if(old('type_id') == $type->typeID) selected @endif>
                      {{ $type->typeName }}
                    </option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group row">
          <label for="capitals-justification" class="col-sm-2 col-form-label">{{ trans('seat-capitals::capitals.field_justification') }}</label>
          <div class="col-sm-10">
            <textarea name="justification" id="capitals-justification" class="form-control" rows="4" maxlength="2000" required>{{ old('justification') }}</textarea>
            <small class="form-text text-muted">{{ trans('seat-capitals::capitals.field_justification_help') }}</small>
          </div>
        </div>

        <div class="form-group row mb-0">
          <div class="col-sm-10 offset-sm-2">
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> {{ trans('seat-capitals::capitals.button_submit') }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ trans('seat-capitals::capitals.my_applications') }}</h3>
    </div>
    <div class="card-body">
      {{ $dataTable->table() }}
    </div>
  </div>

@endsection

@push('javascript')
  {{ $dataTable->scripts() }}
@endpush
