<?php namespace Paxifi\Test\Support\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FilesControllerTest extends \TestCase
{
    protected $workspace;

    public function setUp()
    {
        $this->workspace = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . time() . rand(0, 1000);
        mkdir($this->workspace, 0777, true);
        $this->workspace = realpath($this->workspace);

        parent::setUp();
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
        $this->call('post', 'files', array(), array('files' => $this->files(true)));

        $targetPath = $this->app['path.public'] . '/uploads/foo.txt';

        $this->assertResponseOk();

        $this->assertFileExists($targetPath);

        $this->clean($targetPath);
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
 