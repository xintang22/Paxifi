<?php namespace Paxifi\Test\Support\FileUploader;

use Paxifi\Support\FileUploader\FileSystemUploaderProvider;
use Mockery as m;

class FileSystemUploaderProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testUploadSingleFile()
    {
        $foo = $this->mockFileUploader();

        $config = m::mock('\Illuminate\Config\Repository');
        $config->shouldReceive('get')->with('paxifi.files.uploads_directory')->once()->andReturn('target/directory');
        $config->shouldReceive('get')->with('app.url')->once()->andReturn('http://foo');

        $fsUploader = new FileSystemUploaderProvider($config);

        $response = $fsUploader->upload($foo);

        $this->assertCount(1, $response);

        $this->assertRegExp('/http:\/\/foo/', $response[0]);
    }

    public function testUploadMultipleFiles()
    {
        $foo = $this->mockFileUploader();
        $bar = $this->mockFileUploader();

        $config = m::mock('\Illuminate\Config\Repository');
        $config->shouldReceive('get')->with('paxifi.files.uploads_directory')->twice()->andReturn('target/directory');
        $config->shouldReceive('get')->with('app.url')->twice()->andReturn('http://foo');

        $fsUploader = new FileSystemUploaderProvider($config);

        $response = $fsUploader->upload([$foo, $bar]);

        $this->assertCount(2, $response);

        $this->assertRegExp('/http:\/\/foo/', $response[0]);
        $this->assertRegExp('/http:\/\/foo/', $response[1]);
    }

    /**
     * @return m\MockInterface
     */
    protected function mockFileUploader()
    {
        $file = m::mock('\Symfony\Component\HttpFoundation\File\UploadedFile');
        $file->shouldReceive('move')->withArgs(['target/directory', m::any()])->once()->andReturn(true);
        $file->shouldReceive('getClientOriginalExtension')->once()->andReturn('txt');
        return $file;
    }

    public function tearDown()
    {
        m::close();
    }
}
 