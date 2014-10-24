<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @copyright Copyright 2014 John Flatness
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * Class implmenting metadata output for RDF metadata format.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_Rdf extends OaiPmhRepository_Metadata_Abstract
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'rdf';

    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';

    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/rdf.xsd';

    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    /** XML namespace for DC terms */
    const DCTERMS_NAMESPACE_URI = 'http://purl.org/dc/terms/';

    /**
     * Append RDF metadata.
     */
    public function appendMetadata($metadataElement)
    {
        $rdf = $this->document->createElementNS(
            self::METADATA_NAMESPACE, 'rdf:RDF');
        $metadataElement->appendChild($rdf);

        // Must manually specify XML schema uri per spec, but DOM won't include
        // a redundant xmlns:xsi attribute, so we just set the attribute
        $rdf->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $rdf->setAttribute('xmlns:dcterms', self::DCTERMS_NAMESPACE_URI);
        $rdf->setAttribute('xmlns:xsi', parent::XML_SCHEMA_NAMESPACE_URI);
        $rdf->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE.' '.
            self::METADATA_SCHEMA);

        $description = $this->appendNewElement($rdf, 'rdf:Description');

        $oaiId = OaiPmhRepository_OaiIdentifier::itemToOaiId($this->item->id);
        $description->setAttribute('rdf:about', $oaiId);

        $dcExtendedElements = array(
            'Title' => 'dc:title',
            'Creator' => 'dc:creator',
            'Subject' => 'dc:subject',
            'Description' => 'dc:description',
            'Publisher' => 'dc:publisher',
            'Contributor' => 'dc:contributor',
            'Date' => 'dc:date',
            'Type' => 'dc:type',
            'Format' => 'dc:format',
            'Identifier' => 'dc:identifier',
            'Source' => 'dc:source',
            'Language' => 'dc:language',
            'Relation' => 'dc:relation',
            'Coverage' => 'dc:coverage',
            'Rights' => 'dc:rights',
            'Abstract' => 'dcterms:abstract',
            'Access Rights' => 'dcterms:accessRights',
            'Accrual Method' => 'dcterms:accrualMethod',
            'Accrual Periodicity' => 'dcterms:accrualPeriodicity',
            'Accrual Policy' => 'dcterms:accrualPolicy',
            'Alternative Title' => 'dcterms:alternative',
            'Audience' => 'dcterms:audience',
            'Date Available' => 'dcterms:available',
            'Bibliographic Citation' => 'dcterms:bibliographicCitation',
            'Conforms To' => 'dcterms:conformsTo',
            'Date Created' => 'dcterms:created',
            'Date Accepted' => 'dcterms:dateAccepted',
            'Date Copyrighted' => 'dcterms:dateCopyrighted',
            'Date Submitted' => 'dcterms:dateSubmitted',
            'Audience Education Level' => 'dcterms:educationLevel',
            'Extent' => 'dcterms:extent',
            'Has Format' => 'dcterms:hasFormat',
            'Has Part' => 'dcterms:hasPart',
            'Has Version' => 'dcterms:hasVersion',
            'Instructional Method' => 'dcterms:instructionalMethod',
            'Is Format Of' => 'dcterms:isFormatOf',
            'Is Part Of' => 'dcterms:isPartOf',
            'Is Referenced By' => 'dcterms:isReferencedBy',
            'Is Replaced By' => 'dcterms:isReplacedBy',
            'Is Required By' => 'dcterms:isRequiredBy',
            'Date Issued' => 'dcterms:issued',
            'Is Version Of' => 'dcterms:isVersionOf',
            'License' => 'dcterms:license',
            'Mediator' => 'dcterms:mediator',
            'Medium' => 'dcterms:medium',
            'Date Modified' => 'dcterms:modified',
            'Provenance' => 'dcterms:provenance',
            'References' => 'dcterms:references',
            'Replaces' => 'dcterms:replaces',
            'Requires' => 'dcterms:requires',
            'Rights Holder' => 'dcterms:rightsHolder',
            'Spatial Coverage' => 'dcterms:spatial',
            'Table Of Contents' => 'dcterms:tableOfContents',
            'Temporal Coverage' => 'dcterms:temporal',
            'Date Valid' => 'dcterms:valid'
        );

        $elementTexts = $this->item->getAllElementTexts();
        $elements = $this->item->getElementsBySetName('Dublin Core');

        foreach ($dcExtendedElements as $elementName => $propertyName) {
            try {
                $texts = $this->item->getElementTexts('Dublin Core', $elementName);
            } catch (Omeka_Record_Exception $e) {
                continue;
            }
            foreach ($texts as $text) {
                $this->appendNewElement($description, $propertyName, $text->text);
            }
        }
    }

    /**
     * Returns the OAI-PMH metadata prefix for the output format.
     *
     * @return string Metadata prefix
     */
    public function getMetadataPrefix()
    {
        return self::METADATA_PREFIX;
    }

    /**
     * Returns the XML schema for the output format.
     *
     * @return string XML schema URI
     */
    public function getMetadataSchema()
    {
        return self::METADATA_SCHEMA;
    }

    /**
     * Returns the XML namespace for the output format.
     *
     * @return string XML namespace URI
     */
    public function getMetadataNamespace()
    {
        return self::METADATA_NAMESPACE;
    }
}
