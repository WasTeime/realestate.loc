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

namespace CKSource\CKFinder\Request\Transformer;

use JsonException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The JsonRequestTransformer class.
 *
 * Transforms POST requests based on the `jsonData` parameter.
 */
class JsonTransformer implements TransformerInterface
{
    /**
     * Transforms a request to the required format.
     *
     * @param Request $request the original request
     *
     * @return Request the request after the transformation
     * @throws JsonException
     */
    public function transform(Request $request): Request
    {
        // Transform only POST requests
        if (!$request->isMethod('POST')) {
            return $request;
        }

        // Transform only if POST request contains jsonData field
        $jsonData = $request->request->get('jsonData');

        if (is_null($jsonData)) {
            return $request;
        }

        $jsonParameters = json_decode((string)$jsonData, true, 512, JSON_THROW_ON_ERROR);

        if (is_array($jsonParameters)) {
            $request->request->add($jsonParameters);
            $request->request->remove('jsonData');
        }

        return $request;
    }
}
