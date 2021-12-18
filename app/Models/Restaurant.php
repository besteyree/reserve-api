<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['active_reservation', 'waiting', 'seated', 'checkout', 'reservation_graph', 'not_appear', 'walkin', 'reservation_count'];

    public function tableType()
    {
        return $this->hasMany(TableType::class);
    }

    public function floor()
    {
        return $this->hasMany(Floor::class);
    }

    public function location()
    {
        return $this->morphTo(Location::class, 'id');
    }

    public function getActiveReservationAttribute()
    {
        return FilledReservation::whereIn('status', ['0', '2', '3'])
        ->whereDate('date', Carbon::now())
        ->count();
    }

    public function getWaitingAttribute()
    {
        return FilledReservation::where('status', 1)
        ->whereDate('date', Carbon::now())
        ->count();
    }


    public function getSeatedAttribute()
    {
        return FilledReservation::where('status', 4)
        ->whereDate('date', Carbon::now())
        ->count();
    }


    public function getCheckoutAttribute()
    {
        return FilledReservation::where('status', 5)
        ->count();
    }

    public function getWalkinAttribute()
    {
        $reservation = FilledReservation::where('is_walkin', '=', null)
        ->count();

        $walkin = FilledReservation::where('is_walkin', '=', 1)
        ->count();

        return [
            'reservation' => $reservation,
            'walkin' => $walkin
        ];
    }

    public function getNotAppearAttribute()
    {
        $not = FilledReservation::whereIn('status', [0, 2])
        ->whereDate('date', '<', Carbon::now()->format('Y-m-d'))
        ->count();

        $appear = FilledReservation::where('status', 5)
        ->whereDate('date', '<', Carbon::now()->format('Y-m-d'))
        ->count();

        return [
            'not' => $not,
            'yes' => $appear
        ];
    }

    public function getReservationGraphAttribute()
    {
        $endOfMonth = Carbon::now()->endOfMonth()->format('d');
        $dataDay = collect();
        $dataMonth = collect();
        $dataWeek = collect();

        for($i=1; $i <= $endOfMonth ; $i++){
            $total = FilledReservation::where('status', 5)
            ->whereDay('date', $i)
            ->count();
            $dataDay->push([
                'day' =>  Carbon::now()->format('M') .'-'. $i,
                'total' => $total
            ]);
        }

        $month = array(
            1 => "Jan",
            2 => "Feb",
            3 => "Mar",
            4 => "Apr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Aug",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dec"
        );

        for($i=1; $i <= 12 ; $i++){
            $total = FilledReservation::where('status', 5)
            ->whereMonth('date', $i)
            ->count();
            $dataMonth->push([
                'day' =>  $month[$i],
                'total' => $total
            ]);
        }

        $week = array(
            1 => "Sun",
            2 => "Mon",
            3 => "Tue",
            4 => "Wed",
            5 => "Thu",
            6 => "Fri",
            7 => "Sat",
        );

        for($i=1; $i <= 7 ; $i++){
            $total = FilledReservation::where('status', 5)
            ->whereBetween('date', [Carbon::now()->startOfWeek()->format('Y-m-d') , Carbon::now()->endOfWeek()->format('Y-m-d')])
            ->get()
            ->map(function($row) {
                $row->day = date('D', strtotime($row->date));
                return $row;
            })->where('day', $week[$i])->count();

            $dataWeek->push([
                'day' =>  $week[$i],
                'total' => $total
            ]);
        }

        return [
            'month' => $dataDay,
            'year' => $dataMonth,
            'week' => $dataWeek
        ];
    }

    public function getReservationCountAttribute()
    {
        $reservation = FilledReservation::get();

        $restaurants['one'] =  $reservation->where('status', 1)->count();
        $restaurants['two'] =  $reservation->where('status', 1)
            ->where('no_of_occupancy', '>', '0')
            ->where('no_of_occupancy', '<', '4')->count();
        $restaurants['three'] =  $reservation->where('status', 1)
            ->where('no_of_occupancy', '>', '3')
            ->where('no_of_occupancy', '<', '6')->count();
        $restaurants['four'] = $reservation->where('status', 1)
            ->where('no_of_occupancy', '>', '5')
            ->where('no_of_occupancy', '<', '9')
            ->count();
        $restaurants['five'] =  $reservation->where('status', 1)
            ->where('no_of_occupancy', '>', '8')
            ->count();

        return $restaurants;
    }
}
