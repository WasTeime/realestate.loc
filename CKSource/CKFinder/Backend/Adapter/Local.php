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

namespace CKSource\CKFinder\Backend\Adapter;

use CKSource\CKFinder\{Acl\Acl,
    Acl\Permission,
    Backend\Backend,
    Exception\AccessDeniedException,
    Exception\FolderNotFoundException,
    Filesystem\Path,
    ResourceType\ResourceType,
    Utils};
use League\Flysystem\Exception;
use SplFileInfo;

/**
 * Local file system adapter.
 *
 * A wrapper class for \League\Flysystem\Adapter\Local with
 * additions for `chmod` permissions management and conversions
 * between the file system and connector file name encoding.
 */
class Local extends \League\Flysystem\Adapter\Local
{
    /**
     * Constructor.
     *
     * @param array $backendConfig Backend configuration node.
     *
     * @throws AccessDeniedException
     * @throws FolderNotFoundException
     */
    public function __construct(protected array $backendConfig)
    {
        if (empty($backendConfig['root'])) {
            $baseUrl = $backendConfig['baseUrl'];
            $baseUrl = preg_replace('|^http(s)?://[^/]+|i', '', $baseUrl);
            $backendConfig['root'] = Path::combine(Utils::getRootPath(), Utils::decodeURLParts($baseUrl));
        }

        if (!is_dir($backendConfig['root'])) {
            @mkdir($backendConfig['root'], $backendConfig['chmodFolders'], true);
            if (!is_dir($backendConfig['root'])) {
                throw new FolderNotFoundException(
                    sprintf(
                        'The root folder of backend "%s" not found (%s)',
                        $backendConfig['name'],
                        $backendConfig['root']
                    )
                );
            }
        }

        if (!is_readable($backendConfig['root'])) {
            throw new AccessDeniedException(
                sprintf(
                    'The root folder of backend "%s" is not readable (%s)',
                    $backendConfig['name'],
                    $backendConfig['root']
                )
            );
        }

        parent::__construct($backendConfig['root'], LOCK_EX, self::SKIP_LINKS, [
            'file' => ['public' => $backendConfig['chmodFiles']],
            'dir' => ['public' => $backendConfig['chmodFolders']],
        ]);
    }

    /**
     * Creates a stream for writing to a file.
     *
     * @return resource|false
     * @throws Exception
     */
    public function createWriteStream(string $path)
    {
        $location = $this->applyPathPrefix($path);
        $this->ensureDirectory(dirname($location));
        $chmodFiles = $this->backendConfig['chmodFiles'];

        if (!$stream = fopen($location, 'ab+')) {
            return false;
        }

        $oldUmask = umask(0);
        chmod($location, $chmodFiles);
        umask($oldUmask);

        return $stream;
    }

    /**
     * Checks if the directory contains subdirectories.
     */
    public function containsDirectories(
        Backend $backend,
        ResourceType $resourceType,
        string $clientPath,
        Acl $acl
    ): bool {
        $location =
            rtrim($this->applyPathPrefix(Path::combine($resourceType->getDirectory(), $clientPath)), '/\\') . '/';

        if (!is_dir($location) || (false === $fh = @opendir($location))) {
            return false;
        }

        $hasChildren = false;
        $resourceTypeName = $resourceType->getName();
        $clientPath = rtrim($clientPath, '/\\') . '/';

        while (false !== ($filename = readdir($fh))) {
            if ('.' === $filename || '..' === $filename) {
                continue;
            }

            if (is_dir($location . $filename)) {
                if (!$acl->isAllowed($resourceTypeName, $clientPath . $filename, Permission::FOLDER_VIEW)) {
                    continue;
                }
                if ($backend->isHiddenFolder($filename)) {
                    continue;
                }
                $hasChildren = true;

                break;
            }
        }

        closedir($fh);

        return $hasChildren;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDir($dirname): bool
    {
        $location = $this->applyPathPrefix($dirname);

        if ($this->backendConfig['followSymlinks'] && is_link($location)) {
            return unlink($location);
        }

        return parent::deleteDir($dirname);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeFileInfo(SplFileInfo $file)
    {
        if ($this->backendConfig['followSymlinks']) {
            return $this->mapFileInfo($file);
        }

        return parent::normalizeFileInfo($file);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapFileInfo(SplFileInfo $file): array
    {
        $normalized = parent::mapFileInfo($file);

        if ($this->backendConfig['followSymlinks'] && $file->isLink()) {
            $normalized['type'] = $file->isDir() ? 'dir' : 'file';

            if ('file' === $normalized['type']) {
                $normalized['size'] = $file->getSize();
            }
        }

        return $normalized;
    }
}
