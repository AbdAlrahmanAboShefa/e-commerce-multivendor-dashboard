<?php

namespace App\Http\Controllers;
use App\Models\SelerRequest;
use Illuminate\Http\Request;

class selerrequestcontroller extends Controller
{
    public function index()
    {
        $selerRequests = SelerRequest::with('user')->get();
        return response()->json(['seler_requests' => $selerRequests]);
    }
    public function store(Request $request)
    {
        if (SelerRequest::where('user_id', $request->input('user_id'))->exists()) {
            return response()->json(['message' => 'Seler request already submitted.'], 400);
        } if (auth()->user()->hasRole('seller')) {
            return response()->json(['message' => 'User is already a seller.'], 400);
        } if (auth()->user()->hasRole('admin')) {
            return response()->json(['message' => 'Admin cannot submit a seller request.'], 400);
        } if (SelerRequest::where('user_id', auth()->user()->id)->where('status', 'pending')->exists()) {
            return response()->json(['message' => 'There is already a pending seller request for this user.'], 400);
        }
        

        $selerRequest = SelerRequest::create([
            'user_id' => auth()->user()->id,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Seler request submitted successfully.', 'seler_request' => $selerRequest], 201);
    }
    public function approve($id)
    {
        $selerRequest = SelerRequest::findOrFail($id);
        $selerRequest->status = 'approved';
        $selerRequest->save();
        $selerRequest->user->removeRole('user');
        $selerRequest->user->assignRole('seller');
        return response()->json(['message' => 'Seler request approved.', 'seler_request' => $selerRequest]);
    }
    public function reject($id)
    {
        $selerRequest = SelerRequest::findOrFail($id);
        $selerRequest->status = 'rejected';
        $selerRequest->save();
        $selerRequest->delete();
        return response()->json(['message' => 'Seler request rejected.', 'seler_request' => $selerRequest]);
    }
}
