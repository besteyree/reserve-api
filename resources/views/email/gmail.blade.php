@component('mail::message')

 
# Reservation Summary
 
@component('mail::table')
|   Metric    | Count         |
| ------------- |:-------------:| 
| Repeat Customers |  {{ $details['reservation_repeated'] }}  |
| Reservations Seated    | {{ $details['reservation_seated'] }} |
| Walkins Seated  |{{ $details['is_walkin'] }} |
| Total Seated    | {{ $details['total_seated'] }} |



@endcomponent
 
Thanks,<br>

@endcomponent