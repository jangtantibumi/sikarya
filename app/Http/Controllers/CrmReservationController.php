<?php

namespace App\Http\Controllers;

use App\Models\CrmReservation;
use App\Models\CrmCustomer;
use App\Models\CrmCustomerTimeline;
use Illuminate\Http\Request;

class CrmReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = CrmReservation::with('customer')->orderBy('reservation_date', 'desc')->orderBy('reservation_time', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        $reservations = $query->paginate(15);
        
        return view('crm.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $customers = CrmCustomer::where('is_active', true)->get();
        return view('crm.reservations.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'pax' => 'required|integer|min:1',
            'table_preference' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
        ]);

        $reservation = CrmReservation::create($validated);

        CrmCustomerTimeline::create([
            'customer_id' => $reservation->customer_id,
            'action' => 'RESERVATION_CREATED',
            'description' => "Reservasi dibuat untuk tanggal {$reservation->reservation_date->format('d/m/Y')} {$reservation->reservation_time} ({$reservation->pax} Pax).",
            'reference_id' => $reservation->id,
        ]);

        return redirect()->route('crm.reservations.index')->with('success', 'Reservasi berhasil dibuat');
    }

    public function edit(CrmReservation $reservation)
    {
        $customers = CrmCustomer::where('is_active', true)->get();
        return view('crm.reservations.edit', compact('reservation', 'customers'));
    }

    public function update(Request $request, CrmReservation $reservation)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:crm_customers,id',
            'reservation_date' => 'required|date',
            'reservation_time' => 'required',
            'pax' => 'required|integer|min:1',
            'table_preference' => 'nullable|string|max:255',
            'special_requests' => 'nullable|string|max:1000',
            'status' => 'required|in:Pending,Confirmed,Cancelled,Completed',
        ]);

        $oldStatus = $reservation->status;
        $reservation->update($validated);

        if ($oldStatus !== $reservation->status) {
            CrmCustomerTimeline::create([
                'customer_id' => $reservation->customer_id,
                'action' => 'RESERVATION_UPDATED',
                'description' => "Status reservasi ({$reservation->reservation_date->format('d/m/Y')}) berubah dari {$oldStatus} menjadi {$reservation->status}.",
                'reference_id' => $reservation->id,
            ]);
        }

        return redirect()->route('crm.reservations.index')->with('success', 'Reservasi berhasil diperbarui');
    }

    public function destroy(CrmReservation $reservation)
    {
        $reservation->delete();
        return redirect()->route('crm.reservations.index')->with('success', 'Reservasi berhasil dihapus');
    }
}
