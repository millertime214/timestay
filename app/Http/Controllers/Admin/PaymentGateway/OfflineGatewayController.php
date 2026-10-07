<?php

namespace App\Http\Controllers\Admin\PaymentGateway;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentGateway\OfflineGateway;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Mews\Purifier\Facades\Purifier;

class OfflineGatewayController extends Controller

    {
  public function index()
  {
    $offlineGateways = OfflineGateway::orderBy('id', 'desc')->get();

    return view('admin.payment-gateways.offline-gateways.index', compact('offlineGateways'));
  }

  public function store(Request $request)
  {
    $rules = [
      'name' => 'required',
      'has_attachment' => 'required',
      'serial_number' => 'required|numeric',
      'ewallet_type' => 'nullable|required_with:account_name,account_number,qr_code|max:100',
      'account_name' => 'nullable|required_with:ewallet_type,account_number,qr_code|max:255',
      'account_number' => 'nullable|required_with:ewallet_type,account_name,qr_code|max:100',
      'qr_code' => 'nullable|required_with:ewallet_type,account_name,account_number|image|mimes:jpeg,jpg,png,webp|max:2048'
    ];

    $message = [
      'has_attachment.required' => 'The attachment field is required.'
    ];

    $validator = Validator::make($request->all(), $rules, $message);

    if ($validator->fails()) {
      return Response::json([
        'errors' => $validator->getMessageBag()->toArray()
      ], 400);
    }

    $data = $request->except(['instructions', 'qr_code']);
    if ($request->hasFile('qr_code')) {
      $data['qr_code'] = $this->storeQrCode($request);
    }

    OfflineGateway::create($data + [
      'instructions' => Purifier::clean($request->instructions, 'youtube')
    ]);

    Session::flash('success', __('New offline payment gateway added successfully') . '!');

    return Response::json(['status' => 'success'], 200);
  }

  public function updateStatus(Request $request, $id)
  {
    try {
      $offlineGateway = OfflineGateway::findOrFail($id);

      if ($request->status == 1) {
        $offlineGateway->update(['status' => 1]);
      } else {
        $offlineGateway->update(['status' => 0]);
      }

      Session::flash('success', __('Status updated successfully') . '!');
    } catch (ModelNotFoundException $e) {
      Session::flash('success', __('No record found in database') . '!');
    }

    return redirect()->back();
  }

  public function update(Request $request)
  {
    $rules = [
      'name' => 'required',
      'has_attachment' => 'required',
      'serial_number' => 'required|numeric',
      'ewallet_type' => 'nullable|required_with:account_name,account_number|max:100',
      'account_name' => 'nullable|required_with:ewallet_type,account_number|max:255',
      'account_number' => 'nullable|required_with:ewallet_type,account_name|max:100',
      'qr_code' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048'
    ];

    $message = [
      'has_attachment.required' => 'The attachment field is required.'
    ];

    $validator = Validator::make($request->all(), $rules, $message);

    if ($validator->fails()) {
      return Response::json([
        'errors' => $validator->getMessageBag()->toArray()
      ], 400);
    }

    try {
      $gateway = OfflineGateway::findOrFail($request->id);

      $data = $request->except(['instructions', 'qr_code']);
      if ($request->hasFile('qr_code')) {
        $oldQrCode = $gateway->qr_code;
        $data['qr_code'] = $this->storeQrCode($request);
        $this->deleteQrCode($oldQrCode);
      }

      $gateway->update($data + [
        'instructions' => Purifier::clean($request->instructions, 'youtube')
      ]);

      Session::flash('success', __('Offline payment gateway updated successfully') . '!');
    } catch (ModelNotFoundException $e) {
      Session::flash('success', __('No record found in database') . '!');
    }


    


    return Response::json(['status' => 'success'], 200);
  }

  public function destroy($id)
  {
    try {
      $gateway = OfflineGateway::findOrFail($id);
      $this->deleteQrCode($gateway->qr_code);
      $gateway->delete();

      return redirect()->back()->with('success',  __('Offline payment gateway deleted successfully') . '!');
    } catch (ModelNotFoundException $e) {
      return redirect()->back()->with('warning',  __('No record found in database') . '!');
    }
  }

  private function storeQrCode(Request $request)
  {
    $file = $request->file('qr_code');
    $filename = uniqid('ewallet_qr_', true) . '.' . $file->getClientOriginalExtension();
    $directory = public_path('assets/img/payment-gateways/ewallet');
    File::ensureDirectoryExists($directory);
    $file->move($directory, $filename);

    return $filename;
  }

  private function deleteQrCode($filename)
  {
    if (!empty($filename)) {
      File::delete(public_path('assets/img/payment-gateways/ewallet/' . basename($filename)));
    }
  }
}
