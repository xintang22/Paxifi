<?php namespace Paxifi\Support\Controller;

use Illuminate\Routing\Controller;
use Paxifi\Support\FileUploader\FileUploader;

class FilesController extends Controller
{
    public function upload()
    {
        $file = \Input::file('files');

        $url = FileUploader::upload($file);
    }
} 