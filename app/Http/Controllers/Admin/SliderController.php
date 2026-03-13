<?php



namespace App\Http\Controllers\Admin;



use App\Models\Slider;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;



class SliderController extends Controller

{

    public function index()

    {

        $sliders = Slider::orderBy('sort_order', 'asc')->get();

        return view('admin.pages.slider.index', compact('sliders'));
    }



    public function store(Request $request)

    {

        $validator = Validator::make($request->all(), [

            'image_file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            'mobile_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            'title' => ['nullable', 'string', 'max:255', 'special_characters'],

            'description' => ['nullable', 'string'],

            'button_text' => ['nullable', 'string', 'max:255', 'special_characters'],

            'button_link' => ['nullable', 'string', 'max:255', 'special_characters'],

            'sort_order' => ['nullable', 'integer', 'min:0'],

            'is_active' => ['nullable', 'boolean'],

        ]);

        if ($validator->fails()) {

            return response()->json(['msg' => implode(' ', $validator->errors()->all())], 422);
        }



        $payload = [

            'title' => $request->input('title'),

            'description' => $request->input('description'),

            'button_text' => $request->input('button_text'),

            'button_link' => $request->input('button_link'),

            'sort_order' => (int)$request->input('sort_order', 0),

            'is_active' => (bool)$request->boolean('is_active'),

        ];



        if ($request->hasFile('image_file')) {

            $dir = public_path('upload/slider');

            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $file = $request->file('image_file');

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            $name = uniqid('slider_') . '.' . $ext;

            $file->move($dir, $name);

            $payload['image'] = '/upload/slider/' . $name;
        }



        if ($request->hasFile('mobile_image_file')) {

            $dir = public_path('upload/slider');

            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $file = $request->file('mobile_image_file');

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            // Dosya ismini farklılaştırıyoruz ki karışmasın

            $name = uniqid('slider_mobile_') . '.' . $ext;

            $file->move($dir, $name);

            $payload['mobile_image'] = '/upload/slider/' . $name;
        }



        $slider = Slider::create($payload);

        app(AdminLogService::class)->log('Slider Oluşturuldu', null, $slider->toArray());

        return response()->json(['msg' => 'Slider oluşturuldu', 'id' => $slider->id]);
    }



    public function update(Request $request, int $id)
    {
        $slider = Slider::findOrFail($id);
        $before = $slider->toArray();

        $validator = Validator::make($request->all(), [

            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            'mobile_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],

            'title' => ['nullable', 'string', 'max:255', 'special_characters'],

            'description' => ['nullable', 'string'],

            'button_text' => ['nullable', 'string', 'max:255', 'special_characters'],

            'button_link' => ['nullable', 'string', 'max:255', 'special_characters'],

            'sort_order' => ['nullable', 'integer', 'min:0'],

            'is_active' => ['nullable', 'boolean'],

        ]);

        if ($validator->fails()) {

            return response()->json(['msg' => implode(' ', $validator->errors()->all())], 422);
        }



        $payload = [

            'title' => $request->input('title'),

            'description' => $request->input('description'),

            'button_text' => $request->input('button_text'),

            'button_link' => $request->input('button_link'),

            'sort_order' => (int)$request->input('sort_order', $slider->sort_order),

            'is_active' => (bool)$request->boolean('is_active'),

        ];



        if ($request->hasFile('image_file')) {

            $dir = public_path('upload/slider');

            if (!is_dir($dir)) mkdir($dir, 0777, true);

            if ($slider->image && is_file(public_path(ltrim($slider->image, '/')))) {

                @unlink(public_path(ltrim($slider->image, '/')));
            }

            $file = $request->file('image_file');

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            $name = uniqid('slider_') . '.' . $ext;

            $file->move($dir, $name);

            $payload['image'] = '/upload/slider/' . $name;
        }



        if ($request->hasFile('mobile_image_file')) {

            $dir = public_path('upload/slider');

            if (!is_dir($dir)) mkdir($dir, 0777, true);

            if ($slider->mobile_image && is_file(public_path(ltrim($slider->mobile_image, '/')))) {

                @unlink(public_path(ltrim($slider->mobile_image, '/')));
            }

            $file = $request->file('mobile_image_file');

            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');

            $name = uniqid('slider_mobile_') . '.' . $ext;

            $file->move($dir, $name);

            $payload['mobile_image'] = '/upload/slider/' . $name;
        }



        $slider->update($payload);

        app(AdminLogService::class)->log('Slider Güncellendi', $before, $slider->fresh()->toArray());

        return response()->json(['msg' => 'Slider güncellendi']);
    }



    public function destroy(int $id)
    {
        $slider = Slider::findOrFail($id);
        $before = $slider->toArray();

        if ($slider->image && is_file(public_path(ltrim($slider->image, '/')))) {

            @unlink(public_path(ltrim($slider->image, '/')));
        }

        if ($slider->mobile_image && is_file(public_path(ltrim($slider->mobile_image, '/')))) {

            @unlink(public_path(ltrim($slider->mobile_image, '/')));
        }

        $slider->delete();

        app(AdminLogService::class)->log('Slider Silindi', $before, null);

        return response()->json(['msg' => 'Slider silindi']);
    }
}
