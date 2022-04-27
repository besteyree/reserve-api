<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
Use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\Gmail;


class everyDay extends Command 
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'day:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reservation report mail';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       // yesterday Date (formate : Y.M.D)
       $date= date('Y-m-d',strtotime("-1 days"));
  
       //reservation 
       $data =  DB:: table('filled_reservations')
               ->where("date","=", $date)
               ->get();

       //count reservation        
       $reservation = $data->count();        
       

       //reservation seated
       $data2 = DB::table('filled_reservations')
                ->select(DB::raw('DATE(seated_date) AS seated_date '))
               //  ->where("seated_date","=", $date)
               ->whereNotNull('seated_date')
               ->get();

        //count reservation seated           
         $reservation_seated   =0;
             foreach ($data2  as $value) {
                 # code...
               if ($value->seated_date == $date ) {
                   
                   $reservation_seated++ ;
               }
             
                   $reservation_seated  = $reservation_seated;
             
                     # code...

             }      

    
             
       //is walkin
       $data3 = DB::table('filled_reservations')
               ->where('is_walkin','=', 1)
               ->where("date","=", $date)
               ->get();

       $is_walkin = $data3->count();        
               


       // reservation repeated
       $data3 = DB::table('filled_reservations') 
       ->select(DB::raw(' COUNT(phone) as repeated, phone'))
       ->where("date","=", $date)
       ->groupBy('phone')
       ->get();
         
       $reservation_repeated=0;
       $phone = "0000000";
     
       foreach ($data3 as $value) {
           # code...    
          
           if ($value->repeated > 1) {

               $str = strcmp($phone,$value->phone);

                   if ($str != 0 ) {
                       # code...
                       $reservation_repeated =  $reservation_repeated +1;
                       $phone =  $value->phone;
                   } 

                   $phone =  $value->phone;
           }     
           $reservation_repeated = $reservation_repeated;
                
       
       }


       //Total seated

       $total_seated = $is_walkin + $reservation_seated;

       $details = [
           'title' => 'Reservation Summary ',
           'reservation_seated' =>  '' .$reservation_seated.'',
           'is_walkin' => ''.$is_walkin.'',
           'reservation_repeated' => ''.$reservation_repeated.'',
           'total_seated' => ''.$total_seated.''
      
       ];

       
       Mail::to('automatez.team@gmail.com')->send(new Gmail($details));
   }

         
    
}
