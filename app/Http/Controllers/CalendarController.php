<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;

class CalendarController extends Controller
{
    public function index(){
        $events = array();
        $bookings = Booking::all();
        foreach($bookings as $booking){
            $color = null;
            if($booking->title == 'Test'){
                $color = 'red';
            }

            $events[] = [
                'id'=>$booking->id,
                'title'=> $booking->title,
                'start'=> $booking->start_date,
                'end'=> $booking->end_date,
                //'color'=> $color
                'color'=> 'grey',
                'textColor'=> '#FFF',
                'borderColor'=> '#FFF',
            ];
        }
        return view('calendar.index',['events' => $events]);
    }

    public function store(Request $request){
        $request->validate([
            'title' => 'required|string'
        ]); 
        $booking = Booking::create([
            'title'=>$request->title,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
        ]);

        $color = null;
        if($booking->title == 'ICONEDEV'){
            $color = 'yellow';
        }

        return response()->json([
            'id'=>$booking->id,
            'title'=>$booking->title,
            'start_date'=>$booking->start_date,
            'end_date'=>$booking->end_date,
            'color'=> $color ? $color: '',
        ]);
    }

    public function update(Request $request, $id){
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d H:i',
            'end_date' => 'required|date_format:Y-m-d H:i',
        ]);

        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json([
                'error' => 'Unable to locate the booking'
            ], 404);
        }

        $booking->update([
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        return response()->json('Booking is updated successfully');
    }

    public function destroy($id)
    {
        // Find the booking by its ID
        $booking = Booking::find($id);

        // Check if the booking exists
        if (!$booking) {
            return response()->json([
                'error' => 'Unable to locate the booking'
            ], 404);
        }

        // Delete the booking
        $booking->delete();

        // Return a success response
        return response()->json([
            'message' => 'Booking deleted successfully',
            'booking_id' => $id
        ], 200);
    }

}
