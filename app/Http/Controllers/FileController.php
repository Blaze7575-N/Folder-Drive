<?php

namespace App\Http\Controllers;

use App\Http\Requests\FavouriteActionRequest;
use App\Http\Requests\TrashFileRequest;
use App\Jobs\UploadFileOnCloud;
use Illuminate\Support\Facades\Auth;
use App\Models\File;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\FileActionRequest;
use App\Http\Requests\ShareFileRequest;
use App\Http\Resources\FileResource;
use App\Http\Resources\ShareFileResource;
use App\Mail\ShareFilesMail;
use App\Models\FileShare;
use App\Models\StarredFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;


class FileController extends Controller
{

    public function myFiles(Request $request, string $folder = null)
    {
        $favourites = $request->favourite === "1";
        $search = $request->get('search');
        // dd($request->get('search'));
        // echo phpinfo();
        // exit;

        if ($folder) {
            $folder = File::query()
                ->where("created_by", Auth::id())
                ->where("path", $folder)
                ->firstOrFail();
        } elseif (!$folder) {
            $folder = $this->getRoot();
        }

        $query = File::query()
            ->select('files.*')
            ->with("starred")
            ->where('created_by', Auth::id())
            ->orderBy('is_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');


        if ($favourites) {
            $query->join('starred_files', 'starred_files.file_id', '=', 'files.id')
                ->where('starred_files.user_id', Auth::id());
        }

        if ($search) {
            $query->where('name', 'like', "%$search%");
        } else {
            $query->where('parent_id', $folder->id);
        }

        $files = $query->paginate("13");
        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render("MyFiles", compact('files', 'folder', 'ancestors'));
    }

    public function trash(Request $request,)
    {
        $search = $request->get('search');

        if ($search) {
            $files = File::onlyTrashed()
                ->where('created_by', Auth::id())
                ->orderBy('is_folder', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('files.id', 'desc')
                ->where('name', 'like', "%$search%")
                ->paginate("13");
        } else {
            $files = File::onlyTrashed()
                ->where('created_by', Auth::id())
                ->orderBy('is_folder', 'desc')
                ->orderBy('created_at', 'desc')
                ->orderBy('files.id', 'desc')
                ->paginate("13");
        }

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render("Trash", compact('files'));
    }

    public function sharedWithMe(Request $request, string $folder = null)
    {
        $search = $request->get('search');

        if ($folder) {
            $owner_id = (int) $request->owner_id;
            $parent_id = (int) $request->parent_id;
            $query = File::query()
                ->where("created_by", $owner_id)
                ->where("parent_id", $parent_id)
                ->orderBy('is_folder', 'desc')
                ->orderBy('id', 'desc')
                ->orderBy('created_at', 'desc');
        } else {

            $query = File::query()
                ->join('file_shares', 'files.id', 'file_shares.file_id')
                ->where('file_shares.user_id', Auth::id())
                ->orderBy('is_folder', 'desc')
                ->orderBy('files.created_at', 'desc')
                ->orderBy('files.id', 'desc');
        }

        $search ?
            $files = $query->where('name', 'like', "%$search%")->paginate(13) :
            $files = $query->paginate(13);

        $folder ? $files = FileResource::collection($files) : $files = ShareFileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        // dd($filesShareRows, $fileIds, $files);

        return Inertia::render("SharedWithMe", compact('files'));
    }
    public function sharedByMe(Request $request, string $folder = null)
    {
        $search = $request->get('search');

        if ($folder) {
            $owner_id = (int) $request->owner_id;
            $parent_id = (int) $request->parent_id;
            $query = File::query()
                ->where("created_by", $owner_id)
                ->where("parent_id", $parent_id)
                ->orderBy('is_folder', 'desc')
                ->orderBy('id', 'desc')
                ->orderBy('created_at', 'desc');
        } else {

            $query = File::query()
                ->join('file_shares', 'files.id', 'file_shares.file_id')
                ->where('created_by', Auth::id())
                ->orderBy('is_folder', 'desc')
                ->orderBy('files.created_at', 'desc')
                ->orderBy('files.id', 'desc');
        }

        $search ?
            $files = $query->where('name', 'like', "%$search%")->paginate(13) :
            $files = $query->paginate(13);

        $folder ? $files = FileResource::collection($files) : $files = ShareFileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        // dd($filesShareRows, $fileIds, $files);

        return Inertia::render("SharedByMe", compact('files'));
    }


    public function createFolder(StoreFolderRequest $request)
    { //how does this route work?
        $data = $request->validated();
        // Log::channel("debug")->info("FileController viewing data", $data);
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }

    function saveFile($file, $parent, $user)
    {
        /** @var \Illuminate\Http\UploadedFile  $file */

        $path = $file->store("/files/$user->id", 'local');

        /** @var \App\Models\File  $model */

        $model = new File();

        $model->name = $file->getClientOriginalName();
        $model->is_folder = 0;
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();
        $model->storage_path = $path;
        $model->uploaded_on_cloud = 0;

        $parent->appendNode($model);

        // TODO create a job that handles data upload on the cloud server.



        UploadFileOnCloud::dispatch($model);
        // dd($model);
    }

    public function storeFile(StoreFileRequest $request)
    {
        $data = $request->validated();
        // dd($data);
        $parent = $request->parent ?? $this->getRoot();
        $user = $request->user();
        $fileTree = $request->file_tree;

        // dd($data, $fileTree, $user);

        if (!empty($fileTree)) {
            $this->saveFileTree($fileTree, $parent, $user);
        } else
            foreach ($data['files'] as $file) {
                $this->saveFile($file, $parent, $user);
            }
    }


    private function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->whereIsRoot()->firstOrFail();
    }

