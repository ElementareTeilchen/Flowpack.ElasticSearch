<?php
namespace Flowpack\ElasticSearch\Indexer\Object\Transform;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class TransformerFactory
{
    /**
     * @Flow\Inject
     * @var \Neos\Flow\ObjectManagement\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param string $annotatedTransformer Either a full qualified class name or a shortened one which is seeked in the current package.
     * @return \Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerInterface
     * @throws \Flowpack\ElasticSearch\Exception
     */
    public function create($annotatedTransformer)
    {
        if (!class_exists($annotatedTransformer)) {
            $annotatedTransformer = 'Flowpack\ElasticSearch\Indexer\Object\Transform\\' . $annotatedTransformer . 'Transformer';
        }
        $transformer = $this->objectManager->get($annotatedTransformer);
        if (!$transformer instanceof \Flowpack\ElasticSearch\Indexer\Object\Transform\TransformerInterface) {
            throw new \Flowpack\ElasticSearch\Exception(sprintf('The transformer instance "%s" does not implement the TransformerInterface.', $annotatedTransformer), 1339598316);
        }

        return $transformer;
    }
}
