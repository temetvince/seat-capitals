@php
  $badge = match ($status) {
    \temetvince\SeatCapitals\Models\ApplicationStatus::Pending => 'badge-warning',
    \temetvince\SeatCapitals\Models\ApplicationStatus::Approved => 'badge-success',
    \temetvince\SeatCapitals\Models\ApplicationStatus::Denied => 'badge-danger',
    \temetvince\SeatCapitals\Models\ApplicationStatus::Withdrawn => 'badge-secondary',
  };
@endphp
<span class="badge {{ $badge }}">{{ trans($status->labelKey()) }}</span>
