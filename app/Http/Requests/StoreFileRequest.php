<?php

namespace App\Http\Requests;


use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StoreFileRequest extends ParentIdBaseRequest
{

    protected function prepareForValidation()
    {
        $paths = array_filter($this->relative_paths ?? [], fn ($f) => $f != null);
        $this->merge([
            "file_paths" => $paths,
            "folder_name" => $this->detectFolderName($paths)
        ]);
    }

    function passedValidation()
    {
        $data = $this->validated();

        $this->replace([
            'file_tree' => $this->buildFileTree($this->file_paths ?? [], $data['files'] ?? []),
        ]);
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        /** @var $value \Illuminate\Http\UploadedFile */
        return array_merge(parent::rules(), [
            'folder_name' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        /** @var $value \Illuminate\Http\UploadedFile */
                        $file = File::query()->where('name', $value)
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        Log::channel('debug')->debug("This is StoreFileRequest for folder: $file");

                        if ($file) {
                            $fail('Folder "' . $value . '" already exists.');
                        }
                    }
                }
            ],
            'files.*' => [
                'required',
                'file',
                function ($attribute, $value, $fail) {
                    if (!$this->folder_name) {
                        /** @var \Illuminate\Http\UploadedFile  $value */

                        $file = File::query()->where('name', $value->getClientOriginalName())
                            ->where('created_by', Auth::id())
                            ->where('parent_id', $this->parent_id)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($file) {
                            $fail('File "' . $value->getClientOriginalName() . '" already exists.');
                        }
                    }
                }
            ]
        ]);
    }

    public function detectFolderName($paths)
    {
        if (!$paths) {
            return null;
        }

        // Log::channel('debug')->info("storeFileReq viewing paths", $paths);

        $parts = explode('/', $paths[0]);
        // Log::channel('debug')->info('storeFileReq parts', $parts);
        return $parts[0];
    }

    function buildFileTree($filePaths, $files)
    {
        $filePaths = array_slice($filePaths, 0, count($files));
        $filePaths = array_filter($filePaths ?? [], fn ($f) => $f != null);

        // dd($filePaths, $files);

        $tree = [];

        foreach ($filePaths as $ind => $filePath) {
            $parts = explode('/', $filePath);

            $currentNode = &$tree;

            foreach ($parts as $i => $part) {

                if (!isset(($tree[$part]))) $currentNode[$part] = [];

                if ($i == count($parts) - 1) $currentNode[$part] = $files[$ind];
                else $currentNode = &$currentNode[$part];
            }
        }

        return $tree;

    }
}
