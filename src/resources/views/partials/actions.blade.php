@if($application->isPending())
  @if($review)
    <div class="btn-group btn-group-sm" role="group">
      <button type="button" class="btn btn-success capitals-decide"
              data-decision="approved"
              data-url="{{ route('seat-capitals::review.decide', ['application' => $application->id]) }}">
        <i class="fas fa-check"></i> {{ trans('seat-capitals::capitals.button_approve') }}
      </button>
      <button type="button" class="btn btn-danger capitals-decide"
              data-decision="denied"
              data-url="{{ route('seat-capitals::review.decide', ['application' => $application->id]) }}">
        <i class="fas fa-times"></i> {{ trans('seat-capitals::capitals.button_deny') }}
      </button>
    </div>
  @else
    <form method="post" action="{{ route('seat-capitals::applications.withdraw', ['application' => $application->id]) }}" class="d-inline">
      {!! csrf_field() !!}
      <button type="submit" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-undo"></i> {{ trans('seat-capitals::capitals.button_withdraw') }}
      </button>
    </form>
  @endif
@endif
