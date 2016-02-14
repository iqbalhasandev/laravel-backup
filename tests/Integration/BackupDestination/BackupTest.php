<?php

namespace Spatie\Backup\Test\Integration\BackupCollectionTest;

use Carbon\Carbon;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Test\Integration\TestCase;
use Storage;

class BackupTest extends TestCase
{
    /** @test */
    public function it_can_determine_the_path_of_the_backup()
    {
        $fileName = 'test.zip';

        $backup = $this->getBackupForFile($fileName);

        $this->assertSame("mysite.com/{$fileName}", $backup->getPath());
    }

    /** @test */
    public function it_can_delete_itself()
    {
        $fileName = 'test.zip';

        $backup = $this->getBackupForFile($fileName);

        $fullPath = $this->testHelper->getTempDirectory().'/'.$backup->getPath();

        $this->assertTrue($backup->exists());

        $this->assertTrue(file_exists($fullPath));

        $backup->delete();

        $this->assertFalse($backup->exists());

        $this->assertFalse(file_exists($fullPath));
    }

    /** @test */
    public function it_can_determine_its_size()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $fileSize = filesize($this->testHelper->getTempDirectory().'/'.$backup->getPath());

        $this->assertSame($fileSize, $backup->getSize());

        $this->assertGreaterThan(0, $backup->getSize());
    }

    /** @test */
    public function it_can_determine_its_size_even_after_it_has_been_deleted()
    {
        $backup = $this->getBackupForFile('test.zip', 0, 'this backup has content');

        $backup->delete();

        $this->assertSame(0, $backup->getSize());
    }

    protected function getBackupForFile(string $name, int $ageInDays = 0, string $contents = '') : Backup
    {
        $disk = Storage::disk('local');

        $path = 'mysite.com/'.$name;

        $this->testHelper->createTempFileWithAge(
            $path,
            Carbon::now()->subDays($ageInDays),
            $contents
        );

        return new Backup($disk, $path);
    }
}