    function saveFileTree($fileTree, $parent, $user)
    {
        foreach ($fileTree as $key => $subTree) {
            if (is_array($subTree)) {
                $folder = new File();
                $folder->is_folder = 1;
                $folder->name = $key;

                $parent->appendNode($folder);
                $this->saveFileTree($subTree, $folder, $user);
            } else {
                $this->saveFile($subTree, $parent, $user);
            }
        }
    }

    public function moveToTrash(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if ($data['all']) {
            $children = $parent->children;

            foreach ($children as $child) {
                $child->moveToTrash();
            }
        } else {
            foreach ($data["ids"] ?? [] as $id) {
                $file = File::find($id);
                // dd($id, $file->children);
                if ($file->is_folder) {
                    $file->moveToTrash();
                } else {
                    $file->moveToTrash();
                    // Log::channel('debug')->info($file->name . ' ' . $file->path);
                }
            }
        }
    }

    public function download(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent ?? null;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                "message" => "Please select files to download"
            ];
        }


        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name . ".zip";
        } else {
            // if (count($ids) == 1) {
            //     $file = File::find($ids[0]);
            //     if ($file->is_folder) {
            //         if ($file->children->count() == 0) return ["message" => "The folder is empty"];

            //         $url = $this->createZip($file->children);
            //         $filename = $file->name . ".zip";
            //         // dd($filename, $url,["if count is 1 and the file is a folder."]);
            //     } else {
            //         $dest = "public/" . pathinfo($file->storage_path, PATHINFO_BASENAME);
            //         Storage::copy($file->storage_path, $dest);

            //         $url = asset(Storage::url($dest));
            //         $filename = $file->name;
            //     }
            // } else {
            //     $files = File::query()->whereIn('id', $ids)->get();
            //     $url = $this->createZip($files);
            //     $filename = $parent->name . ".zip";
            // }

            [$url, $filename] = $this->getDownloadUrl($ids, $parent->name);
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    public function sdownload(FileActionRequest $request)
    {
        $data = $request->validated();

        Log::channel('debug')->info("", [$data]);

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                "message" => "Please select files to download"
            ];
        }

        $files = File::find($ids);
        $zipName = 'sharedWithMe';

        if ($all) {
            $url = $this->createZip($files);
            $filename = $zipName . ".zip";
        } else {
            if (count($ids) == 1) {
                $file = File::find($ids[0]);
                if ($file->is_folder) {
                    if ($file->children->count() == 0) return ["message" => "The folder is empty"];

                    $url = $this->createZip($file->children);
                    $filename = $file->name . ".zip";
                    // dd($filename, $url,["if count is 1 and the file is a folder."]);
                } else {
                    $dest = "public/" . pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $dest);

                    $url = asset(Storage::url($dest));
                    $filename = $file->name;
                }
            } else {
                $files = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);
                $filename = $zipName . ".zip";
            }
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    protected function createZip($files): string
    {
        // $zipPath = "zip/" . Str::random() . ".zip";
        // $publicPath = "public/$zipPath";



        // $zipFile = Storage::path($publicPath);

        // $zip = new \ZipArchive();

        // if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true) {
        //     $this->addFilesToZip($zip, $files);
        // }

        // Log::channel('console')->info("\nMessage: The CreateZip function has been called.\nzipPath: $zipPath\npublicPath: $publicPath\nzipFile: $zipFile");


        // return asset(Storage::url($zipPath));

        $zipPath = 'zip/' . Str::random() . '.zip';
        $publicPath = "$zipPath";

        if (!is_dir(dirname($publicPath))) {
            Storage::disk('public')->makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::disk('public')->path($publicPath);

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::disk('local')->url($zipPath));
    }

    private function addFilesToZip($zip, $files, $ancestors = ''): void
    {
        // foreach ($files as $file) {
        //     if ($file->is_folder) {
        //         $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . "/");
        //     } else {
        //         $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
        //     }
        // }

        foreach ($files as $file) {
            if ($file->is_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            } else {
                $localPath = Storage::disk('local')->path($file->storage_path);
                if ($file->uploaded_on_cloud == 1) {
                    $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);
                    $content = Storage::get($file->storage_path);
                    Storage::disk('public')->put($dest, $content);
                    $localPath = Storage::disk('public')->path($dest);
                }

                $zip->addFile($localPath, $ancestors . $file->name);
            }
        }
    }

    public function restore(TrashFileRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $files = File::onlyTrashed()->where('created_by', Auth::id())->get();
            foreach ($files as $file) {
                $file->restore();
            }
        } else {
            $files = File::onlyTrashed()
                ->where('created_by', Auth::id())
                ->whereIn('id', $data['ids'])->get();
            // dd($files);
            foreach ($files as $file) {
                $file->restore();
            }
        }
    }

    public function deleteForever(TrashFileRequest $request)
    {
        $data = $request->validated();

        if ($data['all']) {
            $files = File::onlyTrashed()->where('created_by', Auth::id())->get();
            foreach ($files as $file) {
                $this->deleteFile($file);
            }
        } else {
            $files = File::onlyTrashed()
                ->where('created_by', Auth::id())
                ->whereIn('id', $data['ids'])->get();
            // dd($files[0]);
            foreach ($files as $file) {
                $this->deleteFile($file);
            }
        }
    }

    protected function deleteFile($file)
    {
        $children = $file->children ?? [];
        // dd($file);
        foreach ($children as $child) {
            $child->is_folder ? $this->deleteFile($child) : Storage::delete($child->storage_path);
            $child->forceDelete();
        }

        if (!$file->is_folder) Storage::delete($file->storage_path);
        $file->forceDelete();
    }

    public function addToFavourite(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        $children = [];


        if ($data['all']) {
            $children = $parent->children;
        } else {
            $children = File::find($data["ids"])->where('created_by', Auth::id());
        }

        // dd($parent, $children);

        $data = [];
        foreach ($children as $child) {
            $data[] = [
                "file_id" => $child->id,
                "user_id" => Auth::id(),
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ];
        }
        // dd($data);

        StarredFile::insert($data);

        return to_route('myFiles', ['folder' => $parent->path]);
    }

    // The reason there are two add Favourite functions is because one is when you use the star icon with each file to make the request( a single file makes the request) 
    // and the other is when you use the button to make the request.
    public function addRemoveFavourite(FavouriteActionRequest $request)
    {
        $data = $request->validated();
        $file = StarredFile::query()->where('file_id', $data['id'])->where('user_id', Auth::id())->first();

        if (!$file) {
            $data = [
                'file_id' => $data['id'],
                'user_id' => Auth::id(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            StarredFile::create($data);
        } else {
            $file->delete();
        }
    }

    public function share(ShareFileRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        // Log::channel('debug')->info("",['message'=>"Start of the download function."]);

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                "message" => "Please select files to download"
            ];
        }

        $user = User::query()->where("email", $data['email'])->first();

        if (!$user) return redirect()->back();

        $data['all'] ? $files = $parent->children : $files = File::find($data['ids']);

        $ids = Arr::pluck($files, 'id');
        $existing_ids = FileShare::query()
            ->whereIn('file_id', $ids)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('file_id');

        // dd($existing_ids,$ids,$files);

        $data = [];
        foreach ($files as $file) {
            if ($existing_ids->has($file->id)) continue;

            $data[] = [
                'user_id' => $user->id,
                'file_id' => $file->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }

        FileShare::insert($data);


        Mail::to($user)->send(new ShareFilesMail($user, Auth::user(), $files));

        return redirect()->back();
    }

    private function getDownloadUrl(array $ids, $zipName)
    {
        if (count($ids) === 1) {
            $file = File::find($ids[0]);
            if ($file->is_folder) {
                if ($file->children->count() === 0) {
                    return [
                        'message' => 'The folder is empty'
                    ];
                }
                $url = $this->createZip($file->children);
                $filename = $file->name . '.zip';
            } else {
                $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);
                if ($file->uploaded_on_cloud) {
                    $content = Storage::get($file->storage_path);
                } else {
                    $content = Storage::disk('local')->get($file->storage_path);
                }

                Log::debug("Getting file content. File:  " . $file->storage_path) . ". Content: " .  intval($content);

                $success = Storage::disk('public')->put($dest, $content);
                Log::debug('Inserted in public disk. "' . $dest . '". Success: ' . intval($success));
                $url = asset(Storage::disk('public')->url($dest));
                Log::debug("Logging URL " . $url);
                $filename = $file->name;
            }
        } else {
            $files = File::query()->whereIn('id', $ids)->get();
            $url = $this->createZip($files);

            $filename = $zipName . '.zip';
        }

        return [$url, $filename];
    }
}
