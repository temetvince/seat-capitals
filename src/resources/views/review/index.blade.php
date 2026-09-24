@extends('web::layouts.grids.12')

@section('title', trans('seat-capitals::capitals.title'))
@section('page_header', trans('seat-capitals::capitals.title'))
@section('page_description', trans('seat-capitals::capitals.menu_review'))

@section('full')

  <div class="card">
    <div class="card-header">
      <h3 class="card-title">{{ trans('seat-capitals::capitals.review_heading') }}</h3>
    </div>
    <div class="card-body">
      <p class="text-muted">{{ trans('seat-capitals::capitals.review_intro') }}</p>
      {{ $dataTable->table() }}
    </div>
  </div>

  <div class="modal fade" id="capitals-decision-modal" tabindex="-1" role="dialog" aria-labelledby="capitals-decision-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="post" action="" id="capitals-decision-form">
          {!! csrf_field() !!}
          <input type="hidden" name="decision" id="capitals-decision-value" value="" />
          <div class="modal-header">
            <h5 class="modal-title" id="capitals-decision-title"></h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="capitals-decision-note">{{ trans('seat-capitals::capitals.field_note') }}</label>
              <textarea name="note" id="capitals-decision-note" class="form-control" rows="4" maxlength="2000"></textarea>
              <small class="form-text text-muted">{{ trans('seat-capitals::capitals.field_note_help') }}</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('seat-capitals::capitals.button_cancel') }}</button>
            <button type="submit" class="btn btn-primary" id="capitals-decision-submit"></button>
          </div>
        </form>
      </div>
    </div>
  </div>

@endsection

@push('javascript')
  {{ $dataTable->scripts() }}
  <script>
    $(document).on('click', '.capitals-decide', function () {
      var button = $(this);
      var approve = button.data('decision') === 'approved';

      $('#capitals-decision-form').attr('action', button.data('url'));
      $('#capitals-decision-value').val(button.data('decision'));
      $('#capitals-decision-note').val('');
      $('#capitals-decision-title').text(approve
        ? @json(trans('seat-capitals::capitals.approve_title'))
        : @json(trans('seat-capitals::capitals.deny_title')));
      $('#capitals-decision-submit')
        .text(approve
          ? @json(trans('seat-capitals::capitals.button_approve'))
          : @json(trans('seat-capitals::capitals.button_deny')))
        .toggleClass('btn-success', approve)
        .toggleClass('btn-danger', ! approve)
        .removeClass('btn-primary');

      $('#capitals-decision-modal').modal('show');
    });
  </script>
@endpush
