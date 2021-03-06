<?php namespace Paxifi\Support\Controller;

use Illuminate\Routing\Controller;
use Paxifi\Support\FileUploader\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FilesController extends Controller
{
    public function upload()
    {
        if (!\Input::hasFile('files'))
            throw new FileException('No file was uploaded.');

        $file = \Input::file('files');

        $target = \Input::get('target', 'photos');

        switch ($target) {
            case 'profile':
                $target = 'profiles';
                break;

            case 'product':
                $target = 'products';
                break;

            default:
                $target = 'photos';
                break;
        }

        $url = FileUploader::upload($file, $target);

        return \Response::json(array(
            'success' => 1,
            'urls' => $url,
        ));
    }
}