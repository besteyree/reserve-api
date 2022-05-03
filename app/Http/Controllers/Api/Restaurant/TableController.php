<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest;
use App\Jobs\SendConfirmEmail;
use App\Models\FilledReservation;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;
use DB;

class TableController extends Controller
{
    public function index($id = null)
    {
        if ($id) {
            return Table::where('restaurant_id', $id)->paginate(6);
        }

        return Table::paginate(6);
    }

    public function storeUpdate(TableRequest $request, $id = null)
    {
        if ($id) {
            try {

                $input = $request->validate();
                $input['restaurant_id'] = auth()->user()->restaurant_id;
                $restaurant = Table::find($id)
                    ->update($input);

                return \Response::success($restaurant, 'Table Updated Successfully');
            } catch (\Exception $e) {
                \Response::failed($e, 'Table Updated failed');
            }
        }

        try {

            $input = $request->validate();
            $input['restaurant_id'] = auth()->user()->restaurant_id;
            $restaurant = Table::create($input);

            return \Response::success($restaurant, 'Restaurant Updated Successfully');
        } catch (\Exception $e) {
            \Response::failed($e, 'Restaurant Updated failed');
        }
    }

    public function destroy($id)
    {
        try {
            $restaurant =  Table::destroy($id);
            return \Response::success($restaurant, 'Table deleted Successfully');
        } catch (\Exception $e) {
            \Response::failed($e, 'Table delete failed');
        }
    }

    public function statusChange(Request $request)
    {
        try {

            foreach ($request->table as $table) {
                Table::find($table)
                    ->update([
                        'status' => $request->status,
                        'user_id' => $request->user_id,
                    ]);
            }

            $user = FilledReservation::find($request->user_id);

            if ($user->status === 0) {
                dispatch(new SendConfirmEmail($user));
            }

            $user->update([
                'status' => 4,
                'confirmed_date' => now(),
                'seated_date' => now()
            ]);

            return \Response::success(Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title', 'floor.table.user')->find(1), 'Table Reserved Successfully');
        } catch (\Exception $e) {
            \Response::failed($e, 'Table Reserved failed');
        }
    }

    public function checkout($id)
    {
        try {
            $tableId = Table::where('user_id', $id)->pluck('id');

            Table::whereIn('id', $tableId)->update([
                'status' => 0,
                'user_id' => null
            ]);

            FilledReservation::find($id)
                ->update([
                    'status' => 5
                ]);

            return \Response::success([
                'rmTable' => Table::whereIn('id', $tableId)->get(),
                'restaurant' => Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title', 'floor.table.user')->find(1)
            ], 'Checkout Success');
        } catch (\Exception $e) {
            return \Response::failed($e, 'Checkout failed');
        }
    }

    public function checkoutOne($id, $user_id)
    {

        try {
            $tableId = Table::find($id)->update([
                'status' => 0,
                'user_id' => null
            ]);

            if (!Table::where('user_id', $user_id)->exists()) {
                FilledReservation::find($user_id)
                    ->update([
                        'status' => 5
                    ]);
            }

            return \Response::success([
                'rmTable' => Table::where('id', $tableId)->get(),
                'restaurant' => Restaurant::with('floor:id,title,restaurant_id', 'floor.table:id,title,no_of_occupany,floor_id,type_id,status,user_id', 'floor.table.tableType:id,title', 'floor.table.user')->find(1)
            ], 'Checkout Success');
        } catch (\Exception $e) {
            return \Response::failed($e, 'Checkout failed');
        }
    }


    public function resttable(Request  $request, $id = null)
    {
        $title = $request->input('title');
        $no_of_table = $request->input('no_of_table');
        $no_of_occupany = $request->input('no_of_occupany');
        $type_id = $request->input('type_id');
        $floor_id = $request->input('floor_id');
        $restaurant_id = auth()->user()->restaurant_id;


        $i = 1;
        try {
            while ($i <= $no_of_table) {

                // $title append  $title and $i

                $table_title = $title . $i;
                DB::insert('insert into tables (title,no_of_occupany,type_id, floor_id,created_at,updated_at,deleted_at,restaurant_id,status,user_id) values(?,?,?,?,?,?,?,?,?,?)', [$table_title, $no_of_occupany, $type_id, $floor_id, Null, Null, Null, $restaurant_id, 0, Null]);
                $i = $i + 1;
            }

            return response()->json(['message' => 'Table Inserted Successfully ']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Failed']);
        }
    }


    //fetch and get details
    public function getTableDetailsById($limit)
    {
        # code...
        // if ($id) {
        //     return Table::find($id);
        // }

        $mylimit = explode(',', $limit);

        $restaurant_id = auth()->user()->restaurant_id;

        return $restaurant_tables = Table::select('*', 'id as AllId')->where('restaurant_id', $restaurant_id)
            ->whereBetween('id', $mylimit)
            ->get();

        // return  $restaurant_tables->paginate(6);
    }

    public function groupTable($id = null)
    {
        $grp = DB::select("
        SELECT
CONCAT(MIN(`id`), ',', MAX(`id`)) AS AllId,
CONCAT(MIN(`title`), '-', MAX(`title`)) AS title
,`no_of_occupany`
FROM
(
    SELECT `title`,
        @row_number:=CASE
            WHEN @id = `no_of_occupany` THEN @row_number
            ELSE @row_number + 1
        END AS num,
        @id:=`no_of_occupany` as no_of_occupany,
        id
    FROM tables, (SELECT @row_number:=0, @id := 1) t
    WHERE `restaurant_id` = '".auth()->user()->restaurant_id."' 
    ORDER BY id
) t

GROUP BY num
        ");

        return $grp;
    }
}


// DB::table("where_subquery_group_3_ t")
// ->select("min (id) as low", "max (id) as high", "@row_number", "@id")
// ->addSelect(DB::raw("case when @id = `no_of_occupany` then @row_number else @row_number + 1 end as num, @id: = `no_of_occupany`"))
// ->where("`restaurant_id`", "=", 1)
// ->orderBy("id",")","t","where","t.`no_of_occupany`","=","4","group","by")
// ->get();


// SELECT COUNT(*),
// CONCAT(MIN(`id`), ',', MAX(`id`)) AS AllId,
// CONCAT(MIN(`title`), '-', MAX(`title`)) AS title, `no_of_occupany` FROM tables
// WHERE `restaurant_id` = 1 GROUP BY `no_of_occupany`