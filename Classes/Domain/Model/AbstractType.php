<?php
namespace Flowpack\ElasticSearch\Domain\Model;

/*
 * This file is part of the Flowpack.ElasticSearch package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;

/**
 * An abstract document type. Implement your own or use the GenericType provided with this package.
 */
abstract class AbstractType
{
    /**
     * @Flow\Inject
     * @var \Flowpack\ElasticSearch\Domain\Factory\DocumentFactory
     */
    protected $documentFactory;

    /**
     * @var \Flowpack\ElasticSearch\Domain\Model\Index
     */
    protected $index;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param Index $index
     * @param string $name
     */
    public function __construct(Index $index, $name = null)
    {
        $this->index = $index;

        if ($name === null) {
            $this->name = str_replace('\\', '_', get_class($this));
        } else {
            $this->name = $name;
        }
    }

    /**
     * Gets this type's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Flowpack\ElasticSearch\Domain\Model\Index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns a document
     *
     * @param string $id
     * @return \Flowpack\ElasticSearch\Domain\Model\Document
     */
    public function findDocumentById($id)
    {
        $response = $this->request('GET', '/' . $id);
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $document = $this->documentFactory->createFromResponse($this, $id, $response);

        return $document;
    }

    /**
     * @param string $id
     * @return boolean ...whether the deletion is considered successful
     */
    public function deleteDocumentById($id)
    {
        $response = $this->request('DELETE', '/' . $id);
        $treatedContent = $response->getTreatedContent();

        return $response->getStatusCode() === 200 && $treatedContent['found'] === true;
    }

    /**
     * @return integer
     */
    public function count()
    {
        $response = $this->request('GET', '/_count');
        if ($response->getStatusCode() !== 200) {
            return null;
        }
        $treatedContent = $response->getTreatedContent();

        return (integer)$treatedContent['count'];
    }

    /**
     * @param array $searchQuery The search query TODO: make it an object
     * @return \Flowpack\ElasticSearch\Transfer\Response
     */
    public function search(array $searchQuery)
    {
        return $this->request('GET', '/_search', array(), json_encode($searchQuery));
    }

    /**
     * @param string $method
     * @param string $path
     * @param array $arguments
     * @param string $content
     * @return \Flowpack\ElasticSearch\Transfer\Response
     */
    public function request($method, $path = null, array $arguments = array(), $content = null)
    {
        $path = '/' . $this->name . ($path ?: '');

        return $this->index->request($method, $path, $arguments, $content);
    }
}
