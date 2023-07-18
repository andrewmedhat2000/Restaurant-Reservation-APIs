<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\WaitingList;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function create_reservation(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'table_id' => 'required|exists:tables,id',
            'customer_id' => 'required|exists:customers,id',
            'num_of_guests' => 'required',
            'from_time' => 'required|date',
            'to_time' => 'required|date',
        ]);
        $data = $request->all();
        // Create a new reservation
        $capacity=Table::find($request->table_id)->value('capacity');
        if($request->num_of_guests>$capacity){
          $WaitingList= WaitingList::create($data);
          return response()->json(['status' => 'error', 'message' =>"number of guests is greater than table capacity you will be added to the waiting list" ]);
        }
        else{
        $reservation = Reservation::create($data);
        return response()->json(['status' => 'success', 'reservation_details' =>$reservation ]);
        }
    }








public function checkAvailability(Request $request)
    {
        $this->validate($request, [
            'table_id' => 'required|exists:tables,id',
            'num_of_guests' => 'required',
            'from_time' => 'required',
            'to_time' => 'required',
        ]);

        $fromTime = $request->input('from_time');
        $toTime = $request->input('to_time');
        $numOfGuests = $request->input('num_of_guests');
        $tableId = $request->input('table_id');

        // Parse the date and time inputs
        $startDateTime = Carbon::parse($fromTime);
        $endDateTime = Carbon::parse($toTime);

        //$reservation  = Reservation::where('table_id', $tableId)->Where('from_time','<=',$startDateTime)->Where('to_time','>=',$endDateTime)->orWhere('to_time','<',$startDateTime)->orWhere('from_time','<',$endDateTime)->get();
        $rangeCount = Reservation::where('table_id', $tableId)->where(function ($query) use ($fromTime, $toTime) {
            $query->where(function ($query) use ($fromTime, $toTime) {
                $query->where('from_time', '<=', $fromTime)
                    ->where('to_time', '>=', $fromTime);
            })->orWhere(function ($query) use ($fromTime, $toTime) {
                $query->where('from_time', '<=', $toTime)
                    ->where('to_time', '>=', $toTime);
            })->orWhere(function ($query) use ($fromTime, $toTime) {
                $query->where('from_time', '>=', $fromTime)
                    ->where('to_time', '<=', $toTime);
            });
        })->count();
        if($rangeCount>0){
            return response()->json(['status' => 'error', 'message' => 'Table not available for the requested date and time range']);
        }
        else{
            $table=Table::find($tableId);
            if($table->capacity<$numOfGuests){
                return response()->json(['status' => 'error', 'message' => 'Table not available for the requested number of guests']);
            }
            else{
                return response()->json(['status' => 'success', 'message' => 'Table available for the requested date and time range and number of guests.']);
            }
        }

    }




    public function menu_list(){
        $meals = Meal::all();

        $response = [];
        foreach ($meals as $meal) {

            $response[] = [
                'description' => $meal->description,
                'price' => $meal->price,
                'quantity_available' => $meal->quantity_available,
                'discount' => $meal->discount,
            ];
        }

        return response()->json($response);
    }





    public function create_order(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'table_id' => 'required|exists:tables,id',
            'customer_id' => 'required|exists:customers,id',
            'reservation_id' => 'required|exists:reservations,id',
            'waiter_id'=>'required',
            'date' => 'required|date',
        ]);
        $total=0;
        $paid=0;
        $data=[];
        $data['table_id']=$request->table_id;
        $data['customer_id']=$request->customer_id;
        $data['reservation_id']=$request->reservation_id;
        $data['waiter_id']=$request->waiter_id;
        $data['date']=$request->date;

        foreach($request->meal_id as $meal_id)
        {

          $meal=DB::table('meals')->where('id',$meal_id)->first();

          $total+=$meal->price;
          $paid+=($meal->price-$meal->discount);
          $update_meal_quantity = Meal::find($meal_id);
          $update_meal_quantity->update([
           'quantity_available' => $meal->quantity_available-1,
             ]);
        }

        $data['total']=$total;
        $data['paid']=$paid;
        $order = Order::create($data);
        $order_details_data=[];
        foreach($request->meal_id as $meal_id){
        $order_details_data['order_id']=$order->id;
        $order_details_data['meal_id']=$meal_id;
        $order_details_data['amount_to_pay']=Meal::find($meal_id)->value('price');
        $order_details = OrderDetail::create($order_details_data);
        }
        $order_details = OrderDetail::get();

        return response()->json(['status' => 'success', 'order' =>$order , 'order_details' =>$order_details ]);
    }


    public function checkout(){

        $orders = Order::with('orderDetails')->get();
        $data=[];
        $all_data=[];
        $count=0;
        foreach ($orders as $order) {
            $data['Order_ID']=$order->id;
            $data['Order_Date']=$order->date;
            $data['Order_Total']=$order->total;
            $data['Order_Paid']=$order->paid;
            $all_order_details=[];
            foreach ($order->orderDetails as $detail) {
                if($detail){
                $det=[];
                $det['order_id']=$detail->order_id;
                $det['meal_id']=$detail->meal_id;
                $meal_details=DB::table('meals')->where('id',$detail->meal_id)->first();
                $det['meal_description']=$meal_details->description;
                $det['meal_price']=$meal_details->price;
                $det['meal_discount']=$meal_details->discount;
                $det['amount_to_pay']=$meal_details->price-$meal_details->discount;
                array_push($all_order_details, $det);
                }

            }

            $data['order_details']=$all_order_details;
            array_push($all_data, $data);

        }
        return response()->json(['status' => 'success', 'checkout' =>$all_data]);


    }
}
