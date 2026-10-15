<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UploadFile;
use App\Models\Language;
use App\Models\PricingFeatureSlide;
use App\Rules\ImageMimeTypeRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PricingFeatureSliderController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::where('code', $request->language)->firstOrFail();

        return view('admin.pricing-feature-slider.index', [
            'language' => $language,
            'langs' => Language::all(),
            'slides' => PricingFeatureSlide::where('language_id', $language->id)->orderBy('serial_number')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language_id' => ['required', 'exists:languages,id'], 'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'], 'serial_number' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'boolean'], 'image' => ['required', new ImageMimeTypeRule()],
        ]);
        if ($validator->fails()) return Response::json(['errors' => $validator->errors()], 400);
        $image = UploadFile::store(public_path('assets/img/pricing-features/'), $request->file('image'));
        PricingFeatureSlide::create($request->except('image') + ['image' => $image]);
        Session::flash('success', __('Pricing feature slide added successfully') . '!');
        return Response::json(['status' => 'success']);
    }

    public function update(Request $request)
    {
        $slide = PricingFeatureSlide::findOrFail($request->id);
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:1000'],
            'serial_number' => ['required', 'integer', 'min:1'], 'status' => ['required', 'boolean'],
            'image' => $request->hasFile('image') ? [new ImageMimeTypeRule()] : [],
        ]);
        if ($validator->fails()) return Response::json(['errors' => $validator->errors()], 400);
        $image = $slide->image;
        if ($request->hasFile('image')) $image = UploadFile::update(public_path('assets/img/pricing-features/'), $request->file('image'), $slide->image);
        $slide->update($request->except(['id', 'image']) + ['image' => $image]);
        Session::flash('success', __('Pricing feature slide updated successfully') . '!');
        return Response::json(['status' => 'success']);
    }

    public function destroy(int $id)
    {
        $slide = PricingFeatureSlide::findOrFail($id);
        @unlink(public_path('assets/img/pricing-features/' . $slide->image));
        $slide->delete();
        return back()->with('success', __('Pricing feature slide deleted successfully') . '!');
    }
}
