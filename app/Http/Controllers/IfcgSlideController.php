<?php
namespace App\Http\Controllers;
use App\Models\IfcgSlide;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class IfcgSlideController extends Controller {
 public function index(){return view('ifcg.slides',['slides'=>IfcgSlide::orderBy('sort_order')->orderBy('id')->get()]);}
 public function store(Request $request){$slide=new IfcgSlide;$this->save($request,$slide);\App\Services\CrmAccess::audit('content.slide.created',$slide,['locale'=>$slide->locale,'active'=>$slide->is_active]);return back()->with('success',__('ifcg.saved'));}
 public function update(Request $request,IfcgSlide $slide){$this->save($request,$slide);\App\Services\CrmAccess::audit('content.slide.updated',$slide,['locale'=>$slide->locale,'active'=>$slide->is_active]);return back()->with('success',__('ifcg.saved'));}
 public function disable(IfcgSlide $slide){$slide->update(['is_active'=>false]);\App\Services\CrmAccess::audit('content.slide.disabled',$slide);return back()->with('success',__('ifcg.saved'));}
 private function save(Request $request,IfcgSlide $slide):void {
  $data=$request->validate([
   'locale'=>['nullable',Rule::in(array_keys(config('ifcg.locales')))],'title'=>'nullable|string|max:180','description'=>'nullable|string|max:700',
   'button_text'=>'nullable|string|max:80','button_url'=>['required','string','max:255',function($attribute,$value,$fail){if(!preg_match('~^/(?!/)[A-Za-z0-9/_?=&%#.-]*$~D',$value))$fail(__('media.local_link'));}],
   'sort_order'=>'required|integer|min:0|max:9999','is_active'=>'nullable|boolean',
   'media'=>[$slide->exists?'nullable':'required','file','mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm','max:20480'],
   'poster'=>['nullable','image','mimes:jpg,jpeg,png,webp','max:5120']
  ]);
  $data=collect($data)->except(['media','poster'])->all();$data['is_active']=$request->boolean('is_active');
  if($request->hasFile('media')){$file=$request->file('media');$mime=$file->getMimeType();$extensions=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','video/mp4'=>'mp4','video/webm'=>'webm'];$name=bin2hex(random_bytes(16)).'.'.$extensions[$mime];$data['media_path']='/uploads/'.$file->storeAs('ifcg-slides',$name,'public_uploads');$data['media_type']=str_starts_with($mime,'video/')?'video':'image';}
  if($request->hasFile('poster')){$data['poster_path']='/uploads/'.$request->file('poster')->store('ifcg-slides','public_uploads');}
  $slide->fill($data)->save();
 }
}
