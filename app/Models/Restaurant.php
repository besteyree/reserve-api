<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Restaurant extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $appends = ['customer', 'floor', 'tabletype', 'source', 'cus_group', 'active_reservation', 'waiting', 'seated', 'checkout', 'reservation_graph', 'not_appear', 'walkin', 'reservation_count', 'sms'];

    public function tableType()
    {
        return $this->hasMany(TableType::class);
    }

    public function getFloorAttribute()
    {
        // $floor = $this->hasMany(Floor::class)->where('restaurant_id', $this->id);
        $floor  = Floor::where('restaurant_id', $this->id)->get();
        return [
            'floor' => $floor,
        ];
    }

    public function getTableTypeAttribute()
    {
        // $floor = $this->hasMany(Floor::class)->where('restaurant_id', $this->id);
        $tabletype  = TableType::where('restaurant_id', $this->id)->get();
        return [
            'tabletype' => $tabletype,
        ];
    }

    public function getSmsAttribute()
    {
        // $floor = $this->hasMany(Floor::class)->where('restaurant_id', $this->id);
        $sms  = Sms_Message::where('restaurant_id', $this->id)->count();
        return [
            'sms' => $sms,
        ];
    }

    public function location()
    {
        return $this->morphTo(Location::class, 'id');
    }



    public function getActiveReservationAttribute()
    {
        return FilledReservation::whereIn('status', ['0', '2', '3'])
        ->where('restaurant_id', $this->id)
        ->whereDate('date', Carbon::now())
        ->count();
    }

    public function getWaitingAttribute()
    {
        return FilledReservation::where('status', 1)
        ->where('restaurant_id', $this->id)
        ->whereDate('date', Carbon::now())
        ->count();
    }


    public function getSeatedAttribute()
    {
        return FilledReservation::where('status', 4)
        ->where('restaurant_id', $this->id)
        ->whereDate('date', Carbon::now())
        ->count();
    }


    public function getCheckoutAttribute()
    {
        return FilledReservation::where('status', 5)
        ->where('restaurant_id', $this->id)
        ->count();
    }

    public function getWalkinAttribute()
    {
        $reservation = FilledReservation::where('is_walkin', '=', null)
        ->where('restaurant_id', $this->id)
        ->sum('no_of_occupancy');

        $walkin = FilledReservation::where('is_walkin', '=', 1)
        ->where('restaurant_id', $this->id)
        ->count('no_of_occupancy');

        return [
            'reservation' => $reservation,
            'walkin' => $walkin
        ];
    }

    public function getCusGroupAttribute()
    {
        $kids = FilledReservation::where('restaurant_id', $this->id)->sum('kids');
        $adults = FilledReservation::where('restaurant_id', $this->id)->sum('adults');

        return [
            'kids' => $kids,
            'adults' => $adults,
        ];
    }

    public function getSourceAttribute()
    {
        $source = FilledReservation::where('restaurant_id', $this->id)->get()
        ->groupBy('source')
        ->map(function($row){
            return $row->count();
        });
        return $source;
    }

    public function getCustomerAttribute()
    {
        $customer = FilledReservation::where('restaurant_id', $this->id)->get()
        ->groupBy('visit')
        ->map(function($row){
            return $row->count();
        });

        return $customer;
    }

    public function getNotAppearAttribute()
    {
        $not = FilledReservation::whereIn('status', [0, 2])
        ->where('restaurant_id', $this->id)
        ->whereDate('date', '<', Carbon::now()->format('Y-m-d'))
        ->count();

        $appear = FilledReservation::where('status', 5)
        ->where('restaurant_id', $this->id)
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
            $total = FilledReservation::
            where('is_walkin', 1)
            ->where('restaurant_id', $this->id)
            ->whereDay('date', $i)
            ->count();
            $dataDay->push([
                'day' =>  Carbon::now()->format('M') .'-'. $i,
                'total' => $total,
                'category' => 'Walkin'
            ]);

            $totalRes = FilledReservation::
            where('is_walkin', null)
            ->where('restaurant_id', $this->id)
            ->whereDay('date', $i)
            ->count();
            $dataDay->push([
                'day' =>  Carbon::now()->format('M') .'-'. $i,
                'total' => $totalRes,
                'category' => 'Reservation'
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
            $total = FilledReservation::where('is_walkin', 1)
            ->where('restaurant_id', $this->id)
            ->whereMonth('date', $i)
            ->count();
            $dataMonth->push([
                'day' =>  $month[$i],
                'total' => $total,
                'category' => 'Walkin'
            ]);

            $totalRes = FilledReservation::
            where('is_walkin', null)
            ->where('restaurant_id', $this->id)
            ->whereMonth('date', $i)
            ->count();
            $dataMonth->push([
                'day' =>  Carbon::now()->format('M') .'-'. $i,
                'total' => $totalRes,
                'category' => 'Reservation'
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
            $total = FilledReservation::where('is_walkin', 1)
            ->where('restaurant_id', $this->id)
            ->whereBetween('date', [Carbon::now()->startOfWeek()->format('Y-m-d') , Carbon::now()->endOfWeek()->format('Y-m-d')])
            ->get()
            ->map(function($row) {
                $row->day = date('D', strtotime($row->date));
                return $row;
            })->where('day', $week[$i])->count();

            $dataWeek->push([
                'day' =>  $week[$i],
                'total' => $total,
                'category' => 'Walkin'
            ]);

            $totalRes = FilledReservation::where('is_walkin', null)
            ->where('restaurant_id', $this->id)
            ->whereBetween('date', [Carbon::now()->startOfWeek()->format('Y-m-d') , Carbon::now()->endOfWeek()->format('Y-m-d')])
            ->get()
            ->map(function($row) {
                $row->day = date('D', strtotime($row->date));
                return $row;
            })->where('day', $week[$i])->count();

            $dataWeek->push([
                'day' =>  $week[$i],
                'total' => $totalRes,
                'category' => 'Reservation'
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

        $restaurants['one'] = FilledReservation::where(function($row) {
            $row->where('status', 1);
            $row->orWhere('type', 2);
        })
        ->where('restaurant_id', $this->id)
        ->count();

        $restaurants['two'] =  FilledReservation::where(function($row) {
                $row->where('status', 1);
                $row->orWhere('type', 2);
            })
            ->where('restaurant_id', $this->id)
            ->where('no_of_occupancy', '>', '0')
            ->where('no_of_occupancy', '<', '4')->count();
        $restaurants['three'] = FilledReservation::where(function($row) {
                $row->where('status', 1);
                $row->orWhere('type', 2);
            })
            ->where('restaurant_id', $this->id)
            ->where('no_of_occupancy', '>', '3')
            ->where('no_of_occupancy', '<', '6')->count();

        $restaurants['four'] = FilledReservation::where(function($row) {
                $row->where('status', 1);
                $row->orWhere('type', 2);
            })
            ->where('restaurant_id', $this->id)
            ->where('no_of_occupancy', '>', '5')
            ->where('no_of_occupancy', '<', '9')
            ->count();

        $restaurants['five'] =  FilledReservation::where(function($row) {
                $row->where('status', 1);
                $row->orWhere('type', 2);
            })
            ->where('restaurant_id', $this->id)
            ->where('no_of_occupancy', '>', '8')
            ->count();

        return $restaurants;
    }
}
