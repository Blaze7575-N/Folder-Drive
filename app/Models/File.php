<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\HasCreatorAndUpdater;
use App\Models\StarredFile;
use Carbon\Carbon;

;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Kalnoy\Nestedset\NodeTrait;

class File extends Model
{
    use HasFactory, HasCreatorAndUpdater, NodeTrait, SoftDeletes;


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(File::class, 'created_by');
    }

    public function starred()
    {
        return $this->hasOne(StarredFile::class,'file_id','id')->where('user_id', Auth::id());
    } 

    public function owner(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['created_by'] == Auth::id() ? 'me' : $this->user->id;
            }
        );
    }

    public function isRoot()
    {
        return $this->parent_id == null;
    }


    function isOwnedBy($userId)
    {
        return $this->created_by == $userId;
    }

    function getSize()
    {
        $suffixes = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        
        $power = $this->size ? floor(log($this->size, 1024)) : 0;
        $size = round($this->size / pow(1024, $power), 2) . ' ' . $suffixes[$power];
        
        
        return $size;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->parent) {
                return;
            }

            $model->path = (!$model->parent->isRoot() ? $model->parent->path . '/' : '') . Str::slug($model->name);
        });

        // static::deleted(function ($model) {
            
        //     if (!$model->is_folder){
        //         // dd($model);
        //         Storage::delete($model->storage_path);
        //     }
        //     // you got to create a function that deletes all the files in the sub-folders of the deleting folder.
        // });
    }

    public function moveToTrash(){
        $this->deleted_at = Carbon::now();

        $this->save();
    }
}
