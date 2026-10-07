<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorEwallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class EwalletController extends Controller
{
    public function index()
    {
        $wallets = VendorEwallet::where('vendor_id', Auth::guard('vendor')->id())->latest()->get();

        return view('vendors.ewallets.index', compact('wallets'));
    }

    public function store(Request $request)
    {
        $data = $this->validateWallet($request, true);
        $data['vendor_id'] = Auth::guard('vendor')->id();
        $data['qr_code'] = $this->storeQrCode($request);
        $data = array_merge($data, $this->storeVerificationDocuments($request));
        $data['status'] = $request->boolean('status');

        VendorEwallet::create($data);
        Session::flash('success', __('E-wallet added successfully.'));

        return redirect()->route('vendor.ewallets.index');
    }

    public function update(Request $request, int $id)
    {
        $wallet = $this->walletForVendor($id);
        $data = $this->validateWallet($request, false, $wallet);
        $data['status'] = $request->boolean('status');

        if ($request->hasFile('qr_code')) {
            $this->deleteQrCode($wallet->qr_code);
            $data['qr_code'] = $this->storeQrCode($request);
        }

        foreach ($this->verificationDocumentFields() as $field) {
            if ($request->hasFile($field)) {
                $this->deleteVerificationDocument($wallet->{$field});
                $data[$field] = $this->storeVerificationDocument($request, $field);
            }
        }

        $wallet->update($data);
        Session::flash('success', __('E-wallet updated successfully.'));

        return redirect()->route('vendor.ewallets.index');
    }

    public function destroy(int $id)
    {
        $wallet = $this->walletForVendor($id);

        if ($wallet->bookings()->exists()) {
            $wallet->update(['status' => false]);
            Session::flash('warning', __('This e-wallet has bookings and was disabled instead of deleted.'));
        } else {
            $this->deleteQrCode($wallet->qr_code);
            foreach ($this->verificationDocumentFields() as $field) {
                $this->deleteVerificationDocument($wallet->{$field});
            }
            $wallet->delete();
            Session::flash('success', __('E-wallet deleted successfully.'));
        }

        return redirect()->route('vendor.ewallets.index');
    }

    public function downloadVerificationDocument(int $id, string $document)
    {
        $wallet = $this->walletForVendor($id);

        abort_unless(in_array($document, $this->verificationDocumentFields(), true), 404);
        $path = $wallet->{$document};
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    private function validateWallet(Request $request, bool $qrRequired, ?VendorEwallet $wallet = null): array
    {
        $profileScreenshotRequired = $qrRequired || empty($wallet?->profile_screenshot);
        $registrationRequired = $qrRequired || empty($wallet?->business_registration_document);
        $mayorsPermitRequired = $qrRequired || empty($wallet?->mayors_permit_document);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:100'],
            'verified_name' => ['required', 'string', 'max:150'],
            'tin_number' => ['required', 'regex:/^[0-9-]{9,20}$/', 'max:30'],
            'dti_business_name_number' => ['required', 'string', 'max:100'],
            'qr_code' => [$qrRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'profile_screenshot' => [$profileScreenshotRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'business_registration_document' => [$registrationRequired ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'mayors_permit_document' => [$mayorsPermitRequired ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
    }

    private function walletForVendor(int $id): VendorEwallet
    {
        return VendorEwallet::where('vendor_id', Auth::guard('vendor')->id())->findOrFail($id);
    }

    private function storeQrCode(Request $request): string
    {
        $directory = public_path('assets/admin/img/vendor-ewallet-qr/');
        @mkdir($directory, 0775, true);
        $file = $request->file('qr_code');
        $name = uniqid('wallet_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $name);

        return $name;
    }

    private function deleteQrCode(?string $name): void
    {
        if ($name) {
            @unlink(public_path('assets/admin/img/vendor-ewallet-qr/' . $name));
        }
    }

    private function verificationDocumentFields(): array
    {
        return ['profile_screenshot', 'business_registration_document', 'mayors_permit_document'];
    }

    private function storeVerificationDocuments(Request $request): array
    {
        $documents = [];
        foreach ($this->verificationDocumentFields() as $field) {
            $documents[$field] = $this->storeVerificationDocument($request, $field);
        }

        return $documents;
    }

    private function storeVerificationDocument(Request $request, string $field): string
    {
        return $request->file($field)->store('vendor-ewallet-verification/' . Auth::guard('vendor')->id(), 'local');
    }

    private function deleteVerificationDocument(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }
}
