<?php

/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

namespace CKSource\CKFinder\Command;

use CKSource\CKFinder\{Acl\Permission,
    Cache\CacheManager,
    Config,
    Error,
    Event\AfterCommandEvent,
    Event\CKFinderEvent,
    Event\FileUploadEvent,
    Exception\InvalidExtensionException,
    Exception\InvalidNameException,
    Exception\InvalidUploadException,
    Image,
    Thumbnail\ThumbnailRepository};
use CKSource\CKFinder\Filesystem\{File\UploadedFile, Folder\WorkingFolder, Path};
use Exception;
use Symfony\Component\{EventDispatcher\EventDispatcher, HttpFoundation\Request};

class FileUpload extends CommandAbstract
{
    protected string $requestMethod = Request::METHOD_POST;

    protected array $requires = [Permission::FILE_CREATE];

    /**
     * @throws InvalidExtensionException
     * @throws InvalidNameException
     * @throws InvalidUploadException
     * @throws Exception
     */
    public function execute(
        Request $request,
        WorkingFolder $workingFolder,
        EventDispatcher $dispatcher,
        Config $config,
        CacheManager $cache,
        ThumbnailRepository $thumbsRepository
    ): array {
        // #111 IE9 download JSON issue workaround
        if ($request->get('asPlainText')) {
            $uploadEvents = [
                CKFinderEvent::AFTER_COMMAND_FILE_UPLOAD,
                CKFinderEvent::AFTER_COMMAND_QUICK_UPLOAD,
            ];

            foreach ($uploadEvents as $eventName) {
                $dispatcher->addListener($eventName, function (AfterCommandEvent $event) {
                    $response = $event->getResponse();
                    $response->headers->set('Content-Type', 'text/plain');
                });
            }
        }

        $uploaded = 0;

        $warningErrorCode = null;
        $upload = $request->files->get('upload');

        if (is_null($upload)) {
            throw new InvalidUploadException();
        }

        $uploadedFile = new UploadedFile($upload, $this->app);

        if (!$uploadedFile->isValid()) {
            throw new InvalidUploadException($uploadedFile->getErrorMessage());
        }

        $uploadedFile->sanitizeFilename();

        if ($uploadedFile->wasRenamed()) {
            $warningErrorCode = Error::UPLOADED_INVALID_NAME_RENAMED;
        }

        if (!$uploadedFile->hasValidFilename() || $uploadedFile->isHiddenFile()) {
            throw new InvalidNameException();
        }

        if (!$uploadedFile->hasAllowedExtension()) {
            throw new InvalidExtensionException();
        }

        // Autorename if required
        $overwriteOnUpload = $config->get('overwriteOnUpload');
        if (!$overwriteOnUpload && $uploadedFile->autorename()) {
            $warningErrorCode = Error::UPLOADED_FILE_RENAMED;
        }

        $fileName = $uploadedFile->getFilename();

        if (!$uploadedFile->isAllowedHtmlFile() && $uploadedFile->containsHtml()) {
            throw new InvalidUploadException('HTML detected in disallowed file type', Error::UPLOADED_WRONG_HTML_FILE);
        }

        if ($config->get('secureImageUploads') && $uploadedFile->isImage() && !$uploadedFile->isValidImage()) {
            throw new InvalidUploadException('Invalid upload: corrupted image', Error::UPLOADED_CORRUPT);
        }

        $maxFileSize = $workingFolder->getResourceType()->getMaxSize();

        if (!$config->get('checkSizeAfterScaling') && $maxFileSize && $uploadedFile->getSize() > $maxFileSize) {
            throw new InvalidUploadException('Uploaded file is too big', Error::UPLOADED_TOO_BIG);
        }

        if (Image::isSupportedExtension($uploadedFile->getExtension())) {
            $imagesConfig = $config->get('images');
            $image = Image::create($uploadedFile->getContents());

            if (($imagesConfig['maxWidth'] && $image->getWidth() > $imagesConfig['maxWidth']) ||
                ($imagesConfig['maxHeight'] && $image->getHeight() > $imagesConfig['maxHeight'])) {
                $image->resize($imagesConfig['maxWidth'], $imagesConfig['maxHeight'], $imagesConfig['quality']);
                $imageData = $image->getData();
                $uploadedFile->save($imageData);
            }

            $cache->set(
                Path::combine(
                    $workingFolder->getResourceType()->getName(),
                    $workingFolder->getClientCurrentFolder(),
                    $fileName
                ),
                $image->getInfo()
            );

            unset($imageData, $image);
        }

        if ($maxFileSize && $uploadedFile->getSize() > $maxFileSize) {
            throw new InvalidUploadException('Uploaded file is too big', Error::UPLOADED_TOO_BIG);
        }

        $event = new FileUploadEvent($this->app, $uploadedFile);
        $dispatcher->dispatch($event, CKFinderEvent::FILE_UPLOAD);

        if (!$event->isPropagationStopped()) {
            $uploadedFileStream = $uploadedFile->getContentsStream();
            $uploaded = (int)$workingFolder->putStream($fileName, $uploadedFileStream, $uploadedFile->getMimeType());

            if (is_resource($uploadedFileStream)) {
                fclose($uploadedFileStream);
            }

            if ($overwriteOnUpload) {
                $thumbsRepository->deleteThumbnails(
                    $workingFolder->getResourceType(),
                    $workingFolder->getClientCurrentFolder(),
                    $fileName
                );
            }

            if (!$uploaded) {
                $warningErrorCode = Error::ACCESS_DENIED;
            }
        }

        $responseData = [
            'fileName' => $fileName,
            'uploaded' => $uploaded,
        ];

        if ($warningErrorCode) {
            $errorMessage = $this->app['translator']->translateErrorMessage($warningErrorCode, ['name' => $fileName]);
            $responseData['error'] = [
                'number' => $warningErrorCode,
                'message' => $errorMessage,
            ];
        }

        return $responseData;
    }
}
