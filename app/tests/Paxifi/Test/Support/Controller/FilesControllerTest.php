<?php namespace Paxifi\Test\Support\Controller;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FilesControllerTest extends \TestCase
{
    protected $workspace;

    public function setUp()
    {
        parent::setUp();

        $this->workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . time() . rand(0, 1000);
        mkdir($this->workspace, 0777, true);
        $this->workspace = realpath($this->workspace);

        // Override the files uploads directory
        $this->app['config']->set('paxifi.files.uploads_directory', $this->workspace);
    }

    public function tearDown()
    {
        $this->clean($this->workspace);
    }

    protected function clean($file)
    {
        if (is_dir($file) && !is_link($file)) {
            $dir = new \FilesystemIterator($file);
            foreach ($dir as $childFile) {
                $this->clean($childFile);
            }

            rmdir($file);
        } else {
            unlink($file);
        }
    }

    public function testUploadSingleFile()
    {
        $foo = $this->files(true);

        $response = $this->call('post', 'files', array(), array('files' => $foo));

        $this->assertResponseOk();

        // If successful upload, the foo.txt will no longer exist.
        $this->assertFileNotExists($this->workspace . DIRECTORY_SEPARATOR . 'foo.txt');

        return $response;
    }

    /**
     * @depends testUploadSingleFile
     */
    public function testReturnUrlToUploadedFile(JsonResponse $response)
    {
        $this->assertJson($response->getContent());
        $this->assertContains('urls', $response->getContent());
    }

    /**
     * @expectedException \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    public function testThrowFileExceptionIfNoFileUploaded()
    {
        $this->call('post', 'files');
    }

    public function testUploadMultipleFiles()
    {
        $this->call('post', 'files', array(), array('files' => $this->files()));

        $this->assertResponseOk();

        // If successful upload, the foo.txt will no longer exist.
        $this->assertFileNotExists($this->workspace . DIRECTORY_SEPARATOR . 'foo.txt');
        $this->assertFileNotExists($this->workspace . DIRECTORY_SEPARATOR . 'bar.txt');
    }

    protected function files($one = false)
    {
        $foo = $this->workspace . DIRECTORY_SEPARATOR . 'foo.txt';
        file_put_contents($foo, 'foo');

        $bar = $this->workspace . DIRECTORY_SEPARATOR . 'bar.txt';
        file_put_contents($bar, 'bar');

        $files = array(
            new UploadedFile($foo, 'foo.txt'),
            new UploadedFile($bar, 'bar.txt'),
        );

        return $one ? $files[0] : $files;
    }

}
 