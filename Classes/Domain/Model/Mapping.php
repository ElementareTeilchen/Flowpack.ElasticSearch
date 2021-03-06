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
use Neos\Utility\Arrays;

/**
 * Reflects a Mapping of Elasticsearch
 */
class Mapping
{
    /**
     * @var AbstractType
     */
    protected $type;

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/mapping-root-object-type.html#_dynamic_templates
     * @var array
     */
    protected $dynamicTemplates = array();

    /**
     * This is the full / raw ElasticSearch mapping which is merged with the properties and dynamicTemplates.
     *
     * It can be used to specify arbitrary ElasticSearch mapping options, like f.e. configuring the _all field.
     *
     * @var array
     */
    protected $fullMapping = array();

    /**
     * @param AbstractType $type
     */
    public function __construct(AbstractType $type)
    {
        $this->type = $type;
    }

    /**
     * Gets a property setting by its path
     *
     * @param array|string $path
     * @return mixed
     */
    public function getPropertyByPath($path)
    {
        return Arrays::getValueByPath($this->properties, $path);
    }

    /**
     * Gets a property setting by its path
     *
     * @param array|string $path
     * @param string $value
     * @return void
     */
    public function setPropertyByPath($path, $value)
    {
        $this->properties = Arrays::setValueByPath($this->properties, $path, $value);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return AbstractType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Return the mapping which would be sent to the server as array
     *
     * @return array
     */
    public function asArray()
    {
        return array($this->type->getName() => Arrays::arrayMergeRecursiveOverrule(array(
            'dynamic_templates' => $this->getDynamicTemplates(),
            'properties' => $this->getProperties()
        ), $this->fullMapping));
    }

    /**
     * Sets this mapping to the server
     *
     * @return \Flowpack\ElasticSearch\Transfer\Response
     */
    public function apply()
    {
        $content = json_encode($this->asArray());
        $response = $this->type->request('PUT', '/_mapping', array(), $content);

        return $response;
    }

    /**
     * @return array
     */
    public function getDynamicTemplates()
    {
        return $this->dynamicTemplates;
    }

    /**
     * Dynamic templates allow to define mapping templates
     *
     * @param string $dynamicTemplateName
     * @param array $mappingConfiguration
     * @return void
     */
    public function addDynamicTemplate($dynamicTemplateName, array $mappingConfiguration)
    {
        $this->dynamicTemplates[] = array(
            $dynamicTemplateName => $mappingConfiguration
        );
    }

    /**
     * This is the full / raw ElasticSearch mapping which is merged with the properties and dynamicTemplates.
     *
     * It can be used to specify arbitrary ElasticSearch mapping options, like f.e. configuring the _all field.
     *
     * @param array $fullMapping
     * @return void
     */
    public function setFullMapping(array $fullMapping)
    {
        $this->fullMapping = $fullMapping;
    }

    /**
     * See {@link setFullMapping} for documentation
     *
     * @return array
     */
    public function getFullMapping()
    {
        return $this->fullMapping;
    }
}
