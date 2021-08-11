<?php

namespace RTippin\Messenger\Actions\Bots;

use Exception;
use Illuminate\Http\UploadedFile;
use RTippin\Messenger\Exceptions\FeatureDisabledException;
use RTippin\Messenger\Exceptions\FileServiceException;
use RTippin\Messenger\Models\Bot;
use Throwable;

class StoreBotAvatar extends BotAvatarAction
{
    /**
     * @param mixed ...$parameters
     * @var Bot[0]
     * @var UploadedFile[1]['image']
     * @return $this
     * @throws FeatureDisabledException|FileServiceException|Exception
     */
    public function execute(...$parameters): self
    {
        $this->bailWhenFeatureDisabled();

        $this->setBot($parameters[0])
            ->attemptTransactionOrRollbackFile($this->upload($parameters[1]['image']))
            ->generateResource()
            ->fireEvents();

        return $this;
    }

    /**
     * The avatar has been uploaded at this point, so if our
     * database actions fail, we want to remove the avatar
     * from storage and rethrow the exception.
     *
     * @param string $fileName
     * @return $this
     * @throws Exception
     */
    private function attemptTransactionOrRollbackFile(string $fileName): self
    {
        try {
            return $this->removeOldIfExist()->updateBotAvatar($fileName);
        } catch (Throwable $e) {
            $this->fileService
                ->setDisk($this->getBot()->getStorageDisk())
                ->destroy("{$this->getBot()->getAvatarDirectory()}/$fileName");

            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws FileServiceException
     */
    private function upload(UploadedFile $file): string
    {
        return $this->fileService
            ->setType('image')
            ->setDisk($this->getBot()->getStorageDisk())
            ->setDirectory($this->getBot()->getAvatarDirectory())
            ->upload($file);
    }
}
